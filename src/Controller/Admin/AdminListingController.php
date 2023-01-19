<?php

namespace App\Controller\Admin;

use App\Model\Listing\ListingStatus;
use App\Model\Listing\ProcessingStatus;
use App\Repository\ListingRepository;
use App\Service\Geo\GeoCodeService;
use App\Service\Geo\Point;
use App\Service\Listing\ListingDataSyncService;
use App\Service\Listing\ListingGeoService;
use App\Service\Listing\ListingMediaSyncService;
use App\Service\Listing\ListingService;
use App\Service\Search\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("/admin", priority=10)
 */
class AdminListingController extends AbstractController
{
    const LIMIT = 50;

    private ListingService $listingService;
    private ListingDataSyncService $listingDataSyncService;
    private ListingMediaSyncService $listingMediaSyncService;
    private ListingGeoService $listingGeoService;
    private ListingRepository $listingRepository;
    private SearchService $searchService;
    private GeoCodeService $geoCodeService;


    public function __construct(
        ListingDataSyncService  $listingDataSyncService,
        ListingGeoService       $listingGeoService,
        ListingMediaSyncService $listingMediaSyncService,
        ListingService          $listingService,
        ListingRepository       $listingRepository,
        SearchService           $searchService,
        GeoCodeService          $geoCodeService
    )
    {
        $this->searchService = $searchService;
        $this->listingRepository = $listingRepository;
        $this->listingGeoService = $listingGeoService;
        $this->listingMediaSyncService = $listingMediaSyncService;
        $this->listingDataSyncService = $listingDataSyncService;
        $this->listingService = $listingService;
        $this->geoCodeService = $geoCodeService;
    }

    /**
     * @Route("/listings/{page}", name="admin_listing_list", requirements={"page"="\d+"}, defaults={"title":"Preferences"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function listings(Request $request, int $page = 1): Response
    {
        $searchData = [
            'mlsNum' => $request->get('mlsNum'),
            'listingId' => $request->get('listingId'),
            'processingStatus' => $request->get('processingStatus'),
            'selfListing' => $request->get('selfListing') == 'on'
        ];
        $offset = ($page - 1) * self::LIMIT;
        $listingListSearchResult = $this->listingService->getAdminListingList($searchData, $page, self::LIMIT, $offset);

        return $this->render(
            'admin/admin-listing-list.html.twig',
            [
                'listingList' => $listingListSearchResult,
                'ajaxPath' => '/listing/list/coordinates/',
            ]
        );
    }

    /**
     * @Route("/listings/listing-{mlsId}", priority=10, name="admin_listing_ajax", methods={"POST"})
     */
    public function setviksemenovListing(string $mlsId): JsonResponse
    {
        $response = $this->listingService->setAdminListingSelfListing($mlsId);

        return $this->json($response);
    }

    /**
     * @Route("/listings/listing-{mlsId}/delete", priority=10, name="admin_listing_delete_ajax", methods={"POST"})
     */
    public function DeleteListing(string $mlsId): JsonResponse
    {
        $response = $this->listingService->setListingTaggedForDeleteListing($mlsId, true);

        return $this->json($response);
    }

    /**
     * @Route("/listings/listing-{id}/sync", priority=10, name="admin_listing_sync_ajax", methods={"POST"})
     */
    public function SyncListing(string $id): JsonResponse
    {
        try {
            $singleListing = $this->listingRepository->getActiveListingById($id);
            $singleListingWithData = $this->listingDataSyncService->syncAllListingData($singleListing);
            $singleListingWithPhotos = $this->listingMediaSyncService->syncAllListingPhotos($singleListingWithData);

            $singleListingWithCoordinates = $this->listingGeoService->syncListingCoordinatesFromAddress(
                $singleListingWithPhotos
            );
            $singleListingWithSchools = $this->listingService->setListingSchools($singleListingWithCoordinates);

            // Command body
            $this->listingService->setListingProcessingStatus(
                $singleListingWithSchools->getId(),
                ProcessingStatus::None
            );
            $this->listingService->setListingStatus(
                $singleListingWithSchools,
                ListingStatus::Live
            );
            $this->listingRepository->refreshCityStatsView();
            $this->searchService->syncListingById($singleListingWithSchools->getId());

            return $this->json(true);
        } catch (\Exception|Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    /**
     * @Route("/listings/listing-{id}/update-coordinates", priority=10, name="admin_listing_update_coordinates_ajax", methods={"POST"})
     */
    public function updateListingCoordinates($id)
    {
        $listing = $this->listingRepository->getActiveListingById($id);

        $listingAddress = $listing->getFullAddress(true);
        if (!empty($listingAddress)) {
            $listingCoordinates = $this->geoCodeService->getLatLong($listingAddress);
            if (is_null($listingCoordinates)) {
                return $this->json(['message' => "Coordinates not found for Listing {$listing->getMlsNum()} feed {$listing->getFeedID()}"]);
            } else {
                $listing = $this->listingService->setListingCoordinates($listing, new Point($listingCoordinates['lat'], $listingCoordinates['lon']));
                $this->searchService->syncListingById($listing->getId());
                return $this->json(['message' => "Coordinates was updated"]);
            }
        }
        return $this->json(['message' => "Listing {$listing->getMlsNum()} feed {$listing->getFeedID()} have not address!"]);
    }

    /**
     * @Route("/listings/search", priority=10, name="admin_listing_search")
     */
    public function searchListing(string $mlsId): JsonResponse
    {
        $response = $this->listingService->setAdminListingSelfListing($mlsId);

        return $this->json($response);
    }
}
