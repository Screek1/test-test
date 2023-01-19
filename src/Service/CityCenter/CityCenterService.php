<?php


namespace App\Service\CityCenter;


use App\Criteria\ListingSearchCriteria;
use App\Entity\CityCenter;
use App\Repository\CityCenterRepository;
use App\Service\Utils\FormUtils;
use Symfony\Component\HttpFoundation\Request;

class CityCenterService
{
    private CityCenterRepository $cityCenterRepository;

    public function __construct(CityCenterRepository $cityCenterRepository)
    {
        $this->cityCenterRepository = $cityCenterRepository;
    }

    public function getCityCenters(
        int $currentPage = 1,
        int $limit = 50,
        int $offset = 0
    )
    {
        $results = $this->cityCenterRepository->findBy([],['id' => 'DESC'], $limit, $offset);
        $cityCenterTotal = $this->countCityCenters();

        $pageCounter = 1;
        if (count($results) != 1) {
            $pageCounter = ceil($cityCenterTotal / $limit);
        }

        return new CityCenterSearchResult($cityCenterTotal, $results, $currentPage, $pageCounter);
    }

    public function getCityCenter(ListingSearchCriteria $criteria)
    {
        $center = $this->cityCenterRepository->findByCriteria($criteria);

        if (!$center) { return null;}

        return ['lat' => $center->getLatitude(), 'lon' => $center->getLongitude(), 'zoom' => $center->getZoom()];
    }

    public function create($data)
    {
        if($this->cityCenterRepository->checkExist($data)) {
            throw new \Exception('The city center exists');
        }

        $this->cityCenterRepository->create($data);
    }

    public function update(Request $request)
    {
        $data = FormUtils::getJson($request);

        if($this->cityCenterRepository->checkExist($data)) {
            throw new \Exception('The city center exists');
        }

        $this->cityCenterRepository->update($data);
    }

    public function delete(CityCenter $cityCenter)
    {
        $this->cityCenterRepository->delete($cityCenter);
    }

    private function countCityCenters(): int
    {
        return $this->cityCenterRepository->count([]);
    }
}