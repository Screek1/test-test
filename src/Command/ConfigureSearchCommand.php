<?php

namespace App\Command;

use App\Service\Search\ElasticSearchFactory;
use App\Service\Search\SearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureSearchCommand extends Command
{
    protected static $defaultName = 'app:configure-search';
    protected static $defaultDescription = 'Add a short description for your command';

    private ElasticSearchFactory $elasticSearchFactory;

    public function __construct(
        ElasticSearchFactory $elasticSearchFactory
    )
    {
        parent::__construct();
        $this->elasticSearchFactory = $elasticSearchFactory;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->elasticSearchFactory->getClient();

        try {
            $client->indices()->delete(['index' => SearchService::ListingIndex]);
        } catch (\Exception $e) {
        }

        $params = [
            'index' => SearchService::ListingIndex,
            'body' => [
                "mappings" => [
                    'properties' => [
                        "coordinates" => [
                            "type" => "geo_point",
                        ],
                        'address.streetName' => [
                            'type' => 'keyword'
                        ],
                        'feedId' => [
                            'type' => 'keyword'
                        ],
                        'address.city' => [
                            'type' => 'keyword'
                        ],
                        'address.state' => [
                            'type' => 'keyword'
                        ],
                        'type' => [
                            'type' => 'keyword'
                        ],
                        'contractDate' => [
                            'type' => 'date'
                        ],
                        'lastUpdate' => [
                            'type' => 'date'
                        ]
                    ],
                ],
            ],
        ];
        $client->indices()->create($params);

        return Command::SUCCESS;
    }
}
