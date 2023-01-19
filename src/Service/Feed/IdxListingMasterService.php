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

class IdxListingMasterService
{
    private IdxService $idxService;
    private ListingMasterRepository $listingMasterRepository;
    private ListingRepository $listingRepository;
    private SearchService $searchService;
    private LoggerInterface $logger;

    public function __construct(
        IdxService              $idxService,
        ListingMasterRepository $listingMasterRepository,
        ListingRepository       $listingRepository,
        SearchService           $searchService,
        LoggerInterface         $logger
    )
    {
        $this->idxService = $idxService;
        $this->listingMasterRepository = $listingMasterRepository;
        $this->listingRepository = $listingRepository;
        $this->searchService = $searchService;
        $this->logger = $logger;
    }

    public function syncListingRecords($classId)
    {
        $this->listingMasterRepository->truncateListingMasterTable(ListingConstants::FeedIdx, $classId);
        $this->log('truncateListingMasterTable');
        $masterList = $this->idxService->getMasterList($classId);
        $this->log('getMasterList');
        $this->listingMasterRepository->insertMasterList(ListingConstants::FeedIdx, $masterList, $classId);
        $this->log('insertMasterList');
        $this->listingRepository->tagListingsForDeletion(ListingConstants::FeedIdx, $classId);
        $this->log("tagListingsForDeletion");
//        $this->searchService->deleteListings($removedListings);
//        $this->log('deleteListings Elastic');
        $this->listingRepository->createMissingListingsFromIdxListingMaster(ListingConstants::FeedIdx, $classId);
        $this->log('createMissingListingsFromDdfListingMaster');
        $this->listingMasterRepository->truncateListingMasterTable(ListingConstants::FeedIdx, $classId);
        $this->log('truncateListingMasterTable');
    }

    private function log($message)
    {
        $usedMem = round(memory_get_usage(true) / 1024);
        $allocMem = round(memory_get_usage(false) / 1024);
        $this->logger->log(LogLevel::INFO, $message . ': ' . $allocMem . ' / ' . $usedMem . ' / ' . ($allocMem - $usedMem));
    }
}