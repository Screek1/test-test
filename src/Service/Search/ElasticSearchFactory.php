<?php


namespace App\Service\Search;


use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

class ElasticSearchFactory
{
    private ?Client $client = null;
    private array $hosts;
    private LoggerInterface $logger;

    public function __construct(
        string $hosts,
        LoggerInterface $logger

    )
    {
        $this->hosts = explode(',', $hosts);
        $this->logger = $logger;
    }

    public function getClient(): Client
    {
        if (!$this->client) {
            $this->client = ClientBuilder::create()->setHosts($this->hosts)->setLogger($this->logger)->build();
        }

        return $this->client;
    }

}