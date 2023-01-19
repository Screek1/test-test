<?php


namespace App\Service\Search;


use App\Entity\School;
use App\Repository\SchoolRepository;
use App\Service\Listing\ListingService;
use Psr\Log\LoggerInterface;

class SchoolSearchService
{
    const SchoolIndex = 'school';

    private ElasticSearchFactory $elasticSearchFactory;
    private SchoolRepository $schoolRepository;
    private LoggerInterface $logger;

    public function __construct(
        ElasticSearchFactory $elasticSearchFactory,
        SchoolRepository $schoolRepository,
        LoggerInterface $logger
    )
    {
        $this->schoolRepository = $schoolRepository;
        $this->elasticSearchFactory = $elasticSearchFactory;
        $this->logger = $logger;
    }

    public function indexSchool(School $school)
    {
        $params = [
            'index' => self::SchoolIndex,
            'id' => $school->getId(),
            'body' => $this->constructSearchSchoolData($school),
        ];
        try {
            return $this->elasticSearchFactory->getClient()->index($params);
        } catch (\Exception $exception) {
            return new \Exception('Error: ' . $exception->getMessage());
        }
    }

    public function indexSchoolWithRating($school, $extraSchoolData)
    {
        $params = [
            'index' => self::SchoolIndex,
            'id' => $school['id'],
            'body' => $this->addRatingToSchoolData($school, $extraSchoolData)
        ];
        try {
            return $this->elasticSearchFactory->getClient()->index($params);
        } catch (\Exception $exception) {
            return new \Exception('Error: ' . $exception->getMessage());
        }
    }

    public function deleteSchoolIndexById($id)
    {
        try {
            return $this->elasticSearchFactory->getClient()->delete(
                [
                    'index' => self::SchoolIndex,
                    'id' => $id,
                ]
            );
        } catch (\Exception $exception) {
        }
    }

