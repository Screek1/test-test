<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 29.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Listing;


use App\Entity\Listing;

class ListingMediaService
{
    private string $ESBLEndpointEdge;

    public function __construct(string $ESBLEndpointEdge)
    {
        $this->ESBLEndpointEdge = $ESBLEndpointEdge;
    }

    public function getListingPhotos(Listing $listing): ?array
    {
        $imageNames = $listing->getImagesData();
        $listingImagesUrlArray = [];
        if (!is_null($imageNames)) {
            $i = 1;
            foreach ($imageNames as $imageName) {
                $listingImagesUrlArray[] =
                    $this->ESBLEndpointEdge.
                    '/listings/'.
                    $listing->getFeedID().'/'.
                    $listing->getFeedListingID().'/'.$imageName;
            }
        } else {
            $listingImagesUrlArray[] = $this->getListingNoImage();
        }

        return $listingImagesUrlArray;
    }

    public function getListingNoImage()
    {
        return $this->ESBLEndpointEdge . '/listings/'.'no-img.jpg';
    }

}