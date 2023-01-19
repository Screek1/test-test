<?php

namespace App\Service\Demography;


use App\Service\Geo\Point;
use App\Service\Search\ElasticSearchFactory;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Psr\Log\LoggerInterface;
use Shapefile\ShapefileAutoloader;
use Shapefile\ShapefileReader;
use Symfony\Component\Finder\Finder;

class DemographyService
{
    const DemographyIndex = 'demography';

    private ElasticSearchFactory $elasticSearchFactory;
    private LoggerInterface $logger;

    public function __construct(ElasticSearchFactory $elasticSearchFactory, LoggerInterface $logger)
    {
        $this->elasticSearchFactory = $elasticSearchFactory;
        $this->logger = $logger;
    }

    public function index($index, $Geometry)
    {
        $params = [
            'index' => self::DemographyIndex,
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
            'csduid' => $Geometry->getData('CSDUID'),
            'csdname' => $Geometry->getData('CSDNAME'),
            'csdtype' => $Geometry->getData('CSDTYPE'),
            'pruid' => $Geometry->getData('PRUID'),
            'prname' => $Geometry->getData('PRNAME'),
            'cduid' => $Geometry->getData('CDUID'),
            'cdname' => $Geometry->getData('CDNAME'),
            'cdtype' => $Geometry->getData('CDTYPE'),
            'ccsuid' => $Geometry->getData('CCSUID'),
            'ccsname' => $Geometry->getData('CCSNAME'),
            'eruid' => $Geometry->getData('ERUID'),
            'ername' => $Geometry->getData('ERNAME'),
            'saccode' => $Geometry->getData('SACCODE'),
            'sactype' => $Geometry->getData('SACTYPE'),
            'cmauid' => $Geometry->getData('CMAUID'),
            'cmapuid' => $Geometry->getData('CMAPUID'),
            'cmaname' => $Geometry->getData('CMANAME'),
            'cmatype' => $Geometry->getData('CMATYPE'),
            'ctuid' => $Geometry->getData('CTUID'),
            'ctname' => $Geometry->getData('CTNAME'),
            'layer' => $Geometry->getData('LAYER'),
            'path' => $Geometry->getData('PATH'),
            'geoId' => $Geometry->getData('GEO_ID'),
            'geoId10' => $Geometry->getData('GEO_ID10'),
            'id10' => $Geometry->getData('ID10'),
            'geoCode' => $Geometry->getData('GEO_CODE'),
            'geoName' => $Geometry->getData('GEO_NAME'),
            'type' => $Geometry->getData('TYPE'),
            'altGeoCo' => $Geometry->getData('ALT_GEO_CO'),
            '1OfOw' => $Geometry->getData('_1_OF_OW'),
            '1rOfO' => $Geometry->getData('_1R_OF_O'),
            '2OfOw' => $Geometry->getData('_2_OF_OW'),
            '2rOfO' => $Geometry->getData('_2R_OF_O'),
            '3OfTe' => $Geometry->getData('_3_OF_TE'),
            '3rOfT' => $Geometry->getData('_3R_OF_T'),
            '4OfTe' => $Geometry->getData('_4_OF_TE'),
            '4rOfT' => $Geometry->getData('_4R_OF_T'),
            '5Average' => $Geometry->getData('_5_AVERAGE'),
            '5rAverag' => $Geometry->getData('_5R_AVERAG'),
            '6Average' => $Geometry->getData('_6_AVERAGE'),
            '6rAverag' => $Geometry->getData('_6R_AVERAG'),
            '7Average' => $Geometry->getData('_7_AVERAGE'),
            '7rAverag' => $Geometry->getData('_7R_AVERAG'),
            '8Average' => $Geometry->getData('_8_AVERAGE'),
            '8rAverag' => $Geometry->getData('_8R_AVERAG'),
            '9Average' => $Geometry->getData('_9_AVERAGE'),
            '9rAverag' => $Geometry->getData('_9R_AVERAG'),
            '10Engl' => $Geometry->getData('_10_ENGL'),
            '10rEng' => $Geometry->getData('_10R_ENG'),
            '11OfE' => $Geometry->getData('_11_OF_E'),
            '11rOf' => $Geometry->getData('_11R_OF'),
            '12OfF' => $Geometry->getData('_12_OF_F'),
            '12rOf' => $Geometry->getData('_12R_OF'),
            '13OfN' => $Geometry->getData('_13_OF_N'),
            '13rOf' => $Geometry->getData('_13R_OF'),
            '14OfM' => $Geometry->getData('_14_OF_M'),
            '14rOf' => $Geometry->getData('_14R_OF'),
            '15Median' => $Geometry->getData('_15_MEDIAN'),
            '15rMedia' => $Geometry->getData('_15R_MEDIA'),
            '16Median' => $Geometry->getData('b _16_MEDIAN'),
            '16rMedia' => $Geometry->getData('_16R_MEDIA'),
            '17OfW' => $Geometry->getData('_17_OF_W'),
            '17rOf' => $Geometry->getData('_17R_OF'),
            '18OfW' => $Geometry->getData('_18_OF_W'),
            '18rOf' => $Geometry->getData('_18R_OF'),
            '19Popula' => $Geometry->getData('_19_POPULA'),
            '19rPopul' => $Geometry->getData('_19R_POPUL'),
            '20Popula' => $Geometry->getData('_20_POPULA'),
            '20rPopul' => $Geometry->getData('_20R_POPUL'),
            '21Public' => $Geometry->getData('_21_PUBLIC'),
            '21rPubli' => $Geometry->getData('_21R_PUBLI'),
            '22Bycycl' => $Geometry->getData('_22_BICYCL'),
            '22rBycyc' => $Geometry->getData('_22R_BICYC'),
            '23CarT' => $Geometry->getData('_23_CAR_T'),
            '23rCar' => $Geometry->getData('_23R_CAR'),
            '24Walked' => $Geometry->getData('_24_WALKED'),
            '24rWalke' => $Geometry->getData('_24R_WALKE'),
            '25NoCer' => $Geometry->getData('_25_NO_CER'),
            '25rNoCe' => $Geometry->getData('_25R_NO_CE'),
            '26Univer' => $Geometry->getData('_26_UNIVER'),
            '26rUnive' => $Geometry->getData('_26R_UNIVE'),
            '27OfR' => $Geometry->getData('_27_OF_R'),
            '27rOf' => $Geometry->getData('_27R_OF'),
            '28Unempl' => $Geometry->getData('_28_UNEMPL'),
            '28rUnemp' => $Geometry->getData('_28R_UNEMP'),
            '29Popula' => $Geometry->getData('_29_POPULA'),
            '30Total' => $Geometry->getData('_30_TOTAL'),
            'areas' =>  $this->getArea($Geometry)
        ];
    }

