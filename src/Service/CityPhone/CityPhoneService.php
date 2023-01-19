<?php
declare(strict_types=1);

namespace App\Service\CityPhone;

use App\Entity\CityPhone;
use App\Repository\CityPhoneRepository;
use App\Service\Utils\FormUtils;
use Symfony\Component\HttpFoundation\Request;

class CityPhoneService
{
    private CityPhoneRepository $cityPhoneRepository;

    private $defaultNumber = '778-200-2710';

    public function __construct(CityPhoneRepository $cityPhoneRepository)
    {
        $this->cityPhoneRepository = $cityPhoneRepository;
    }

    public function getCityPhones(
        $search,
        int $currentPage = 1,
        int $limit = 50,
        int $offset = 0
    )
    {
        $countWhere = 0;
        $query = $this->cityPhoneRepository->createQueryBuilder('cp');

        if ($search['country']) {
            $this->addWhere($query, 'cp.country', 'country', $countWhere);
            $countWhere++;
        }

        if ($search['stateOrProvince']) {
            $this->addWhere($query, 'cp.state_or_province', 'stateOrProvince', $countWhere);
            $countWhere++;
        }

        if ($search['city']) {
            $this->addWhere($query, 'cp.city', 'city', $countWhere);
            $countWhere++;
        }

        foreach ($search as $key => $value) {
            if ($value) $query->setParameter($key, '%' . $value . '%');
        }

        $cityPhonesTotal = $this->countPhones($query);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->orderBy('cp.id', 'asc');

        $results = $query->getQuery()->getResult();

        $pageCounter = 1;
        if (count($results) != 1) {
            $pageCounter = (int)ceil($cityPhonesTotal / $limit);
        }

        return new CityPhoneSearchResult($cityPhonesTotal, $results, $currentPage, $pageCounter);
    }

    public function getListingPhoneNumber($city, $state, $country)
    {
        $cityNumber = $this->cityPhoneRepository->findByListing($city, $state, $country);

        return $cityNumber ? $cityNumber->getPhone() : $this->defaultNumber;
    }

    public function create($data)
    {
        if ($this->cityPhoneRepository->checkExist($data)) {
            throw new \Exception('The city phone exists');
        }

        $this->cityPhoneRepository->create($data);
    }

    public function update(Request $request)
    {
        $data = FormUtils::getJson($request);

        if ($this->cityPhoneRepository->checkExist($data)) {
            throw new \Exception('The city center exists');
        }

        $this->cityPhoneRepository->update($data);
    }

    public function delete(CityPhone $cityPhone)
    {
        $this->cityPhoneRepository->delete($cityPhone);
    }

    private function countCityPhones(): int
    {
        return $this->cityPhoneRepository->count([]);
    }

    private function addWhere($query, $field, $value, $countWhere)
    {
        if ($countWhere >= 1) {
            return $query->andWhere($field . ' LIKE :' . $value);
        }

        return $query->where($field . ' LIKE :' . $value);
    }

    public function countPhones($query)
    {
        return count($query->getQuery()->getResult());
    }
}