<?php


namespace App\Service\Search;


use App\Criteria\ListingSearchCriteria;
use App\Entity\Listing;
use App\Model\Listing\ListingStatus;
use App\Repository\ListingRepository;
use App\Service\AwsService;
use App\Service\Listing\ListingConstants;
use App\Service\Listing\ListingSearchDataService;
use App\Service\Listing\ListingService;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchService
{
    const ListingIndex = 'listing-new';

    private ListingSearchDataService $listingSearchDataService;
    private ListingRepository $listingRepository;
    private ElasticSearchFactory $elasticSearchFactory;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $router;
    private AwsService $awsService;
    private $appUrl;

    public function __construct(
        ListingSearchDataService $listingSearchDataService,
        ListingRepository        $listingRepository,
        ElasticSearchFactory     $elasticSearchFactory,
        LoggerInterface          $logger,
        UrlGeneratorInterface    $router,
        AwsService               $awsService,
        string                   $appUrl
    )
    {
        $this->listingSearchDataService = $listingSearchDataService;
        $this->listingRepository = $listingRepository;
        $this->elasticSearchFactory = $elasticSearchFactory;
        $this->logger = $logger;
        $this->router = $router;
        $this->appUrl = $appUrl;
        $this->awsService = $awsService;

        $context = $this->router->getContext();
        $context->setBaseUrl($this->appUrl);
    }

    public function deleteListingIndexById($id)
    {
        try {
            return $this->elasticSearchFactory->getClient()->delete(
                [
                    'index' => self::ListingIndex,
                    'id' => $id,
                ]
            );
        } catch (\Exception $exception) {
        }
    }

    public function getOneByIdAndMlsNum(int $id, string $mlsNum): ?array
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['listingId' => $id]],
                            ['match' => ['mlsNumber' => $mlsNum]],
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        if ($results['hits']['total']['value'] == 0) {
            return null;
        }

        return $results['hits']['hits'][0]['_source'];
    }

    public function searchCount(ListingSearchCriteria $criteria)
    {
        $query = $this->criteriaToQuery($criteria);

        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => $query,
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->count($params);

        return $results['count'];
    }

    public function getActiveListingById(int $id)
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['_id' => $id]],
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        if ($results['hits']['total']['value'] == 0) {
            return null;
        }

        return $results['hits']['hits'][0]['_source'];
    }

    public function getActiveByMlsNum(string $mlsNum)
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['mlsNumber' => $mlsNum]],
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        if ($results['hits']['total']['value'] == 0) {
            return null;
        }

        return $results['hits']['hits'][0]['_source'];
    }

    public function search(ListingSearchCriteria $criteria, int $limit = 500): array
    {
        $query = $this->criteriaToQuery($criteria);
        $sort = $this->criteriaToSort($criteria);

        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'size' => $limit,
                'query' => $query,
                'sort' => $sort
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function syncListingById(int $listingId)
    {
        $listing = $this->listingRepository->find($listingId);

        if (!$listing) {
            $this->logger->error("Listing was not found for indexing by listing id: $listingId");
        }

        if ($listing->isActive() && !$this->listingRepository->getIsManualRemoved($listing->getFeedListingID())) {
            return $this->indexListing($listing);
        } else {
            $cloudDestination = 'listings/' . $listing->getFeedID() . '/' . $listing->getFeedListingID() . '/';
            $this->awsService->delete($cloudDestination);
            return $this->deleteListingIndexById($listing->getId());
        }
    }

    public function indexListing(Listing $listing)
    {
        $params = [
            'index' => self::ListingIndex,
            'id' => $listing->getId(),
            'body' => $this->listingSearchDataService->constructSearchListingData($listing),
        ];

        return $this->elasticSearchFactory->getClient()->index($params);
    }

    private function criteriaToQuery(ListingSearchCriteria $criteria)
    {
        $query = [];

        if (!empty($criteria->feedType)) {
            $query['bool']['must'][] = ['match' => ['feedId' => $criteria->feedType]];
        }

        if (!empty($criteria->city)) {
            $query['bool']['must'][] = ['match' => ['address.city' => $criteria->city]];
        }

        if (!empty($criteria->stateOrProvince)) {
            $query['bool']['must'][] = ['match' => ['address.state' => $criteria->stateOrProvince]];
        }

        if (!empty($criteria->minPrice)) {
            $query['bool']['must'][]['range']['financials.listingPrice']['gte'] = $criteria->minPrice;
        }

        if (!empty($criteria->maxPrice)) {
            $query['bool']['must'][]['range']['financials.listingPrice']['lte'] = $criteria->maxPrice;
        }

        if (!empty($criteria->box) && is_string($criteria->box)) {
            list ($neLat, $neLng, $swLat, $swLng) = explode(',', $criteria->box);
            $query['bool']['filter']['geo_bounding_box']['coordinates'] = [
                "top_left" => [
                    "lat" => $neLat,
                    "lon" => $swLng
                ],
                "bottom_right" => [
                    "lat" => $swLat,
                    "lon" => $neLng
                ]
            ];

            if ($neLat < 50.17853708464184 && $neLng < -120.95494620287626 && $swLat > 48.96920305563448 && $neLng > -123.84409588379175) {
                $query['bool']['must'][] = ['match' => ['feedId' => ListingConstants::FeedIdx]];
            }
        }

        if ($criteria->search) {
            $query['bool']['must'][]['multi_match'] = [
                'query' => $criteria->search,
                'fields' => ['mlsNumber', 'description'],
                'type' => 'bool_prefix',
            ];
        }

        if (!empty($criteria->searchType)) {
            switch ($criteria->searchType) {
                case 'new-property':
                    $query['bool']['must'][]['range']['metrics.yearBuilt']['gte'] = Carbon::now()->year;
                    $query['bool']['must'][]['range']['metrics.yearBuilt']['lte'] = Carbon::now()->addYears(30)->year;
                    break;
                case 'commercial':
                case 'Commercial':
                    $query['bool']['must'][]['query_string'] = [
                        'query' => '(Agriculture) or (Business) or (Hospitality) or (Industrial) or (Institutional - Special Purpose) or (Office) or (Retail)',
                        'default_field' => 'type',
                    ];
                    $newArray = [];
                    foreach ($query['bool']['must'] as $element) {
                        if (!isset($element['match']['feedId'])) {
                            $newArray[] = $element;
                        }
                    };
                    $query['bool']['must'] = $newArray;
                    break;
            }
        }

        if (!empty($criteria->minYearBuilt)) {
            $query['bool']['must'][]['range']['metrics.yearBuilt']['gte'] = $criteria->minYearBuilt;
        }
        if (!empty($criteria->maxYearBuilt)) {
            $query['bool']['must'][]['range']['metrics.yearBuilt']['lte'] = $criteria->maxYearBuilt;
        }

        if (!empty($criteria->newConstructionsMinYearBuild)) {
            $query['bool']['must'][]['range']['metrics.yearBuilt']['gte'] = $criteria->newConstructionsMinYearBuild;
        }
        if (!empty($criteria->newConstructionsMaxYearBuild)) {
            $query['bool']['must'][]['range']['metrics.yearBuilt']['lte'] = $criteria->newConstructionsMaxYearBuild;
        }

        if ($criteria->foreclosuresSearch) {
            $query['bool']['must'][]['query_string'] = [
                'query' => '.*(foreclosure|court order|bank owned|Court Order Sale|Court Ordered Sale).*',
                'default_field' => 'description',
            ];
        }

        if ($criteria->showPastWeek) {
            $query['bool']['must'][]['range']['contractDate'] = [
                'gte' => Carbon::now()->subDays(7)->toISOString(),
                'lte' => Carbon::now()->toISOString()
            ];
        }

        if (!empty($criteria->minBeds)) {
            $query['bool']['must'][]['range']['metrics.bedRooms']['gte'] = $criteria->minBeds;
        }
        if (!empty($criteria->maxBeds)) {
            $query['bool']['must'][]['range']['metrics.bedRooms']['lte'] = $criteria->maxBeds;
        }

        if (!empty($criteria->minBaths)) {
            $query['bool']['must'][]['range']['metrics.bathRooms']['gte'] = $criteria->minBaths;
        }
        if (!empty($criteria->maxBaths)) {
            $query['bool']['must'][]['range']['metrics.bathRooms']['lte'] = $criteria->maxBaths;
        }

        if (!empty($criteria->minLivingArea)) {
            $query['bool']['must'][]['range']['metrics.sqrtFootage']['gte'] = $criteria->minLivingArea;
        }
        if (!empty($criteria->maxLivingArea)) {
            $query['bool']['must'][]['range']['metrics.sqrtFootage']['lte'] = $criteria->maxLivingArea;
        }

        if (!empty($criteria->minLotSize)) {
            $query['bool']['must'][]['range']['metrics.lotSize']['gte'] = $criteria->minLotSize;
        }
        if (!empty($criteria->maxLotSize)) {
            $query['bool']['must'][]['range']['metrics.lotSize']['lte'] = $criteria->maxLotSize;
        }

        if (!empty($criteria->propertyTypes)) {
            $query['bool']['must'][] = ['terms' => ['type' => $criteria->propertyTypes]];
        }

        if (!empty($criteria->lastUpdated)) {
            $query['bool']['must'][]['range']['lastUpdate'] = [
                'gte' => Carbon::parse($criteria->lastUpdated)->toISOString()
            ];
        }

        if (!empty($criteria->streetName)) {
            $query['bool']['must'][] = ['match_phrase' => ['address.streetAddress' => $criteria->streetName]];
        }

        if (!count($query)) {
            $query['bool']['must'] = [];
        }

        return $query;
    }

    private function criteriaToSort(ListingSearchCriteria $criteria)
    {
        $sort = [];
        switch ($criteria->sort) {
            case 'new_1':
                $sort[] = ['contractDate' => 'asc'];
                break;
            case 'days_0':
                $sort[] = ['daysOnTheMarket' => 'desc'];
                break;
            case 'days_1':
                $sort[] = ['daysOnTheMarket' => 'asc'];
                break;
            case 'price_0':
                $sort[] = ['financials.listingPrice' => 'desc'];
                break;
            case 'price_1':
                $sort[] = ['financials.listingPrice' => 'asc'];
                break;
            default:
                $sort[] = ['contractDate' => 'desc'];
                break;
        }
        return $sort;
    }

    public function getStreetsByProvince(string $province)
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'address.state' => $province,
                            ],
                        ],
                    ],
                ],
                'aggs' => [
                    'street_count' => [
                        'terms' => [
                            'field' => 'address.streetName',
                            'size' => 499,
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['aggregations']['street_count']['buckets'];
    }

    public function getSelfListings()
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'size' => 30,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    'status' => ListingStatus::VisibleStatuses,
                                ],
                            ],
                            [
                                'term' => [
                                    'selfListing' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function getFeaturedListings()
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    'contractDate' => [
                                        'lte' => Carbon::now()->toISOString()
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort' => [
                    ['contractDate' => ['order' => 'desc']],
                    ['lastUpdate' => ['order' => 'desc']]
                ],
                'size' => 30,
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function getAllCityStats($province)
    {
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'size' => 1,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'address.state' => $province,
                            ],
                        ],
                    ],
                ],
                'aggs' => [
                    'city_count' => [
                        'terms' => [
                            'field' => 'address.city',
                            'size' => 1000,
                        ],
                        'aggs' => [
                            'feed_aggs' => [
                                'terms' => [
                                    'field' => 'feedId'
                                ]
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['aggregations']['city_count']['buckets'];
    }

    public function whichFeedIdMore(ListingSearchCriteria $criteria): string
    {
        if (in_array($criteria->city, ListingConstants::idxCities)) {
            return ListingConstants::FeedIdx;
        }

        if (in_array(ucwords($criteria->search), ListingConstants::idxCities)) {
            return ListingConstants::FeedIdx;
        }

        $max = 0;
        $feedId = 'ddf';
        $query = $this->criteriaToQuery($criteria);

        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'size' => 0,
                'query' => $query,
                'aggs' => [
                    'feeds' => [
                        'terms' => [
                            'field' => 'feedId',
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        foreach ($results['aggregations']['feeds']['buckets'] as $item) {
            if ($item['doc_count'] > $max) {
                $max = $item['doc_count'];
                $feedId = $item['key'];
            }
        }

        return $feedId;
    }

    public function facetsSearch(string $term)
    {
        $result = [];
        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'wildcard' => [
                                'mlsNumber' => '*' . $term . '*',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $paramsAddress = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_bool_prefix' => [
                                'fullAddress' => $term,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $paramsCity = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'multi_match' => [
                                'query' => ucwords(strtolower($term)),
                                'fields' => ['address.city', 'address.state'],
                                'type' => 'bool_prefix',
                            ],
                        ],
                    ],
                ],
                'aggs' => [
                    'city_and_states' => [
                        'multi_terms' => [
                            'terms' => [
                                [
                                    'field' => 'address.city',
                                ],
                                [
                                    'field' => 'address.state',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultsByMLS = $this->elasticSearchFactory->getClient()->search($params);
        $resultByAddress = $this->elasticSearchFactory->getClient()->search($paramsAddress);
        $resultByCity = $this->elasticSearchFactory->getClient()->search($paramsCity);

        foreach ($resultsByMLS['hits']['hits'] as $address) {
            $result[] = [
                'facet' => $address['_source']['mlsNumber'],
                'facet_type' => 'mls_num',
                'rank' => 1,
                'url' => $this->router->getContext()->getBaseUrl() . '/' . $address['_source']['listingUrl'],
            ];
        }

        foreach ($resultByCity['aggregations']['city_and_states']['buckets'] as $item) {
            $result[] = [
                'facet' => $item['key'][0] . ', ' . $item['key'][1],
                'facet_type' => 'landing',
                'rank' => 2,
                'url' => $this->router->generate('listings_map',
                    ['location' => $item['key'][0] . ',' . ListingService::getProvinceCode($item['key'][1])]
                ),
            ];
        }

        foreach ($resultByAddress['hits']['hits'] as $address) {
            $result[] = [
                'facet' => $address['_source']['fullAddress'],
                'facet_type' => 'address',
                'rank' => 3,
                'url' => $this->router->getContext()->getBaseUrl() . '/' . $address['_source']['listingUrl'],
            ];
        }

        return $result;
    }

    public function getAllListingTypes(): array
    {
        $data = [];
        $params = [
            'index' => self::ListingIndex,
            'size' => 0,
            'body' => [
                'aggs' => [
                    'type_agg' => [
                        'terms' => [
                            'field' => 'type',
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        foreach ($results['aggregations']['type_agg']['buckets'] as $result) {
            $data[] = $result['key'];
        }

        return $data;
    }

    public function deleteListings($listings)
    {
        $params = ['body' => []];

        foreach ($listings as $listing) {
            $params['body'][] = [
                'delete' => [
                    '_index' => self::ListingIndex,
                    '_id' => $listing->getId(),
                ],
            ];
        }

        if ($params['body']) {
            $this->elasticSearchFactory->getClient()->bulk($params);
        }
    }

    public function getSimilarListings(
        ?string $type,
        ?string $ownershipType,
        ?int    $bedRooms,
        ?int    $livingArea,
        ?int    $lotSize,
        ?int    $yearBuild,
        ?array  $coordinates,
        ?string $mlsNum
    ): array
    {
        $query = [];
        if ($type) {
            $query['bool']['must'][] = ['term' => ['type' => $type]];
        }
        if ($ownershipType) {
            $query['bool']['must'][] = ['match' => ['ownershipType' => $ownershipType]];
        }
        if (isset($bedRooms)) {
            $query['bool']['must'][]['range']['metrics.bedRooms']['gte'] = $bedRooms - 2;
            $query['bool']['must'][]['range']['metrics.bedRooms']['lte'] = $bedRooms + 2;
        }
        if ($livingArea) {
            if ($livingArea > 1000) {
                $query['bool']['must'][]['range']['metrics.sqrtFootage']['gte'] = $livingArea - 1000;
                $query['bool']['must'][]['range']['metrics.sqrtFootage']['lte'] = $livingArea + 1000;
            } else {
                $query['bool']['must'][]['range']['metrics.sqrtFootage']['lte'] = $livingArea + 1000;
            }
        }
//        if ($lotSize) {
//            if ($lotSize > 500) {
//                $query['bool']['must'][]['range']['metrics.lotSize']['gte'] = $lotSize - 500;
//                $query['bool']['must'][]['range']['metrics.lotSize']['lte'] = $lotSize + 500;
//            } else {
//                $query['bool']['must'][]['range']['metrics.lotSize']['lte'] = $lotSize + 500;
//            }
//        }
        if ($yearBuild) {
            $query['bool']['must'][]['range']['metrics.yearBuilt']['gte'] = $yearBuild - 15;
            $query['bool']['must'][]['range']['metrics.yearBuilt']['lte'] = $yearBuild + 15;
        }
        if (isset($mlsNum)) {
            $query['bool']['must_not'][] = ['match' => ['mlsNumber' => $mlsNum]];
        }

        if ($coordinates && isset($coordinates['lat']) && isset($coordinates['lon'])) {
            $query['bool']['filter']['geo_distance'] = [
                "distance" => "10km",
                "coordinates" => [
                    'lat' => $coordinates['lat'],
                    'lon' => $coordinates['lon'],
                ],
            ];
        }

        $params = [
            'index' => self::ListingIndex,
            'size' => 10,
            'body' => [
                'query' => $query,
            ],
        ];

        $resultsSearch = $this->elasticSearchFactory->getClient()->search($params);

        $result = [];
        foreach ($resultsSearch['hits']['hits'] as $item) {
            $result[] = $item['_source'];
        }

        return $result;
    }

    public function listingForSitemapByStatueOrProvince(string $stateOrProvinces)
    {
        $params = [
            'scroll' => '1m',
            'index' => self::ListingIndex,
            'body' => [
                'size' => 10000,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'address.state' => $stateOrProvinces,
                            ],
                        ],
                    ],
                ],
                'fields' => [
                    'listingUrl',
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        $urls = [];
        while (isset($results['hits']['hits']) && count($results['hits']['hits']) > 0) {
            $scrollId = $results['_scroll_id'];
            foreach ($results['hits']['hits'] as $result) {
                $urls[] = $this->appUrl . '/' . $result['fields']['listingUrl'][0];
            }
            $results = $this->elasticSearchFactory->getClient()->scroll([
                'body' => [
                    'scroll_id' => $scrollId,  //...using our previously obtained _scroll_id
                    'scroll' => '1m'        // and the same timeout window
                ],
            ]);
        }

        unset($results);
        dump(count($urls));

        return $urls;
    }

    public function listingForSitemapByStatueOrProvinceWithImages(string $stateOrProvinces)
    {
        $params = [
            'scroll' => '1m',
            'index' => self::ListingIndex,
            'body' => [
                'size' => 10000,
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'address.state' => $stateOrProvinces,
                            ],
                        ],
                    ],
                ],
                'fields' => [
                    'listingUrl',
                    'images',
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        $urls = [];
        while (isset($results['hits']['hits']) && count($results['hits']['hits']) > 0) {
            $scrollId = $results['_scroll_id'];
            foreach ($results['hits']['hits'] as $result) {
                if (isset($result['fields']['images'])) {
                    $urls[] = [
                        'url' => $this->appUrl . '/' . $result['fields']['listingUrl'][0],
                        'images' => $result['fields']['images'],
                    ];
                }
            }
            $results = $this->elasticSearchFactory->getClient()->scroll([
                'body' => [
                    'scroll_id' => $scrollId,  //...using our previously obtained _scroll_id
                    'scroll' => '1m'        // and the same timeout window
                ],
            ]);
        }

        unset($results);
        dump(count($urls));

        return $urls;
    }

    public function getCanonicalLink(string $mlsNum, $listing): ?string
    {
        if ($listing['feedId'] === ListingConstants::FeedIdx) return null;

        $params = [
            'index' => self::ListingIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['mlsNumber' => $mlsNum]],
                        ],
                    ],
                ],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        if ($results['hits']['total']['value'] == 0) return null;

        if ($results['hits']['total']['value'] < 2) return null;

        $canonicalLink = null;
        foreach ($results['hits']['hits'] as $listing) {
            $data = $listing['_source'];

            if ($data['feedId'] === ListingConstants::FeedIdx) {
                $canonicalLink = $this->appUrl . '/' . $data['listingUrl'];
            }

        }

        return $canonicalLink;
    }
}