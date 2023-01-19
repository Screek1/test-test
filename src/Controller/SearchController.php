<?php

namespace App\Controller;

use App\Service\Listing\ListingSearchService;
use App\Service\Listing\ListingService;
use App\Service\Utils\UrlUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SearchController extends AbstractController
{
    const DefaultPageSize = 49;

    private ListingService $listingService;
    private ListingSearchService $listingSearchService;
    private UrlUtils $urlUtils;
    private SerializerInterface $serializer;

    public function __construct(
        ListingService       $listingService,
        UrlUtils             $urlUtils,
        ListingSearchService $listingSearchService,
        SerializerInterface  $serializer
    )
    {
        $this->urlUtils = $urlUtils;
        $this->listingService = $listingService;
        $this->listingSearchService = $listingSearchService;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/search-listings", requirements={"filters"=".+"}, name="search_listings", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function searchListings(Request $request): Response
    {
        $filters = $request->query->all();
        $criteria = $this->urlUtils->uriToSearchCriteriaBySearch($filters);
        $searchResult = $this->listingSearchService->searchListings($criteria, self::DefaultPageSize);
        $searchFormObject = $this->listingService->getSearchFormObject();

        return $this->render(
            'listings_map/index.html.twig',
            [
                'searchFormObject' => $searchFormObject,
                'searchResult' => $searchResult,
                'criteria' => $this->serializer->normalize($criteria),
                'title' => 'Search Listings',
                'description' => 'Search Listings',
                'center' => null
            ]
        );
    }
}
