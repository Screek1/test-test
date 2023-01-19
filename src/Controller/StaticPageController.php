<?php

namespace App\Controller;

use App\Service\Listing\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StaticPageController extends AbstractController
{
    private ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * @Route ("/how-it-works", name="how-it-works", priority=10)
     */
    public function howItWorks()
    {
        return $this->render('how-it-works/index.html.twig');
    }

    /**
     * @Route ("/selling", name="selling", priority=10)
     */
    public function selling()
    {
        return $this->render('selling/index.html.twig');
    }

    /**
     * @Route ("/buying", name="buying", priority=10)
     */
    public function buying()
    {
        return $this->render('buying/index.html.twig');
    }

    /**
     * @Route ("/sitemap", name="sitemap", priority=10)
     */
    public function sitemap()
    {
        return $this->render('sitemap/index.html.twig');
    }

    /**
     * @deprecated
     */
    public function browseByStreet()
    {
        $streets = $this->listingService->getStreetsWithCount('British Columbia');
        return $this->render('browse_by_street/index.html.twig', [
            'streets' => $streets
        ]);
    }

}