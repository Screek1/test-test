<?php

namespace App\Command;

use App\Service\Demography\CrimeService;
use App\Service\Demography\DemographyService;
use Psr\Log\LoggerInterface;
use Shapefile\ShapefileReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ParseDemographicDataCommand extends Command
{
    protected static $defaultName = 'app:parse-demography-data';
    protected static $defaultDescription = 'Add a short description for your command';

    private ?DemographyService $demographicService;
    private ?CrimeService $crimeService;
    private LoggerInterface $logger;


    public function __construct(DemographyService $demographicService, LoggerInterface $logger, CrimeService $crimeService)
    {
        parent::__construct();
        $this->demographicService = $demographicService;
        $this->crimeService = $crimeService;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('type', InputArgument::REQUIRED, 'Demographic type')
            ->addArgument('directory', InputArgument::REQUIRED, 'Demographic data directory path')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Demographic data filename')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        $path = $input->getArgument('directory');
        $filename = $input->getArgument('filename') ?? null;

        $finder = new Finder();

        if ($filename) {
            $finder->files()->name($filename . '.shp')->in($path);
        } else {
            $finder->files()->name('*.shp')->in($path);
        }

        if ($type === CrimeService::CrimeIndex) {
            $count = $this->crimeService->calculateCrimeIndex();
            $this->logger->info('Count ' . $count);
            foreach ($finder as $file) {
                $Shapefile = new ShapefileReader($file->getRealPath());
                while ($Geometry = $Shapefile->fetchRecord()) {
                    $this->crimeService->index($count, $Geometry);
                    $count++;
                }
            }
        }

        if ($type === DemographyService::DemographyIndex) {
            $count = $this->demographicService->calculateDemographyIndex();
            foreach ($finder as $file) {
                $Shapefile = new ShapefileReader($file->getRealPath());
                while ($Geometry = $Shapefile->fetchRecord()) {
                    $this->demographicService->index($count, $Geometry);
                    $count++;
                }
            }
        }

        $io->success('Success');

        return Command::SUCCESS;
    }
}
