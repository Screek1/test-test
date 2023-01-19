<?php


namespace App\Model\Listing;


class CitiesStats
{
    /**
     * @var CityCounters[]
     */
    public array $lowerMainlandCities = [];

    /**
     * @var CityCounters[]
     */
    public array $otherCities = [];
}