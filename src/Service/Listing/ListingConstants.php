<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 28.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Listing;


interface ListingConstants
{
    const UPLOAD_LISTING_PIC_PATH = '/viksemenovPics/';
    const LIVING_AREA = [
        [-999999, -1], // Any
        [0, 500],
        [501, 750],
        [751, 1000],
        [1001, 1250],
        [1251, 1500],
        [1501, 1750],
        [1751, 2000],
        [2001, 2500],
        [2501, 3000],
        [3001, 3500],
        [3501, 4000],
        [4001, 5000],
        [5001, 7500],
        [7501, 10000],
        [10001, 999999],
    ];
    const LOT_SIZE = [
        [-999999, -1], // Any
        [0, 0],
        [1, 2000],
        [2001, 3000],
        [3001, 5000],
        [5001, 10890],
        [10891, 21780],
        [21781, 43560],
        [43561, 87120],
        [87121, 217800],
        [217801, 435600],
        [435601, 999999],
    ];
    const YEAR_BUILT = [
        [1800, 1969],
        [1970, 1979],
        [1980, 1989],
        [1990, 1999],
        [2000, 2009],
        [2010, 2020],
    ];
    const SEARCH_RADIUS = 3; //kilometers

    const FeedDdf = 'ddf';

    const FeedIdx = 'idx';

    const FeedIdxRD = 'RD_1';
    const FeedIdxRA = 'RA_2';
    const FeedIdxMF = 'MF_3';
    const FeedIdxLD = 'LD_4';
    const FeedIdxRT = 'RT_5';

    const LowerMainlandCities = [
        'Vancouver',
        'North Vancouver',
        'Burnaby',
        'Coquitlam',
        'Surrey',
        'Richmond',
        'West Vancouver',
        'Langley',
        'Maple Ridge',
        'Abbotsford',
        'Chilliwack',
        'Pitt Meadows',
        'Mission',
        'Coquitlam',
        'Port Coquitlam',
        'Port Moody',
        'White Rock',
        'Delta',
        'Langley',
        'Abbotsford',
        'New Westminster'
    ];

    const idxCities = [
        'Westminster',
        'Keats Island',
        'Sardis',
        'Pitt Meadows',
        'Chilliwack',
        'Soames Point',
        'Harrison Hot Springs',
        'Port Moody',
        'Galiano Island',
        'Mayne Island',
        'Bowen Island',
        'Garden Bay',
        'Surrey',
        'White Rock',
        'No City Value',
        'Salt Spring Island',
        'Burnaby',
        'Vancouver',
        'Whistler',
        'Garibaldi Highlands',
        'Devine',
        'Cultus Lake',
        'Yale',
        'Abbotsford',
        'Sardis - Greendale',
        'Gambier Island',
        'Port Coquitlam',
        'Yarrow',
        'Sechelt',
        'Cadreb Other',
        'Hope',
        'Saturna Island',
        'Ruby Lake',
        'Squamish',
        'Pender Harbour',
        'Agassiz',
        'Maple Ridge',
        'Granthams Landing',
        'Pender Island',
        'Richmond',
        'Sardis - Chwk River Valley',
        'Nelson Island',
        'Columbia Valley',
        'North Vancouver',
        'Britannia Beach',
        'D\'Arcy',
        'Ryder Lake',
        'Lions Bay',
        'Birken',
        'Coquitlam',
        'Gibsons',
        'Harrison Mills',
        'Egmont',
        'Delta',
        'Laidlaw',
        'Furry Creek',
        'Mission',
        'West Vancouver',
        'Anmore',
        'Lindell Beach',
        'Ladner',
        'Gabriola Island',
        'Boston Bar',
        'Langdale',
        'Sunshine Valley',
        'Madeira Park',
        'Langley',
        'Seymour',
        'Hopkins Landing',
        'Belcarra',
        'Rosedale',
        'Boston Bar - Lytton',
        'Roberts Creek',
        'Brackendale',
        'Halfmoon Bay',
        'Pemberton',
        'Tsawwassen',
    ];
}