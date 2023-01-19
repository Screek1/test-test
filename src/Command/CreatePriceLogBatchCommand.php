<?php

namespace App\Command;

use App\Repository\ListingRepository;
use App\Service\Listing\PriceLogService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePriceLogBatchCommand extends Command
{
    protected static $defaultName = 'app:create-price-log-batch';
    protected static $defaultDescription = 'Created price logs for current date for a batch of listings.';

    private ListingRepository $listingRepository;
    private PriceLogService $priceLogService;
    private LoggerInterface $logger;

    public function __construct(
        ListingRepository $listingRepository,
        PriceLogService $priceLogService,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->priceLogService = $priceLogService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('batch', InputArgument::OPTIONAL, 'Batch size', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batch = $input->getArgument('batch');

        $listings = $this->listingRepository->getListingWithMissingPriceLog($batch);

        foreach ($listings as $listing) {
            if (!$listing->getListPrice()) {
                try {
                    $this->priceLogService->createPriceLog($listing);
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage(), $e->getTrace());
                }
            } else {
                $this->logger->warning("Missing list price for listing {$listing->getId()}");
                return Command::SUCCESS;
            }
        }

        return Command::SUCCESS;
    }
}
