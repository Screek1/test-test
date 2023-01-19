<?php


namespace App\Controller;


use App\Criteria\ListingSearchCriteria;
use App\Repository\Listing\ListingSearchRepository;
use App\Service\CityCenter\CityCenterService;
use App\Service\Listing\ListingSearchService;
use App\Service\Search\SearchService;
use App\Service\Utils\SearchUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ListingSearchController extends AbstractController
{

    private ListingSearchService $listingSearchService;
    private SearchService $searchService;
    private CityCenterService $cityCenterService;


    public function __construct(
        ListingSearchService $listingSearchService,
        SearchService $searchService,
        CityCenterService $cityCenterService
    ) {
        $this->listingSearchService = $listingSearchService;
        $this->searchService = $searchService;
        $this->cityCenterService = $cityCenterService;
    }

    /**
     * @Route("/api/search/facets", priority=10, name="facets_search", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws ExceptionInterface
     */
    public function facetsSearch(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $query = $request->request->get('text', '');

        if (strlen($query) < 3) {
            return $this->json([]);
        }

//        $facets = $this->listingSearchRepository->facetsSearch($query);
        $facets = $this->searchService->facetsSearch($query);

        return $this->json($facets);
    }

    /**
     * @Route("/api/search/listings", priority=10, name="listing_search", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $criteria = $this->requestToCriteria($request);
        $center = $this->cityCenterService->getCityCenter($criteria);
        $result = $this->listingSearchService->searchListings($criteria);

        return $this->json(['total' => $result->total, 'listings' => $result->listings, 'center' => $center]);
    }

    private function requestToCriteria(Request $request): ListingSearchCriteria
    {
        $criteria = new ListingSearchCriteria();

        $data = json_decode($request->getContent(), true);

        if (is_array($data)) {
            $criteria = SearchUtils::buildSearchCriteriaData($criteria, $data);
        }

        return $criteria;
    }
}