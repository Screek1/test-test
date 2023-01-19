<?php

namespace App\Command;

use App\Service\Demography\CrimeService;
use App\Service\Search\ElasticSearchFactory;
use App\Service\Search\SchoolSearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigureCrimeSearchCommand extends Command
{
    private ElasticSearchFactory $elasticSearchFactory;

    protected static $defaultName = 'app:configure-crime-search';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(
        ElasticSearchFactory $elasticSearchFactory
    ) {
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
            $client->indices()->delete(['index' => CrimeService::CrimeIndex]);
        } catch (\Exception $e) {
        }

        $params = [
            'index' => CrimeService::CrimeIndex,
            'body' => [
                "mappings" => [
                    'properties' => [
                        'areas' => [
                            'type' => 'geo_shape'
                        ]
                    ],
                ],
            ],
        ];
        $client->indices()->create($params);
        $io->success('Configure crime');

        return Command::SUCCESS;
    }
}
