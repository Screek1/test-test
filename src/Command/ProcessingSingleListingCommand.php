<?php

namespace App\Command;

use App\Exception\ListingNotFoundException;
use App\Model\Listing\ListingStatus;
use App\Model\Listing\ProcessingStatus;
use App\Repository\ListingRepository;
use App\Service\Feed\DdfService;
use App\Service\Listing\ListingConstants;
use App\Service\Listing\ListingDataSyncService;
use App\Service\Listing\ListingGeoService;
use App\Service\Listing\ListingMediaSyncService;
use App\Service\Listing\ListingService;
use App\Service\Search\SearchService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class ProcessingSingleListingCommand extends Command
{
    protected static $defaultName = 'app:processing-single-listing';
    private LoggerInterface $logger;
    private ListingRepository $listingRepository;
    private ListingService $listingService;
    private ListingGeoService $listingGeoService;
    private ListingMediaSyncService $listingMediaSyncService;
    private ListingDataSyncService $listingDataSyncService;
    private DdfService $ddfService;
    private EntityManagerInterface $entityManager;
    private SearchService $searchService;

    public function __construct(
        LoggerInterface $logger,
        ListingRepository $listingRepository,
        ListingService $listingService,
        ListingGeoService $listingGeoService,
        ListingMediaSyncService $listingMediaSyncService,
        ListingDataSyncService $listingDataSyncService,
        DdfService $ddfService,
        EntityManagerInterface $entityManager,
        SearchService $searchService
    ) {
        $this->logger = $logger;
        $this->listingRepository = $listingRepository;
        $this->listingService = $listingService;
        $this->listingGeoService = $listingGeoService;
        $this->listingMediaSyncService = $listingMediaSyncService;
        $this->listingDataSyncService = $listingDataSyncService;
        $this->ddfService = $ddfService;
        $this->entityManager = $entityManager;
        $this->searchService = $searchService;
        parent::__construct();
    }

    const BATCH_SIZE = 10;

    protected function configure()
    {
        $this->setDescription('Add a short description for your command')->addArgument(
            'bsize',
            InputArgument::OPTIONAL,
            "Batch size, default ".self::BATCH_SIZE,
            self::BATCH_SIZE
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int)$input->getArgument('bsize');

        $this->ddfService->connect();
        $batchListings = $this->listingService->getBatchListingsForProcessing(ListingConstants::FeedDdf, $batchSize);
        $this->listingService->setBatchProcessingStatus(
            $batchListings,
            ProcessingStatus::Processing
        );
        foreach ($batchListings as $singleListing) {
            try {
                $this->logger->info(
                    "Start processing listing MLS_NUM: {$singleListing->getMlsNum()} Listing Feed ID: {$singleListing->getFeedListingID()} Listing Feed Type: {$singleListing->getFeedId()}"
                );
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

                $this->logger->info(
                    "Success processing - Listing MLS_NUM: {$singleListingWithSchools->getMlsNum()} Listing Feed ID: {$singleListingWithSchools->getFeedListingID()} Listing Feed Type: {$singleListing->getFeedId()}"
                );
            } catch (ListingNotFoundException $exception) {
                $this->listingService->setListingProcessingStatusWithError(
                    $singleListing->getId(),
                    ProcessingStatus::Error,
                    $exception
                );
                $this->logger->error($exception->getMessage());
            } catch (\Exception | Throwable $exception) {
                $this->listingService->setListingProcessingStatusWithError(
                    $singleListing->getId(),
                    ProcessingStatus::Error,
                    $exception
                );
                $this->logger->error($exception->getMessage());
            } finally {
                $this->entityManager->clear();
            }
        }

        return Command::SUCCESS;
    }
}
