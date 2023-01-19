<?php

namespace App\Command;

use App\Repository\CityPhoneRepository;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCityNumbersCommand extends Command
{
    private CityPhoneRepository $cityPhoneRepository;
    protected static $defaultName = 'app:parse-city-numbers-data';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(CityPhoneRepository $cityPhoneRepository)
    {
        $this->cityPhoneRepository = $cityPhoneRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cityNumbers = Reader::createFromPath('dataFiles/cityNumbers/group cities.csv');

        $cityNumbers->setHeaderOffset(0);
        $records = $cityNumbers->getRecords();


        foreach ($records as $cityNumber) {
            $this->cityPhoneRepository->createFromCommand($cityNumber);
        }

        return Command::SUCCESS;
    }
}
