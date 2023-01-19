<?php


namespace App\Service\Utils;


use App\Criteria\ListingSearchCriteria;
use App\Service\Listing\ListingConstants;
use App\Service\Listing\ListingService;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SearchUtils
{
    public static function buildSearchCriteriaData(ListingSearchCriteria $criteria, array $data): ListingSearchCriteria
    {
        if (!empty($data['location'])) {
            $locationParts = explode(',', $data['location']);
            if (count($locationParts) === 2) {
                $criteria->city = $locationParts[0];
                $criteria->stateOrProvince = ListingService::getProvinceName($locationParts[1]);
            } elseif (count($locationParts) === 4) {
                $criteria->box = implode(',', $locationParts);
            } else {
                throw new BadRequestException("unsupported 'location' format");
            }
        }

        if (!empty($data['minPrice'])) {
            $criteria->minPrice = intval($data['minPrice']);
        }
        if (!empty($data['maxPrice'])) {
            $criteria->maxPrice = intval($data['maxPrice']);
        }

        if (!empty($data['searchInput'])) {
            if (in_array(ucwords($data['searchInput']), ListingConstants::idxCities)) {
                $criteria->city = ucwords($data['searchInput']);
            } else {
                $criteria->search = $data['searchInput'];
            }
        }

        if (isset($data['searchType']) && $data['searchType']) {
            $criteria->searchType = $data['searchType'];
        }

        if (!empty($data['minBeds'])) {
            $criteria->minBeds = intval($data['minBeds']);
        }
        if (!empty($data['maxBeds'])) {
            $criteria->maxBeds = intval($data['maxBeds']);
        }

        if (!empty($data['minBaths'])) {
            $criteria->minBaths = intval($data['minBaths']);
        }
        if (!empty($data['maxBaths'])) {
            $criteria->maxBaths = intval($data['maxBaths']);
        }

        if (!empty($data['minYearBuilt'])) {
            $criteria->minYearBuilt = intval($data['minYearBuilt']);
        }
        if (!empty($data['maxYearBuilt'])) {
            $criteria->maxYearBuilt = intval($data['maxYearBuilt']);
        }

        if (!empty($data['minLivingArea'])) {
            $criteria->minLivingArea = intval($data['minLivingArea']);
        }
        if (!empty($data['maxLivingArea'])) {
            $criteria->maxLivingArea = intval($data['maxLivingArea']);
        }

        if (!empty($data['minLotSize'])) {
            $criteria->minLotSize = intval($data['minLotSize']);
        }

        if (!empty($data['streetName'])) {
            $criteria->streetName = $data['streetName'];
        }

        if (!empty($data['propertyTypes'])) {
            if (is_array($data['propertyTypes'])) {
                if (in_array('New Construction', $data['propertyTypes']) && empty($data['minYearBuilt']) && empty($data['maxYearBuilt'])) {
                    $criteria->newConstructionsMinYearBuild = Carbon::now()->year;
                    $criteria->newConstructionsMaxYearBuild = Carbon::now()->addYears(10)->year;
                    $key = array_search('New Construction', $data['propertyTypes']);
                    unset($data['propertyTypes'][$key]);
                }
                if (in_array('Foreclosures', $data['propertyTypes'])) {
                    $criteria->foreclosuresSearch = true;
                    $key = array_search('Foreclosures', $data['propertyTypes']);
                    unset($data['propertyTypes'][$key]);
                }
                if (in_array('New Listings (Past Week)', $data['propertyTypes'])) {
                    $criteria->showPastWeek = true;
                    $key = array_search('New Listings (Past Week)', $data['propertyTypes']);
                    unset($data['propertyTypes'][$key]);
                }
                $criteria->propertyTypes = $data['propertyTypes'];
            } elseif (is_string($data['propertyTypes'])) {
                switch ($data['propertyTypes']) {
                    case 'New Construction':
                        if (empty($data['minYearBuilt']) && empty($data['maxYearBuilt'])) {
                            $criteria->newConstructionsMinYearBuild = Carbon::now()->year;
                            $criteria->newConstructionsMaxYearBuild = Carbon::now()->addYears(10)->year;
                        }
                        break;
                    case 'Foreclosures':
                        $criteria->foreclosuresSearch = true;
                        break;
                    case 'New Listings (Past Week)':
                        $criteria->showPastWeek = true;
                        break;
                    default:
                        $criteria->propertyTypes = [$data['propertyTypes']];
                }
            }
        }
        if (!empty($data['keywordsArray'])) {
            if (is_array($data['keywordsArray'])) {
                $criteria->keywordsArray = $data['keywordsArray'];
            } elseif (is_string($data['keywordsArray'])) {
                $criteria->keywordsArray = json_decode($data['keywordsArray'], true);
            }
        }

        if (!empty($data['sort'])) {
            $criteria->sort = $data['sort'];
        }

        return $criteria;
    }
}