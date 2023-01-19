<?php

namespace App\Command;

use App\Model\Listing\ListingStatus;
use App\Repository\ListingRepository;
use App\Service\Search\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchReindexAllCommand extends Command
{
    protected static $defaultName = 'app:search-reindex-all';
    protected static $defaultDescription = 'Add a short description for your command';

    private ListingRepository $listingRepository;
    private SearchService $searchService;
    private LoggerInterface $logger;

    const LIMIT = 50;

    public function __construct(
        LoggerInterface $logger,
        ListingRepository $listingRepository,
        SearchService $searchService)
    {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->searchService = $searchService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2048M');
        $totalListings = $this->listingRepository->count([
            'status' => ListingStatus::VisibleStatuses,
            'deletedDate' => null,
        ]);

        $this->logger->info('Total listings: ' . $totalListings);
        $offset = 0;
        do {
            $this->logger->info('offset: ' . $offset);
            $listings = $this->listingRepository->findBy(['status' => ListingStatus::VisibleStatuses, 'deletedDate' => null,], null, self::LIMIT, $offset);
            foreach ($listings as $listing) {
                $this->logger->info('Listing ID: ' . $listing->getId());
                $this->searchService->indexListing($listing);
            }
            $offset += self::LIMIT;
        } while ($totalListings > $offset);

        return Command::SUCCESS;
    }
}
