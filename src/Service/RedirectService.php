<?php
declare(strict_types=1);

namespace App\Service;

class RedirectService
{
    private array $redirectUrls = [
        'lower-mainland/vancouver,bc/' => '/for-sale/Vancouver,BC',
        'lower-mainland/langley,bc/' => '/for-sale/Langley,BC',
        'lower-mainland/surrey,bc/' => '/for-sale/Surrey,BC',
        'lower-mainland/richmond,bc/' => '/for-sale/Richmond,BC',
        'lower-mainland/abbotsford,bc' => '/for-sale/Abbotsford,BC',
        'lower-mainland/chilliwack,bc' => '/for-sale/Chilliwack,BC',
        'lower-mainland/maple-ridge,bc' => '/for-sale/Maple%20Ridge,BC',
        'lower-mainland/coquitlam,bc' => '/for-sale/Coquitlam,BC',
        'lower-mainland/west-vancouver,bc' => '/for-sale/West%20Vancouver,BC',
        'lower-mainland/north-vancouver,bc' => '/for-sale/North%20Vancouver,BC',
        'lower-mainland/mission,bc' => '/for-sale/Mission,BC',
        'lower-mainland/delta,bc' => '/for-sale/Delta,BC',
        'lower-mainland/new-westminster,bc' => '/for-sale/New%20Westminster,BC',
        'lower-mainland/white-rock,bc' => '/for-sale/White%20Rock,BC',
        'lower-mainland/port-coquitlam,bc' => '/for-sale/Port%20Coquitlam,BC',
        'lower-mainland/port-moody,bc' => '/for-sale/Port%20Moody,BC',
        'lower-mainland/pitt-meadows,bc' => '/for-sale/Pitt%20Meadows,BC',
        'vancouver-real-estate-mls-vancouver' => '/for-sale/Vancouver,BC',
        'surrey-real-estate-mls-listings-surrey' => '/for-sale/Surrey,BC',
        'burnaby-real-estate-mls-burnaby-listings' => '/for-sale/Burnaby,BC',
        'lower-mainland/richmond,bc/real-estate-for-sale' => '/for-sale/Richmond,BC',
        'lower-mainland/abbotsford,bc/real-estate-for-sale' => '/for-sale/Abbotsford,BC',
        'Vancouver-Condos-for-Sale-Vancouver-Apartments' => '/for-sale/Vancouver,BC/property-types_Apartment/Condo',
        'lower-mainland/surrey,bc/condos-for-sale' => '/for-sale/Surrey,BC/property-types_Apartment/Condo',
        'lower-mainland/burnaby,bc/condos-for-sale' => '/for-sale/Burnaby,BC/property-types_Apartment/Condo',
        'lower-mainland/richmond,bc/condos-for-sale' => '/for-sale/Richmond,BC/property-types_Apartment/Condo',
        'lower-mainland/coquitlam,bc/condos-for-sale' => '/for-sale/Coquitlam,BC/property-types_Apartment/Condo',
        'lower-mainland/coquitlam,bc/houses-for-sale' => '/for-sale/Coquitlam,BC/property-types_House/Single%20Family',
        'lower-mainland/vancouver,bc/houses-for-sale' => '/for-sale/Vancouver,BC/property-types_House/Single%20Family',
        'houses-for-sale-in-surrey-bc-surrey-homes' => '/for-sale/Surrey,BC/property-types_House/Single%20Family',
        'Burnaby-Houses-for-sale-burnaby-condos' => '/for-sale/Burnaby,BC/property-types_House/Single%20Family',
        'lower-mainland/richmond,bc/houses-for-sale' => '/for-sale/Richmond,BC/property-types_House/Single%20Family',
        'vancouver-island/' => '/for-sale/Victoria,BC',
        'vancouver-island/victoria,bc' => '/for-sale/Victoria,BC',
        'vancouver-island/victoria,bc/list_v' => '/for-sale/Victoria,BC',
        'vancouver-island/langford,bc/' => '/for-sale/Langford,BC',
        'vancouver-island/langford,bc/list_v' => '/for-sale/Langford,BC',
        'vancouver-island/duncan,bc' => '/for-sale/Duncan,BC',
        'vancouver-island/duncan,bc/list_v' => '/for-sale/Duncan,BC',
        'vancouver-island/nanaimo,bc' => '/for-sale/Nanaimo,BC',
        'vancouver-island/nanaimo,bc/list_v' => '/for-sale/Nanaimo,BC',
    ];

    public function searchRedirectUrl($slug)
    {
        if (isset($this->redirectUrls[$slug])) {
            return $this->redirectUrls[$slug];
        }
        return $this->redirectUrls[$slug] ?? null;
    }
}