    public function getAllSchoolsInMapBox(float $neLat, float $neLng, float $swLat, float $swLng)
    {
        $params = [
            'index' => self::SchoolIndex,
            'body' => [
                'size' => 10000,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'geo_polygon' => [
                                'coordinates' => [
                                    'points' => [
                                        ['lat' => $neLat, 'lon' => $neLng],
                                        ['lat' => $swLat, 'lon' => $neLng],
                                        ['lat' => $swLat, 'lon' => $swLng],
                                        ['lat' => $neLat, 'lon' => $swLng]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function constructSearchSchoolData(School $school)
    {
        return [
            'id' => $school->getId(),
            'name' => $school->getSchoolName(),
            'rating' => $school->getRating(),
            'street' => $school->getStreet(),
            'city' => $school->getCity(),
            'state' => $school->getState(),
            'level' => $school->getLevel(),
            'grades' => $school->getGrades(),
            'lang' => $school->getLang(),
            'public' => $school->getPublic(),
            'program' => $school->getProgram(),
            'uri' => $school->getWebUrl(),
            'coordinates' => $this->getSingleSchoolCoordinatesObject($school),
            'areas' => $this->getSingleSchoolAreasCoordinatesObject($school),
        ];
    }

    public function addRatingToSchoolData($school, $extraSchoolData)
    {
        $data = $school;

        if ($extraSchoolData[6]) {
            $data['rating'] = $extraSchoolData[6];
        }

        if ($extraSchoolData[8]) {
            $data['secondaryRating'] = $extraSchoolData[8];
        }

        return $data;
    }

    private function getSingleSchoolCoordinatesObject(School $school)
    {
        return [
            'lat' => $school->getCoordinates()->getLatitude(),
            'lon' => $school->getCoordinates()->getLongitude(),
        ];
    }

    private function getSingleSchoolAreasCoordinatesObject(School $school): ?array
    {
        $this->logger->alert('School: ' . $school->getId());
        $area = $school->getAreas();
        $data = null;
        $newArea = [];
        if ($area) {
            $area = $area->getPoints();
            $size = count($area);
            $fixedArea = [];

            foreach ($area as $index => $coordinates) {
                if ($index == 0 || ($index <= ($size - 1) && $coordinates != $area[$index - 1])) {
                    $fixedArea[] = $coordinates;
                }
            }
            foreach ($fixedArea as $coordinates) {
                $newArea[] = [
                    $coordinates->getLatitude(),
                    $coordinates->getLongitude()
                ];
            }
            $data = [
                "type" => "polygon",
                "coordinates" => [$newArea]
            ];
        }
        return $data;
    }

    /**
     * @deprecated
     */
    public function syncSchools()
    {
        $offset = 0;
        $totalSchool = $this->schoolRepository->countSchool();

        do {
            $schools = $this->schoolRepository->getAllSchools($offset);
            foreach ($schools as $school) {
                $this->indexSchool($school);
            }
            $offset += 100;
        } while ($totalSchool > $offset);
    }

    public function getAllSchoolsWithPagination($page, $size)
    {
        $data = [];
        $offset = ($page - 1) * $size;
        $params = [
            'index' => self::SchoolIndex,
            'body' => [
                'from' => $offset,
                'size' => $size,
                'track_total_hits' => true,
                "sort" => [
                    'id' => [
                        'order' => 'asc',
                    ],
                ]
            ],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        foreach ($results['hits']['hits'] as $result) {
            $data['schools'][] = $result['_source'];
        }
        $data['total'] = ceil($results['hits']['total']['value'] / 50);
        $data['currentPage'] = $page;
        return $data;
    }


    public function getSchoolById($id)
    {
        $params = [
            'index' => self::SchoolIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['_id' => $id]]
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->elasticSearchFactory->getClient()->search($params);

        if ($result['hits']['total']['value'] == 0) {
            return null;
        }

        return $result['hits']['hits'][0]['_source'];
    }

    public function getPublicSchools($listingCoordinates)
    {
        $data = [];

        if (is_null($listingCoordinates->getLongitude()) && is_null($listingCoordinates->getLatitude())) {
            return $data;
        }
        $params = [
            'index' => self::SchoolIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['public' => true]]
                        ],
                        'filter' => [
                            'geo_shape' => [
                                'areas' => [
                                    'shape' => [
                                        'type' => 'point',
                                        'coordinates' => [
                                            (float)$listingCoordinates->getLongitude(),
                                            (float)$listingCoordinates->getLatitude(),
                                        ]
                                    ],
                                    "relation" => "intersects"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $result = $this->elasticSearchFactory->getClient()->search($params);

        foreach ($result['hits']['hits'] as $school) {
            $data[] = $school['_source'];
        }

        return $data;
    }

    public function getPrivateSchools($listingCoordinates)
    {
        $privateSchools = [];

        if (is_null($listingCoordinates->getLongitude()) && is_null($listingCoordinates->getLatitude())) {
            return $privateSchools;
        }
        $params = [
            'index' => self::SchoolIndex,
            'body' => [
                'size' => 1,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['public' => false]]
                        ],
                        'should' => [
                            'wildcard' => [
                                'level' => '*Elementary*'
                            ]
                        ],
                        'filter' => [
                            'geo_distance' => [
                                "distance" => "10000km",
                                "coordinates" => [
                                    "lat" => (float)$listingCoordinates->getLatitude(),
                                    "lon" => (float)$listingCoordinates->getLongitude()
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    '_geo_distance' => [
                        'coordinates' => [
                            "lat" => (float)$listingCoordinates->getLatitude(),
                            "lon" => (float)$listingCoordinates->getLongitude()
                        ],
                        'order' => 'asc',
                        'unit' => 'km'
                    ]
                ]
            ]
        ];

        $result = $this->elasticSearchFactory->getClient()->search($params);

        if (isset($result['hits']['hits'][0])) {
            $privateSchools['elementary'][] = $result['hits']['hits'][0]['_source'];
        }

        $paramsSecondarySchool = [
            'index' => self::SchoolIndex,
            'body' => [
                'size' => 1,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['public' => false]]
                        ],
                        'should' => [
                            'wildcard' => [
                                'level' => '*Secondary*'
                            ]
                        ],
                        'filter' => [
                            'geo_distance' => [
                                "distance" => "10000km",
                                "coordinates" => [
                                    "lat" => (float)$listingCoordinates->getLatitude(),
                                    "lon" => (float)$listingCoordinates->getLongitude()
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    '_geo_distance' => [
                        'coordinates' => [
                            "lat" => (float)$listingCoordinates->getLatitude(),
                            "lon" => (float)$listingCoordinates->getLongitude()
                        ],
                        'order' => 'asc',
                        'unit' => 'km'
                    ]
                ]
            ]
        ];

        $resultSecondarySchool = $this->elasticSearchFactory->getClient()->search($paramsSecondarySchool);

        if (isset($resultSecondarySchool['hits']['hits'][0])) {
            $privateSchools['secondary'][] = $resultSecondarySchool['hits']['hits'][0]['_source'];
        }

        return $privateSchools;
    }

    public function getSchoolByData($schoolData)
    {
        $query = $this->configureSearch($schoolData);
        $params = [
            'index' => self::SchoolIndex,
            'body' => [
                'query' => $query
            ]
        ];

        $result = $this->elasticSearchFactory->getClient()->search($params);

        if ($result['hits']['total']['value'] == 0) {
            return null;
        }

        return $result['hits']['hits'][0]['_source'];
    }

    protected function configureSearch($schoolData): array
    {
        $query = [];

        if ($schoolData[0]) {
            $query['bool']['must'][]['match'] = ['name' => $schoolData[0]];
        }

        if ($schoolData[2]) {
            $query['bool']['must'][]['match'] = ['street' => $schoolData[2]];
        }

        if ($schoolData[4]) {
            $query['bool']['must'][]['match'] = ['state' => ListingService::getProvinceCode($schoolData[3])];
        }

        if ($schoolData[4]) {
            $query['bool']['must'][]['match'] = ['city' => $schoolData[4]];
        }

        return $query;
    }
}