<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 03.10.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Feed;


use App\Repository\ListingMasterRepository;
use App\Repository\ListingRepository;
use App\Service\Listing\ListingConstants;
use App\Service\Search\SearchService;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class DdfListingMasterService
{
    private DdfService $ddfService;
    private ListingMasterRepository $listingMasterRepository;
    private ListingRepository $listingRepository;
    private SearchService $searchService;
    private LoggerInterface $logger;


    public function __construct(
        DdfService              $ddfService,
        ListingMasterRepository $listingMasterRepository,
        ListingRepository       $listingRepository,
        SearchService           $searchService,
        LoggerInterface         $logger
    )
    {
        $this->ddfService = $ddfService;
        $this->listingMasterRepository = $listingMasterRepository;
        $this->listingRepository = $listingRepository;
        $this->searchService = $searchService;
        $this->logger = $logger;
    }

    public function syncListingRecords()
    {
        $this->listingMasterRepository->truncateListingMasterTable(ListingConstants::FeedDdf);
        $this->log('truncateListingMasterTable');
        $masterList = $this->ddfService->getMasterList();
        $this->log('getMasterList');
        $this->listingMasterRepository->insertMasterList(ListingConstants::FeedDdf, $masterList);
        $this->log('insertMasterList');
        $this->listingRepository->tagListingsForDeletion(ListingConstants::FeedDdf);
        $this->log("tagListingsForDeletion DB");
//        $this->searchService->deleteListings($removedListings);
//        $this->log('deleteListings elasticsearch');
        $this->listingRepository->createMissingListingsFromDdfListingMaster(ListingConstants::FeedDdf);
        $this->log('createMissingListingsFromDdfListingMaster');
        $this->listingMasterRepository->truncateListingMasterTable(ListingConstants::FeedDdf);
        $this->log('truncateListingMasterTable');
    }

    private function log($message)
    {
        $usedMem = round(memory_get_usage(true) / 1024);
        $allocMem = round(memory_get_usage(false) / 1024);
        $this->logger->log(LogLevel::INFO, $message . ': ' . $allocMem . ' / ' . $usedMem . ' / ' . ($allocMem - $usedMem));
    }

}