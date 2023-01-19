<?php

namespace App\Command;

use App\Service\Feed\FeedService;
use App\Service\Feed\IdxListingMasterService;
use App\Service\Feed\SearchUpdatedIdxListingsService;
use App\Service\Listing\ListingConstants;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class FetchListingIdxUpdatesCommand extends Command
{
    protected static $defaultName = 'app:listing-idx-updates';

    private bool $hasError = false;

    private LoggerInterface $logger;
    private FeedService $feedService;
    private IdxListingMasterService $idxListingMasterService;
    private SearchUpdatedIdxListingsService $searchUpdatedIdxListingsService;

    public function __construct(
        LoggerInterface                 $logger,
        FeedService                     $feedService,
        IdxListingMasterService         $idxListingMasterService,
        SearchUpdatedIdxListingsService $searchUpdatedIdxListingsService
    )
    {
        $this->logger = $logger;
        $this->feedService = $feedService;
        $this->idxListingMasterService = $idxListingMasterService;
        $this->searchUpdatedIdxListingsService = $searchUpdatedIdxListingsService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('classId', InputArgument::REQUIRED, 'Listing Class ID: RD_1, RA_2, MF_3, LD_4, RT_5')
            ->setDescription(
                'This command fetched the updates from IDX Feed. Also, makes sure that expired listings are removed and missing are added'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');
        $classId = $input->getArgument('classId');

        $commandLastRunTimeDate = new \DateTime();
        $io = new SymfonyStyle($input, $output);

        if (!$classId) {
            $io->warning('Missed class ID');
            return Command::FAILURE;
        }

        $io->success("Start fetching idx listings");
        $this->logger->alert('Start fetching idx listings');
        if ($this->feedService->isFeedBusy(ListingConstants::FeedIdx, $classId)) {
            $io->warning('IDX ' . $classId . ' feed is busy');
            $this->logger->warning('IDX ' . $classId . ' feed is busy');

            return Command::FAILURE;
        }
        $lastRunTimeDate = $this->feedService->setBusyByFeedName(ListingConstants::FeedIdx, true, $classId);
        try {
            $this->searchUpdatedIdxListingsService->searchAndRecordUpdatedListings($lastRunTimeDate, $classId);
            $this->idxListingMasterService->syncListingRecords($classId);

            $this->feedService->setLastRunTimeByFeedName(ListingConstants::FeedIdx, $commandLastRunTimeDate, $classId);
        } catch (\Exception|Throwable $e) {
            $io->error($e->getMessage());
            $this->logger->error($e->getMessage(), $e->getTrace());
            $this->hasError = true;
        } finally {
            $this->feedService->setBusyByFeedName(ListingConstants::FeedIdx, false, $classId);
        }

        $this->logger->alert('Fetching idx listings is finished');

        return $this->hasError ? Command::FAILURE : Command::SUCCESS;
    }
}
