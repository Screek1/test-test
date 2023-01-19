<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 06.10.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Listing;


use App\Entity\Listing;
use App\Exception\ListingNotFoundException;
use App\Service\Feed\DdfService;
use App\Service\Feed\IdxService;
use PHRETS\Exceptions\CapabilityUnavailable;

class ListingDataSyncService
{
    private ListingService $listingService;
    private IdxService $idxService;
    private DdfService $ddfService;

    public function __construct(ListingService $listingService, DdfService $ddfService, IdxService $idxService)
    {
        $this->listingService = $listingService;
        $this->idxService = $idxService;
        $this->ddfService = $ddfService;
    }

    /**
     * @param Listing $listing
     * @return Listing
     * @throws ListingNotFoundException
     * @throws CapabilityUnavailable
     */
    public function syncAllListingData(Listing $listing): Listing
    {
        if ($listing->getFeedID() == 'ddf') {
            $listingForProcessingData = $this->ddfService->getListingByFeedListingId($listing->getFeedListingID());
            return $this->listingService->upsertFromDdfResult($listingForProcessingData, false);
        } else {
            $listingForProcessingData = $this->idxService->getListingByFeedListingId($listing);
            return $this->listingService->upsertFromIdxResult($listingForProcessingData, false);
        }
    }
}