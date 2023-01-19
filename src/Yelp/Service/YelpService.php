<?php


namespace App\Yelp\Service;


use Stevenmaguire\Yelp\ClientFactory;
use Stevenmaguire\Yelp\Exception\InvalidVersionException;
use Stevenmaguire\Yelp\v3\Client;
use Stevenmaguire\Yelp\Version;

class YelpService
{
    private ?Client $client = null;

    private string $yelpApiKey;

    public function __construct(string $yelpApiKey)
    {
        $this->yelpApiKey = $yelpApiKey;
    }

    /**
     * @throws InvalidVersionException
     */
    private function getClient(): Client
    {
        if (!$this->client) {
            $options = [
                'apiKey' => $this->yelpApiKey,
            ];

            $this->client = ClientFactory::makeWith(
                $options,
                Version::THREE
            );
        }

        return $this->client;
    }

    /**
     * @param array $params
     * @return object
     * @throws InvalidVersionException
     */
    public function search(array $params): object
    {
        return $this->getClient()->getBusinessesSearchResults($params);
    }
}