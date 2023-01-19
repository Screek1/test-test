<?php


namespace App\Model\Listing;


class ListingStatus
{
    const New = 'new';
    const Updated = 'updated';
    const Live = 'live';

    const VisibleStatuses = [ListingStatus::Live, ListingStatus::Updated];
}