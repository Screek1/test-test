<?php

namespace App\Command;

use App\Service\Search\SchoolSearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;

class FillSchoolRatingCommand extends Command
{
    protected static $defaultName = 'command:fill-school-rating';
    protected static $defaultDescription = 'Add a short description for your command';

    private SchoolSearchService $schoolSearchService;

    public function __construct(SchoolSearchService $schoolSearchService)
    {
        $this->schoolSearchService = $schoolSearchService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $spreadsheet = $this->readFile('dataFiles/schools/SCHOOLS_RATINGS_DONE_2022.xlsx');
        $data = $this->createDataFromSpreadsheet($spreadsheet);

        foreach ($data['Sheet1']['columnValues'] as $item) {
//            Column Names
//            0 => "Full Name"
//            1 => "Short Name",
//            2 => "address",
//            3 => "province",
//            4 => "city",
//            5 => "level",
//            6 => "Elementary Rating",
//            7 => "Elementary Text Rating",
//            8 => "Secondary Rating",
//            9 => "Secondary Text Rating",
//            10 => "language",
//            11 => "board"

            $school = $this->schoolSearchService->getSchoolByData($item);
            if (!$school) { continue; }
            $this->schoolSearchService->indexSchoolWithRating($school, $item);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    protected function readFile($filename): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        return $reader->load($filename);
    }

    protected function createDataFromSpreadsheet($spreadsheet)
    {
        $data = [];
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $data[$worksheetTitle] = [
                'columnNames' => [],
                'columnValues' => [],
            ];
            foreach ($worksheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
                if ($rowIndex > 2) {
                    $data[$worksheetTitle]['columnValues'][$rowIndex] = [];
                }
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Loop over all cells, even if it is not set
                foreach ($cellIterator as $cell) {
                    if ($rowIndex === 1) {
                        $data[$worksheetTitle]['columnNames'][] = $cell->getCalculatedValue();
                    }
                    if ($rowIndex > 2) {
                        $data[$worksheetTitle]['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }
        }

        return $data;
    }

}
