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

class CreateSingleListingPriceLogCommand extends Command
{
    protected static $defaultName = 'app:create-listing-price-log';
    protected static $defaultDescription = 'Created price logs for a given listing id';

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
            ->addArgument('listingId', InputArgument::REQUIRED, 'Listing ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listingId = $input->getArgument('listingId');

        $listing = $this->listingRepository->getActiveListingById($listingId);

        if ($listing) {
            try {
                $this->priceLogService->createPriceLog($listing);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());

                return Command::FAILURE;
            }
        } else {
            $this->logger->warning('Listing is missing or inactive');
        }

        return Command::SUCCESS;
    }
}
