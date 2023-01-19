<?php

namespace App\Command;

use App\Service\BusStop\BusStopService;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ParseBusStopsDataCommand extends Command
{
    protected static $defaultName = 'app:parse-bus-stops-data';
    protected static $defaultDescription = 'Add a short description for your command';
    private BusStopService $busStopService;

    public function __construct(BusStopService $busStopService)
    {
        $this->busStopService = $busStopService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to bus stops file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        $busStops = Reader::createFromPath($filePath);

        $busStops->setHeaderOffset(0);
        $records = $busStops->getRecords();

        $total = $this->busStopService->countBusStops();
        foreach ($records as $busStop) {
            $total++;
            $this->busStopService->index($busStop, $total);
        }

        $io->success('Success.');
        return Command::SUCCESS;
    }
}
