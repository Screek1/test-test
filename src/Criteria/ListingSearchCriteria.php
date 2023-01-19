<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 29.01.2021
 *
 * @package viksemenov20
 */

namespace App\Criteria;

use DateTimeInterface;

class ListingSearchCriteria
{
    public ?string $feedId = null;
    public ?string $feedType = null;

    public ?string $city;
    public ?string $stateOrProvince;

    public ?string $box = null;

    public ?int $minPrice = null;
    public ?int $maxPrice = null;

    public ?int $minYearBuilt = null;
    public ?int $maxYearBuilt = null;

    public ?int $newConstructionsMinYearBuild = null;
    public ?int $newConstructionsMaxYearBuild = null;

    public ?int $minBeds = null;
    public ?int $maxBeds = null;

    public ?int $minBaths = null;
    public ?int $maxBaths = null;


    public ?int $minLivingArea = null;
    public ?int $maxLivingArea = null;

    public ?int $minLotSize = null;
    public ?int $maxLotSize = null;

    public ?array $propertyTypes = null;

    public ?string $location = null;

    public ?string $search = null;
    public ?string $searchType = null;

    public ?string $streetName = null;

    public ?array $keywordsArray = null;

    public ?bool $foreclosuresSearch = false;
    public ?bool $showPastWeek = false;

    public ?string $sort = null;

    public ?DateTimeInterface $lastUpdated = null;

    public function __construct(
        ?string $city = null,
        ?string $stateOrProvince = null
    )
    {
        $this->city = $city;
        $this->stateOrProvince = $stateOrProvince;
    }

}