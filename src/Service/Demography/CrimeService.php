<?php


namespace App\Service\Demography;


use App\Entity\School;
use App\Repository\SchoolRepository;
use App\Service\Geo\Point;
use App\Service\Search\ElasticSearchFactory;
use Psr\Log\LoggerInterface;
use function MongoDB\BSON\toJSON;

class CrimeService
{
    const CrimeIndex = 'crime';

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

    public function index($index, $Geometry)
    {
        $params = [
            'index' => self::CrimeIndex,
            'id' => $index,
            'body' => $this->constructSearchData($Geometry),
        ];

        try {
            return $this->elasticSearchFactory->getClient()->index($params);
        } catch (\Exception $exception) {
            dump($exception);
        }
    }

    public function constructSearchData($Geometry)
    {
        return [
            'id' => $Geometry->getData('GEOGRAPHYR'),
            'name' => $Geometry->getData('DISPLAYNAM'),
            'province' => $Geometry->getData('PROVTERRNA'),
            'format' => $Geometry->getData('FORMATTEDV'),
            'description' => $Geometry->getData('NULLDESCRI'),
            'policeStationName' => $Geometry->getData('POLICE_STA'),
            'allViolationsIncidents' => $Geometry->getData('ALL_VIOLAT'),
            'allViolationsRatePerOneHundredThousand' => $Geometry->getData('ALL_VIOL_1'),
            'allViolationsRating' => $Geometry->getData('ALL_VIOL_2'),
            'allViolationsChangeInRate' => $Geometry->getData('ALL_VIOL_3'),
            'criminalCodeViolationsIncidents' => $Geometry->getData('CRIMINAL_C'),
            'criminalCodeViolationsRatePerOneHundredThousand' => $Geometry->getData('CRIMINAL_1'),
            'criminalViolationsRating' => $Geometry->getData('CRIMINAL_V'),
            'criminalCodeViolationsChangeInRate' => $Geometry->getData('CRIMINAL_2'),
            'violentCriminalCodeViolationsIncidents' => $Geometry->getData('VIOLENT_CR'),
            'violentCriminalCodeViolationsRatePerOneHundredThousand' => $Geometry->getData('VIOLENT_1'),
            'violentCrimeRating' => $Geometry->getData('VILOENT_CR'),
            'violentCriminalCodeViolationsChangeInRate' => $Geometry->getData('VIOLENT_2'),
            'propertyCrimeIncidents' => $Geometry->getData('PROPERTY_C'),
            'propertyCrimeRatePerOneHundredThousand' => $Geometry->getData('PROPERTY_1'),
            'propertyCrimeRating' => $Geometry->getData('PROPERTY_2'),
            'propertyCrimeChangeInRate' => $Geometry->getData('PROPERTY_3'),
            'otherCriminalViolationsIncidents' => $Geometry->getData('OTHER_CRIM'),
            'otherCriminalViolationsRatePerOneHundredThousand' => $Geometry->getData('OTHER_CR_1'),
            'otherCrimeRating' => $Geometry->getData('OTHER_CR_2'),
            'otherCriminalViolationsChangeInRate' => $Geometry->getData('OTHER_CR_3'),
            'trafficViolationsIncidents' => $Geometry->getData('TRAFFIC_VI'),
            'trafficViolationsRatePerOneHundredThousand' => $Geometry->getData('TRAFFIC_1'),
            'trafficCrimeRating' => $Geometry->getData('TRAFFIC_CR'),
            'trafficViolationsChangeInRate' => $Geometry->getData('TRAFFIC_2'),
            'drugViolationsIncidents' => $Geometry->getData('DRUG_VIOLA'),
            'drugViolationsRatePerOneHundredThousand' => $Geometry->getData('DRUG_VIO_1'),
            'drugCrimeRating' => $Geometry->getData('DRUG_CRIME'),
            'drugViolationsChangeInRate' => $Geometry->getData('DRUG_VIO_2'),
            'areas' => json_decode($Geometry->getGeoJSON())
        ];
    }

    public function getCrimeArea($Geometry): ?array
    {
        $polygon = json_decode($Geometry->getGeoJSON());

        if (!$polygon) {
            $this->logger->alert('null');
            return null;
        }

        return [
            "type" => $polygon->type,
            "coordinates" => $polygon->coordinates
        ];
    }


    public function getAllCrimeInMapBox(float $neLat, float $neLng, float $swLat, float $swLng)
    {
        $params = [
            'index' => self::CrimeIndex,
            'body' => [
                'size' => 10000,
                'query' => [
                    'bool' => [
                        'filter' => [
                            'geo_bounding_box' => [
                                'areas' => [
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
                ],
            ],
            '_source' => ['allViolationsRating', 'criminalViolationsRating', 'violentCrimeRating', 'propertyCrimeRating', 'otherCrimeRating', 'trafficCrimeRating', 'drugCrimeRating', 'areas'],
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function calculateCrimeIndex()
    {
        $params = [
            'index' => self::CrimeIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => []
                    ]
                ]
            ]
        ];

        $results = $this->elasticSearchFactory->getClient()->count($params);

        return $results['count'] + 1;
    }

    public function getCrimeForSeoDescription($coordinates)
    {
        if (!isset($coordinates['lon']) && !isset($coordinates['lat'])) {
            return null;
        }
        $params = [
            'index' => self::CrimeIndex,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'geo_shape' => [
                                'areas' => [
                                    'shape' => [
                                        'type' => 'point',
                                        'coordinates' => [
                                            (float)$coordinates['lon'],
                                            (float)$coordinates['lat'],
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

        if ($result['hits']['total']['value'] == 0) {
            return null;
        }

        return $result['hits']['hits'][0]['_source'];
    }
}