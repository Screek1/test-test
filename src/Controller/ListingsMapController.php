<?php

namespace App\Controller;

use App\Service\CityCenter\CityCenterService;
use App\Service\Listing\ListingSearchService;
use App\Service\Listing\ListingService;
use App\Service\Utils\UrlUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ListingsMapController extends AbstractController
{
    const DefaultPageSize = 49;

    private ListingService $listingService;
    private UrlUtils $urlUtils;
    private ListingSearchService $listingSearchService;
    private SerializerInterface $serializer;
    private CityCenterService $cityCenterServer;

    public function __construct(
        ListingService       $listingService,
        UrlUtils             $urlUtils,
        ListingSearchService $listingSearchService,
        SerializerInterface  $serializer,
        CityCenterService    $cityCenterServer
    )
    {
        $this->listingService = $listingService;
        $this->urlUtils = $urlUtils;
        $this->listingSearchService = $listingSearchService;
        $this->serializer = $serializer;
        $this->cityCenterServer = $cityCenterServer;
    }

    private function resolveTitle(string $location): string
    {
        $locationParts = explode(',', $location);

        if (count($locationParts) === 2) {
            return "$locationParts[0], $locationParts[1] Real Estate for Sale, MLS® Listings & Homes for Sale";
        }

        return 'Homes for Sale, MLS® Listings | viksemenov';
    }

    private function resolveDescription(string $location): string
    {
        $locationParts = explode(',', $location);

        if (count($locationParts) === 2) {
            return "List of all MLS® Real Estate Listings: Houses, Condos, Apartments and Land for sale in $location, Canada | viksemenov";
        }

        return 'List of all MLS® Real Estate Listings: Houses, Condos, Apartments and Land for sale in Canada | viksemenov';
    }

    /**
     * @Route("/for-sale/{location}/{filters?}", priority=10, name="listings_map", requirements={"filters"=".+"}, methods={"GET"})
     *
     * @param string $location
     * @param string|null $filters
     * @return Response
     * @throws ExceptionInterface
     */
    public function index(string $location, string $filters = null): Response
    {
        // Search for relevant listings
        $criteria = $this->urlUtils->buildCriteriaByLocationAndFiltersString($location, $filters);
        $searchResult = $this->listingSearchService->searchListings($criteria, self::DefaultPageSize);

        $searchFormObject = $this->listingService->getSearchFormObject();
        $center = $this->cityCenterServer->getCityCenter($criteria);

        return $this->render(
            'listings_map/index.html.twig',
            [
                'searchFormObject' => $searchFormObject,
                'searchResult' => $searchResult,
                'criteria' => $this->serializer->normalize($criteria),
                'title' => $this->resolveTitle($location),
                'description' => $this->resolveDescription($location),
                'center' => $center
            ]
        );
    }

    /**
     * @param string $streetName
     * @param string|null $filters
     * @return Response
     * @throws ExceptionInterface
     * @deprecated
     *
     */
    public function streetSale(string $streetName, string $filters = null): Response
    {
        $streetName = str_replace('-', ' ', $streetName);
        $criteria = $this->urlUtils->buildCriteriaByStreetAndFiltersString($streetName, $filters);
        $searchResult = $this->listingSearchService->searchListings($criteria, self::DefaultPageSize);

        $searchFormObject = $this->listingService->getSearchFormObject();

        return $this->render(
            'listings_map/index.html.twig',
            [
                'searchFormObject' => $searchFormObject,
                'searchResult' => $searchResult,
                'criteria' => $this->serializer->normalize($criteria),
            ]
        );
    }

}
