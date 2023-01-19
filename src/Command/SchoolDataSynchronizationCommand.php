<?php

namespace App\Command;

use App\Service\Geo\Point;
use App\Service\Geo\Polygon;
use App\Service\School\SchoolService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SchoolDataSynchronizationCommand extends Command
{
    protected static $defaultName = 'app:school-data-synchronization';
    private SchoolService $schoolService;
    private string $projectDir;

    public function __construct(SchoolService $schoolService, string $projectDir)
    {
        $this->schoolService = $schoolService;
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Inserting school data from file to database')
            ->addArgument('directory', InputArgument::REQUIRED, 'School data directory path')
            ->addArgument('filename', InputArgument::OPTIONAL, 'School data filename')
            ->addArgument('truncate', InputArgument::OPTIONAL, 'Truncate table? (0/1)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit','2048M');
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('directory');
        $filename = $input->getArgument('filename') ?? null;
        $truncate = (bool)$input->getArgument('truncate') ?? true;

        if (substr($path,0,1) != '/')
        {
            $filePath = $this->projectDir . '/' . str_ireplace('../', '', $path);
        }

        $schoolsIndexed = $this->schoolService->index($path, $truncate, $filename);

        $io->success("Successfully indexed $schoolsIndexed schools from $filePath file");
        if ($filename) {$io->success("Indexed filename $filename");}

        return Command::SUCCESS;
    }
}
