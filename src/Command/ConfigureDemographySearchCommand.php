<?php

namespace App\Command;

use App\Service\Demography\DemographyService;
use App\Service\Search\ElasticSearchFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigureDemographySearchCommand extends Command
{
    private ElasticSearchFactory $elasticSearchFactory;
    private LoggerInterface $logger;

    protected static $defaultName = 'app:configure-demography-search';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(
        ElasticSearchFactory $elasticSearchFactory,
        LoggerInterface $logger,
    ) {
        $this->elasticSearchFactory = $elasticSearchFactory;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = $this->elasticSearchFactory->getClient();

        try {
            $client->indices()->delete(['index' => DemographyService::DemographyIndex]);
        } catch (\Exception $e) {
        }

        $params = [
            'index' => DemographyService::DemographyIndex,
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
        $io->success('Configure demography');
        $this->logger->alert('Configure demography');

        return Command::SUCCESS;
    }
}
