<?php


namespace App\Service\Utils;


use App\Criteria\ListingSearchCriteria;
use App\Service\Listing\ListingConstants;
use App\Service\Listing\ListingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class UrlUtils
{
    private UrlGeneratorInterface $urlGenerator;
    private ListingService $listingService;
    private RouterInterface $router;
    private LoggerInterface $logger;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ListingService        $listingService,
        RouterInterface       $router,
        LoggerInterface       $logger
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->listingService = $listingService;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function uriToSearchCriteria(string $uri): ?ListingSearchCriteria
    {
        $request = Request::create($uri);

        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->router->getRouteCollection(), $context);

        $attributes = $matcher->match($request->getPathInfo());

        if (empty($attributes['_route']) || $attributes['_route'] != 'listings_map') {
            return null;
        }

        $location = $attributes['location'];
        $filtersString = $attributes['filters'];

        return $this->buildCriteriaByLocationAndFiltersString($location, $filtersString);
    }

    public function uriToSearchCriteriaWithoutLocation(array $filters): ?ListingSearchCriteria
    {
        return $this->buildCriteriaByLocationAndFiltersStringWithoutCriteria($filters);
    }

    public function uriToSearchCriteriaBySearch($filters): ?ListingSearchCriteria
    {
        $criteria = new ListingSearchCriteria();

        if (isset($filters['searchInput']) && $filters['searchInput']) {
            if (in_array(ucwords($filters['searchInput']), ListingConstants::idxCities)) {
                $criteria->city = ucwords($filters['searchInput']);
            } else {
                $criteria->search = $filters['searchInput'];
            }
        }

        if (isset($filters['searchType']) && $filters['searchType']) {
            $criteria->searchType = $filters['searchType'];
        }

        if (isset($filters['text']) && $filters['text']) {
            $criteria->search = $filters['text'];
        }

        if (isset($filters['minPrice']) && (int)$filters['minPrice']) {
            $criteria->minPrice = (int)$filters['minPrice'];
        }

        if (isset($filters['maxPrice']) && (int)$filters['maxPrice']) {
            $criteria->maxPrice = (int)$filters['maxPrice'];
        }

        if (isset($filters['minBeds']) && (int)$filters['minBeds']) {
            $criteria->minBeds = (int)$filters['minBeds'];
        }

        if (isset($filters['minBaths']) && (int)$filters['minBaths']) {
            $criteria->minBaths = (int)$filters['minBaths'];
        }

        if (isset($filters['propertyTypes']) && $filters['propertyTypes']) {
            $criteria->propertyTypes = $filters['propertyTypes'];
        }

        return $criteria;
    }

    public function buildCriteriaByLocationAndFiltersString(string $location, ?string $filtersString): ListingSearchCriteria
    {
        $criteria = new ListingSearchCriteria();

        $locationParts = explode(',', $location);
        if (count($locationParts) === 2) {
            $criteria->city = $locationParts[0];
            $criteria->stateOrProvince = ListingService::getProvinceName($locationParts[1]);
            $criteria->location = $locationParts[0] . ',' . $locationParts[1];
        } elseif (count($locationParts) === 4) {
            $criteria->box = implode(',', $locationParts);
            $criteria->location = $criteria->box;
        } else {
            throw new BadRequestException("unsupported 'location' format");
        }

        if (!empty($filtersString)) {
            $criteria = $this->applyFiltersToCriteria($criteria, $filtersString);
        }

        return $criteria;
    }

    public function buildCriteriaByLocationAndFiltersStringWithoutCriteria(?string $filtersString): ListingSearchCriteria
    {
        $criteria = new ListingSearchCriteria();

        if (!empty($filtersString)) {
            $criteria = $this->applyFiltersToCriteria($criteria, $filtersString);
        }

        return $criteria;
    }

    public function buildCriteriaByStreetAndFiltersString(string $street, ?string $filtersString): ListingSearchCriteria
    {
        $criteria = new ListingSearchCriteria();

        $criteria->streetName = $street;

        if (!empty($filtersString)) {
            $criteria = $this->applyFiltersToCriteria($criteria, $filtersString);
        }

        return $criteria;
    }

    private const FilterPrefixMap = [
        'price' => [
            'type' => 'range',
            'modifier' => 'price',
        ],
        'year-built' => [
            'type' => 'range',
            'modifier' => 'yearBuilt',
        ],
        'beds' => [
            'type' => 'range',
            'modifier' => 'beds',
        ],
        'baths' => [
            'type' => 'range',
            'modifier' => 'baths',
        ],
        'living-area' => [
            'type' => 'range',
            'modifier' => 'livingArea',
        ],
        'property-types' => [
            'type' => 'array',
            'modifier' => 'propertyTypes',
        ],
        'keywords' => [
            'type' => 'array',
            'modifier' => 'keywordsArray',
        ],
        'lot-size' => [
            'type' => 'array',
            'modifier' => 'lotSize',
        ],
    ];


    private function applyFiltersToCriteria(
        ListingSearchCriteria $criteria,
        string                $filtersString
    ): ListingSearchCriteria
    {
        $filtersString = str_replace('Apartment/Condo', 'Apartment-Condo', $filtersString);
        $filtersString = str_replace('House/Single Family', 'House-Single Family', $filtersString);

        $filtersArray = explode('/', $filtersString);

        foreach ($filtersArray as $part) {
            $part = str_replace('Apartment-Condo', 'Apartment/Condo', $part);
            $replacedPart = str_replace('House-Single Family', 'House/Single Family', $part);

            list ($prefix, $data) = explode('_', $replacedPart);


            if (!isset(self::FilterPrefixMap[$prefix])) {
                $this->logger->warning("could not find modifier for prefix $prefix");
                continue;
            }

            $config = self::FilterPrefixMap[$prefix];


            switch ($config['type']) {
                case 'range':
                    $modifier = $config['modifier'];
                    list ($min, $max) = explode('-', $data);

                    if ($min) {
                        $minParam = 'min' . ucfirst($modifier);
                        $criteria->$minParam = $min;
                    }

                    if ($max) {
                        $maxParam = 'max' . ucfirst($modifier);
                        $criteria->$maxParam = $max;
                    }
                    break;

                case 'array':
                    $modifier = $config['modifier'];
                    $criteria->$modifier = explode(',', $data);
                    break;
            }

        }

        return $criteria;
    }

    public function searchCriteriaToUri(ListingSearchCriteria $criteria, int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $urlFilterParts = [];

        if ($criteria->minPrice || $criteria->maxPrice) {
            $minPrice = $criteria->minPrice ? strval($criteria->minPrice) : '0';
            $maxPrice = $criteria->maxPrice ? strval($criteria->maxPrice) : '0';
            $urlFilterParts[] = "price_$minPrice-$maxPrice";
        }

        if ($criteria->minYearBuilt || $criteria->maxYearBuilt) {
            $minYearBuilt = $criteria->minYearBuilt ? strval($criteria->minYearBuilt) : '0';
            $maxYearBuilt = $criteria->maxYearBuilt ? strval($criteria->maxYearBuilt) : '0';
            $urlFilterParts[] = "year-built_$minYearBuilt-$maxYearBuilt";
        }

        if ($criteria->minBeds || $criteria->maxBeds) {
            $minBeds = $criteria->minBeds ? strval($criteria->minBeds) : '0';
            $maxBeds = $criteria->maxBeds ? strval($criteria->maxBeds) : '0';
            $urlFilterParts[] = "beds_$minBeds-$maxBeds";
        }

        if ($criteria->minBaths || $criteria->maxBaths) {
            $minBaths = $criteria->minBaths ? strval($criteria->minBaths) : '0';
            $maxBaths = $criteria->maxBaths ? strval($criteria->maxBaths) : '0';
            $urlFilterParts[] = "baths_$minBaths-$maxBaths";
        }

        if ($criteria->minLivingArea || $criteria->maxLivingArea) {
            $minLivingArea = $criteria->minLivingArea ? strval($criteria->minLivingArea) : '0';
            $maxLivingArea = $criteria->maxLivingArea ? strval($criteria->maxLivingArea) : '0';
            $urlFilterParts[] = "living-area_$minLivingArea-$maxLivingArea";
        }

        if ($criteria->minLotSize || $criteria->maxLotSize) {
            $minLotSize = $criteria->minLotSize ? strval($criteria->minLotSize) : '0';
            $maxLotSize = $criteria->maxLotSize ? strval($criteria->maxLotSize) : '0';
            $urlFilterParts[] = "lot-size_$minLotSize-$maxLotSize";
        }

        if (!empty($criteria->propertyTypes)) {
            $types = implode(',', $criteria->propertyTypes);
            $urlFilterParts[] = "property-types_$types";
        }

        if (!empty($criteria->keywordsArray)) {
            $types = implode(',', $criteria->keywordsArray);
            $urlFilterParts[] = "keywords_$types";
        }

        $filtersUrlPart = !empty($urlFilterParts) ? implode('/', $urlFilterParts) : null;

        $location = null;
        if (!empty($criteria->city) && !empty($criteria->stateOrProvince)) {
            $location = $criteria->city . "," . $this->listingService->getProvinceCode($criteria->stateOrProvince);
        } elseif (!empty($criteria->box)) {
            $location = $criteria->box;
        } else {
            throw new \InvalidArgumentException("not enough data to build location value");
        }

        return $this->urlGenerator->generate(
            'listings_map',
            [
                'location' => $location,
                'filters' => $filtersUrlPart,
            ],
            $referenceType
        );

    }

}