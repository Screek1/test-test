<?php


namespace App\Service\BusStop;


use App\Service\Geo\HereRouteService;
use App\Service\Geo\Point;
use App\Service\Search\ElasticSearchFactory;

class BusStopService
{
    const INDEX = 'bus-stops';
    private ElasticSearchFactory $elasticSearchFactory;
    private HereRouteService $hereRouteService;

    public function __construct(
        ElasticSearchFactory $elasticSearchFactory,
        HereRouteService $hereRouteService
    ) {
        $this->elasticSearchFactory = $elasticSearchFactory;
        $this->hereRouteService = $hereRouteService;
    }

    public function index($busStop, $index)
    {
        $params = [
            'index' => self::INDEX,
            'id' => $index,
            'body' => $this->constructSearchData($busStop),
        ];

        try {
            return $this->elasticSearchFactory->getClient()->index($params);
        } catch (\Exception $exception) {
            dump($exception);
        }
    }

    public function constructSearchData($busStop): array
    {
        return [
            'id' => (int)$busStop['stop_id'],
            'name' => $busStop['stop_name'],
            'muno' => $busStop['muni'],
            'coordinates' => $this->getCoordinatesObject($busStop)
        ];
    }

    private function getCoordinatesObject($busStop): array
    {
        return [
            'lat' => (float)$busStop['stop_lat'],
            'lon' => (float)$busStop['stop_lon']
        ];
    }

    public function countBusStops()
    {

        $params = [
            'index' => self::INDEX,
            'body' => [
                'query' => ['bool' => ['must' => []]],
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->count($params);

        return $results['count'];
    }

    public function getAllDemographyInMapBox(float $neLat, float $neLng, float $swLat, float $swLng)
    {
        $params = [
            'index' => self::INDEX,
            'body' => [
                'size' => 1000,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'geo_bounding_box' => [
                                'coordinates' => [
                                    "top_left" => [
                                        "lat" => $neLat,
                                        "lon" => $swLng
                                    ],
                                    "bottom_right" => [
                                        "lat" => $swLat,
                                        "lon" => $neLng
                                    ]
                                ],
                            ]
                        ],
                    ]
                ]
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function searchBusStop($listing)
    {
        if (!isset($listing['coordinates']['lat']) && !isset($listing['coordinates']['lon'])) {
            return null;
        }

        $listingCoordinates = new Point($listing['coordinates']['lat'], $listing['coordinates']['lon']);

        if (is_null($listingCoordinates->getLongitude()) && is_null($listingCoordinates->getLatitude())) {
            return null;
        }

        $params = [
            'index' => self::INDEX,
            'body' => [
                'size' => 1,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'geo_distance' => [
                                "distance" => "10000km",
                                "coordinates" => [
                                    "lat" => $listingCoordinates->getLatitude(),
                                    "lon" => $listingCoordinates->getLongitude()
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    '_geo_distance' => [
                        'coordinates' => [
                            "lat" => $listingCoordinates->getLatitude(),
                            "lon" => $listingCoordinates->getLongitude()
                        ],
                        'order' => 'asc',
                        'unit' => 'km'
                    ]
                ]
            ]
        ];

        $result = $this->elasticSearchFactory->getClient()->search($params);

        if (!isset($result['hits']['hits'][0])) {
            return null;
        }

        $data = $result['hits']['hits'][0]['_source'];
        $distance = $this->hereRouteService->computeDistance($listingCoordinates, $data['coordinates']);

        return [
            'subtitle' => $data['muno'],
            'label' => $data['name'],
            'val' => round($distance, 2).' km',
            'details' => '#'
        ];
    }
}