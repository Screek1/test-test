<?php

namespace App\Command;

use App\Service\SavedSearch\SavedSearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NewListingAlertsCommand extends Command
{
    protected static $defaultName = 'app:new-listings-alerts';
    protected static $defaultDescription = 'This command distributes the new listings alerts for all saved searches';

    private SavedSearchService $savedSearchService;

    public function __construct(SavedSearchService $savedSearchService)
    {
        parent::__construct();
        $this->savedSearchService = $savedSearchService;
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->savedSearchService->distributeNewListingsAlerts();

        return Command::SUCCESS;
    }
}
