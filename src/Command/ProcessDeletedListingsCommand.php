<?php

namespace App\Command;

use App\Repository\ListingRepository;
use App\Service\Search\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessDeletedListingsCommand extends Command
{
    protected static $defaultName = 'app:process-deleted-listings';
    protected static $defaultDescription = 'Deletes the marked for deletion listings';

    private ListingRepository $listingRepository;
    private SearchService $searchService;
    private LoggerInterface $logger;

    public function __construct(
        ListingRepository $listingRepository,
        SearchService $searchService,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->searchService = $searchService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $listingsToDelete = $this->listingRepository->findMarkedForDeletion(500);
        $toDeleteCount = count($listingsToDelete);
        if ($toDeleteCount > 0) {

            $this->logger->warning("Deleting ".$toDeleteCount." listings...");

            $this->listingRepository->deleteListings($listingsToDelete);
            $this->searchService->deleteListings($listingsToDelete);
        } else {
            $this->logger->warning("No listings to delete");
        }

        return Command::SUCCESS;
    }
}
