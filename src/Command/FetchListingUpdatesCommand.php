<?php

namespace App\Command;

use App\Service\Feed\DdfListingMasterService;
use App\Service\Feed\FeedService;
use App\Service\Feed\SearchUpdatedDdfListingsService;
use App\Service\Listing\ListingConstants;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class FetchListingUpdatesCommand extends Command
{
    protected static $defaultName = 'app:listing-updates';

    private bool $hasError = false;

    private LoggerInterface $logger;
    private FeedService $feedService;
    private DdfListingMasterService $ddfListingMasterService;
    private SearchUpdatedDdfListingsService $searchUpdatedDdfListingsService;

    public function __construct(
        LoggerInterface                 $logger,
        FeedService                     $feedService,
        DdfListingMasterService         $ddfListingMasterService,
        SearchUpdatedDdfListingsService $searchUpdatedDdfListingsService
    )
    {
        $this->logger = $logger;
        $this->feedService = $feedService;
        $this->ddfListingMasterService = $ddfListingMasterService;
        $this->searchUpdatedDdfListingsService = $searchUpdatedDdfListingsService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'This command fetched the updates from DDF Feed. Also, makes sure that expired listings are removed and missing are added'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');
        $commandLastRunTimeDate = new \DateTime();

        $io = new SymfonyStyle($input, $output);
        $io->success("Start fetching ddf listings");
        $this->logger->alert('Start fetching ddf listings');

        if ($this->feedService->isFeedBusy(ListingConstants::FeedDdf)) {
            $io->warning('Ddf Feed is busy');
            $this->logger->warning('Ddf Feed is busy');

            return Command::SUCCESS;
        }
        $lastRunTimeDate = $this->feedService->setBusyByFeedName(ListingConstants::FeedDdf, true);
        try {
            $this->searchUpdatedDdfListingsService->searchAndRecordUpdatedListings($lastRunTimeDate);
            $this->ddfListingMasterService->syncListingRecords();

            $this->feedService->setLastRunTimeByFeedName(ListingConstants::FeedDdf, $commandLastRunTimeDate);
        } catch (\Exception|Throwable $e) {
            $io->error($e);
            $this->logger->error($e->getMessage(), $e->getTrace());
            $this->hasError = true;
        } finally {
            $this->feedService->setBusyByFeedName(ListingConstants::FeedDdf, false);
        }

        $this->logger->alert('Done fetching ddf listings');

        return $this->hasError ? Command::FAILURE : Command::SUCCESS;
    }
}
