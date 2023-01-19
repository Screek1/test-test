<?php

namespace App\Command;

use App\Service\Feed\SearchUpdatedIdxListingsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetAllActiveListingsFromIdxCommand extends Command
{
    private SearchUpdatedIdxListingsService $searchUpdatedIdxListingsService;
    protected static $defaultName = 'app:get-all-active-idx-listing';
    protected static $defaultDescription = 'Add a short description for your command';


    public function __construct(SearchUpdatedIdxListingsService $searchUpdatedIdxListingsService)
    {
        $this->searchUpdatedIdxListingsService = $searchUpdatedIdxListingsService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('classId', InputArgument::REQUIRED, 'Listing Class ID: RD_1, RA_2, MF_3, LD_4, RT_5')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classId = $input->getArgument('classId');

        if (!$classId) {
            return Command::FAILURE;
        }

        $this->searchUpdatedIdxListingsService->searchActiveListings($classId);

        return Command::SUCCESS;
    }
}
