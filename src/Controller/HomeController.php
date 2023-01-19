<?php

namespace App\Controller;

use App\Service\Listing\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * @Route("/", name="home", priority=10)
     */
    public function index()
    {
        $selfListings = $this->listingService->getSelfListingsForHomepage();
        $citiesStats = $this->listingService->getCitiesStats('British Columbia');
        $featuredProperties = $this->listingService->getFeaturedProperties();
        $searchFormObject = $this->listingService->getSearchFormObject();

        return $this->render(
            'default/index.html.twig',
            [
                'selfListings' => $selfListings,
                'featuredProperties' => $featuredProperties,
                'citiesStats' => $citiesStats,
                'searchFormObject' => $searchFormObject,
            ]
        );
    }

    /**
     * @Route("/privacy-policy", name="privacy_policy", priority=10)
     */
    public function policy(): Response
    {
        return $this->render(
            'default/privacy-policy.html.twig'
        );
    }

    /**
     * @Route("/terms-of-use", name="terms_of_use", priority=10)
     */
    public function termsOfUse(): Response
    {
        return $this->render(
            'default/terms-of-use.html.twig'
        );
    }

    /**
     * @Route("/disclaimers", name="disclaimers", priority=10)
     */
    public function disclaimers(): Response
    {
        return $this->render(
            'default/disclaimers.html.twig'
        );
    }

}