    public function getArea($Geometry): ?array
    {
        $polygon = json_decode($Geometry->getGeoJSON());

        if (!$polygon) {
            $this->logger->alert('null');
            $this->logger->alert($Geometry->getGeoJSON());
            return null;
        }

        return [
            "type" => $polygon->type,
            "coordinates" => $polygon->coordinates
        ];
    }

    public function getAllDemographyInMapBox(float $neLat, float $neLng, float $swLat, float $swLng)
    {
        $params = [
            'index' => self::DemographyIndex,
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
                ]
            ],
            '_source' => ['1rOfO', '2rOfO', '3rOfT', '4rOfT', '5rAverag', '6rAverag', '7rAverag', '8rAverag',
                '9rAverag', '10rEng', '11rOf', '12rOf', '13rOf', '14rOf', '15rMedia', '16rMedia', '17rOf', '18rOf',
                '19rPopul', '20rPopul', '21rPubli', '22rBycyc', '23rCar', '24rWalke', '25rNoCe', '26rUnive',
                '27rOf', '28rUnemp', 'areas'
            ]
        ];

        $results = $this->elasticSearchFactory->getClient()->search($params);

        return $results['hits']['hits'];
    }

    public function calculateDemographyIndex()
    {
        $params = [
            'index' => self::DemographyIndex,
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

    public function searchDemography($listing)
    {
        if (!isset($listing['coordinates']['lat']) && !isset($listing['coordinates']['lon'])) {
            return null;
        }

        $listingCoordinates = new Point($listing['coordinates']['lat'], $listing['coordinates']['lon']);

        if (is_null($listingCoordinates->getLongitude()) && is_null($listingCoordinates->getLatitude())) {
            return null;
        }

        $params = [
            'index' => self::DemographyIndex,
            'body' => [
                'query' => [
                    'bool' => [
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

        if ($result['hits']['total']['value'] == 0) {
            return null;
        }

        return $result['hits']['hits'][0]['_source'];
    }
}