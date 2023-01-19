<?php

namespace App\Controller;

use App\Repository\ListingRepository;
use App\Service\CityPhone\CityPhoneService;
use App\Service\Listing\ListingSearchDataService;
use App\Service\Listing\ListingService;
use App\Service\Listing\ListingSimilarSearch;
use App\Service\Search\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListingController extends AbstractController
{
    private array $shortsTitleName = [
        'AVENUE' => 'AVE',
        'BOULEVARD' => 'BLVD',
        'CRESCENT' => 'CRES',
        'DRIVE' => 'DR',
        'HIGHWAY' => 'HWY',
        'PLACE' => 'PL',
        'ROAD' => 'RD',
        'STREET' => 'ST',
        'TERRACE' => 'TERR',
    ];

    private ListingSimilarSearch $listingSimilarSearch;
    private SearchService $searchService;
    private ListingSearchDataService $listingSearchDataService;
    private ListingRepository $listingRepository;
    private ListingService $listingService;
    private CityPhoneService $cityPhoneService;

    public function __construct(
        ListingSimilarSearch     $listingSimilarSearch,
        SearchService            $searchService,
        ListingSearchDataService $listingSearchDataService,
        ListingRepository        $listingRepository,
        ListingService           $listingService,
        CityPhoneService         $cityPhoneService
    )
    {
        $this->listingSimilarSearch = $listingSimilarSearch;
        $this->searchService = $searchService;
        $this->listingSearchDataService = $listingSearchDataService;
        $this->listingRepository = $listingRepository;
        $this->listingService = $listingService;
        $this->cityPhoneService = $cityPhoneService;
    }

    /**
     * @Route("/l/{fullAddress}/{mlsNum}--{id}", priority=10, name="listing", requirements={"fullAddress"=".+", "id"="\d+"})
     * @param string $mlsNum
     * @param string $id
     * @return Response
     */
    public function index(string $mlsNum, string $id)
    {
        $listingData = $this->searchService->getOneByIdAndMlsNum($id, $mlsNum);
        if (!$listingData) {
            $listing = $this->listingRepository->getListingByIdAndMls($id, $mlsNum);

            if (!$listing) {
                return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
            }

            $agent = $this->listingSearchDataService->getAgentObject();
            $phoneNumber = $this->cityPhoneService->getListingPhoneNumber($listing->getCity(), $listing->getStateOrProvince(), $listing->getCountry());
            $similarListings = $this->listingSimilarSearch->getSimilarListings($listing);
            $listingText = 'This listing is either SOLD or REMOVED from the MLS®. Please call us at ' . $phoneNumber . ' or just fill out the form on the right side to find out the SOLD PRICE';
            $location = $listing->getCity() . ', ' . ListingService::getProvinceCode($listing->getStateOrProvince());

            return $this->render('listing/no-listing.html.twig', [
                'listing' => $listing,
                'agent' => $agent,
                'listingTitle' => $this->getListingTitle($listing->getUnparsedAddress(), $listing->getCity(), $listing->getStateOrProvince()),
                'metaTitle' => $this->getMetaTitle($listing->getUnparsedAddress(), $listing->getCity(), $listing->getStateOrProvince(), $listing->getMlsNum()),
                'listingText' => $listingText,
                'buttonLink' => $this->generateUrl('listings_map', ['location' => $listing->getCity() . ',' . ListingService::getProvinceCode($listing->getStateOrProvince())]),
                'buttonText' => 'View Similar Homes in ' . $location,
                'listingMLS' => 'MLS®' . $listing->getMlsNum(),
                'metaDescription' => $this->getMetaDescription($listing->getUnparsedAddress(), $listing->getCity(), $listing->getType(), $listing->getMlsNum()),
                'phoneNumber' => $phoneNumber,
                'similarListings' => $similarListings,
            ]);


        }
        $similarListings = $this->listingSimilarSearch->getSimilarListingsData($listingData);
        $seoDescription = $this->listingSearchDataService->getListingSeoDescription($listingData);
        $neighborhoodData = $this->listingSearchDataService->getNeighborhoodData($listingData);
        $daysOnTheMarket = $this->listingService->countDaysOnTheMarket($listingData);
        $phoneNumber = $this->cityPhoneService->getListingPhoneNumber($listingData['address']['city'], $listingData['address']['state'], $listingData['address']['country']);

        return $this->render(
            'listing/index.html.twig',
            [
                'metaTitle' => $this->getMetaTitle($listingData['address']['streetAddress'], $listingData['address']['city'], $listingData['address']['state'], $listingData['mlsNumber']),
                'listingTitle' => $this->getListingTitle($listingData['address']['streetAddress'], $listingData['address']['city'], $listingData['address']['state']),
                'metaDescription' => $this->getMetaDescription($listingData['address']['streetAddress'], $listingData['address']['city'], $listingData['type'], $listingData['mlsNumber']),
                'listing' => $listingData,
                'similarListings' => $similarListings,
                'seoDescription' => $seoDescription,
                'neighborhoodData' => $neighborhoodData,
                'canonicalLink' => $this->searchService->getCanonicalLink($mlsNum, $listingData),
                'daysOnTheMarket' => $daysOnTheMarket,
                'phoneNumber' => $phoneNumber
            ]
        );
    }

    private function getMetaTitle($streetAddress, $city, $state, $mlsNum)
    {
        $address = [];

        if ($streetAddress) {
            $address[] = $streetAddress;
        }

        if ($city) {
            $address[] = $city;
        }

        if ($state) {
            $address[] = ListingService::getProvinceCode($state) . ' for Sale. MLS® ' . $mlsNum;
        } else {
            $address[] = 'for Sale. MLS® ' . $mlsNum;
        }

        $title = implode(", ", $address);

        foreach ($this->shortsTitleName as $key => $item) {
            $title = str_replace($key, $item, $title);
            $title = str_replace(strtolower($key), strtolower($item), $title);
            $title = str_replace(ucfirst(strtolower($key)), ucfirst(strtolower($item)), $title);
        }

        return $title;
    }

    private function getListingTitle($streetAddress, $city, $state)
    {
        $address = [];

        if ($streetAddress) {
            $address[] = $streetAddress;
        }

        if ($city) {
            $address[] = $city;
        }

        if ($state) {
            $address[] = ListingService::getProvinceCode($state);
        }

        $title = implode(", ", $address);

        foreach ($this->shortsTitleName as $key => $item) {
            $title = str_replace($key, $item, $title);
            $title = str_replace(strtolower($key), strtolower($item), $title);
            $title = str_replace(ucfirst(strtolower($key)), ucfirst(strtolower($item)), $title);
        }

        return $title;
    }

    private function getMetaDescription($streetAddress, $city, $type, $mlsNum)
    {
        $city = ucfirst($city);
        $type = ucfirst(strtolower($type));
        return $streetAddress . ', ' . $city . ', ' . $type . ' for sale (MLS® ' . $mlsNum . '). See property details, home price, crime info, nearby schools and neighbourhood information.';
    }

    /**
     * @Route("/{anything}-mls-{mlsNum}-{feed}", priority=10, name="legacy_listing", requirements={"anything"=".+", "mlsNum"="\w+", "feed"="\d+"})
     * @param string $mlsNum
     * @return Response
     */
    public function legacyListing(Request $request, string $mlsNum): Response
    {
        $possibleListings = $this->listingRepository->getActiveByMlsNum($mlsNum);

        if (empty($possibleListings)) {
            (new Filesystem)->appendToFile('log/legacy_listing.log', $request->getUri() . PHP_EOL);
            // TODO REMOVE LOG NEED TO THINK OF SOMETHING
//            throw $this->createNotFoundException();
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
        }
        $listing = $possibleListings[0];

        (new Filesystem)->appendToFile('log/legacy_listing_done.log', $request->getUri() . PHP_EOL);
        return $this->redirect(
            $this->generateUrl('listing', $listing->getListingRouteParams()),
            Response::HTTP_MOVED_PERMANENTLY
        );
    }

}
