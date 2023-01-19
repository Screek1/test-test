<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 21.12.2020
 *
 * @package viksemenov20
 */

namespace App\Model\Listing;

class CityCounters
{
    public string $city;
    public int $count;
    public string $stateOrProvince;
    public  string $href;

    public function __construct(string $city, int $count, string $stateOrProvince, string $href = '#')
    {
        $this->city = $city;
        $this->count = $count;
        $this->stateOrProvince = $stateOrProvince;
        $this->href = $href;
    }
}