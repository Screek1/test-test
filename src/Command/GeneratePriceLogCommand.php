<?php

namespace App\Command;

use App\Entity\Listing;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GeneratePriceLogCommand extends Command
{
    protected static $defaultName = 'app:generate-price-log';
    protected static $defaultDescription = 'Generates mock price log data for a single listing';

    private ListingRepository $listingRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ListingRepository $listingRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('listingId', InputArgument::REQUIRED, 'Listing ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $listingId = $input->getArgument('listingId');

        $listing = $this->listingRepository->getActiveListingById($listingId);

        if (!$listing instanceof Listing) {
            $io->error("Listing not found");

            return Command::FAILURE;
        }

        $rsm = new ResultSetMapping();
        $this->entityManager->createNativeQuery(
            "delete from price_log where listing_id = :listingId",
            $rsm
        )->setParameter('listingId', $listingId)->execute();

        foreach (range(0, 365) as $number) {
            $this->entityManager->createNativeQuery(
                "insert into price_log (listing_id, date, price, city_average, subdivision_average)
values (:listingId, CURRENT_DATE - INTERVAL '".$number." days', (select (random() * 500000) + 500000), (select (random() * 500000) + 500000), (select (random() * 500000) + 500000))",
                $rsm
            )
                ->setParameter('listingId', $listingId)->execute();
        }


        $io->success('Done');

        return Command::SUCCESS;
    }
}
