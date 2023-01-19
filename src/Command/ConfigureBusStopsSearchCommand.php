<?php

namespace App\Command;

use App\Service\BusStop\BusStopService;
use App\Service\Search\ElasticSearchFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigureBusStopsSearchCommand extends Command
{
    protected static $defaultName = 'app:configure-bus-stops-search';
    protected static $defaultDescription = 'Add a short description for your command';
    private ElasticSearchFactory $elasticSearchFactory;

    public function __construct(ElasticSearchFactory $elasticSearchFactory)
    {
        $this->elasticSearchFactory = $elasticSearchFactory;
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

        $client = $this->elasticSearchFactory->getClient();

        try {
            $client->indices()->delete(['index' => BusStopService::INDEX]);
        } catch (\Exception $e) {
        }

        $params = [
            'index' => BusStopService::INDEX,
            'body' => [
                "mappings" => [
                    'properties' => [
                        "coordinates" => [
                            "type" => "geo_point",
                        ],
                    ],
                ],
            ],
        ];
        $client->indices()->create($params);
        $io->success('Configure bus stops');

        return Command::SUCCESS;
    }
}
