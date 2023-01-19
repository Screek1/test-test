<?php

namespace App\Controller;

use App\Service\Listing\ListingSearchService;
use App\Service\Listing\ListingService;
use App\Service\Page\PageService;
use App\Service\RedirectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractController
{
    private PageService $pageService;
    private ListingService $listingService;
    private ListingSearchService $listingSearchService;
    private RedirectService $redirectService;

    const DefaultPageSize = 49;

    public function __construct(PageService $pageService, ListingService $listingService, ListingSearchService $listingSearchService, RedirectService $redirectService)
    {
        $this->pageService = $pageService;
        $this->listingService = $listingService;
        $this->listingSearchService = $listingSearchService;
        $this->redirectService = $redirectService;
    }

    /**
     * @param $slug
     * @return Response
     */
    public function page($slug): Response
    {
        if ($redirectPage = $this->redirectService->searchRedirectUrl($slug)) {
            return $this->redirect($redirectPage);
        }

        $page = $this->pageService->search(['slug' => $slug, 'status' => true]);
        if ($page) {
            if ($page->getType() == 'static') {
                return $this->render('page/' . $page->getType() . '-page.html.twig', [
                    'page' => $page,
                ]);
            } elseif ($page->getType() == 'landing') {
                return $this->render('page/' . $page->getType() . '-page.html.twig', [
                    'page' => $page,
                    'searchListings' => $this->listingSearchService->getListingsForSearchPage($page)
                ]);
            } else {
                $criteria = $this->pageService->criteriaToListingSearchFormat($page);
                return $this->render('listings_map/index.html.twig', [
                    'title' => $page->getTitle(),
                    'searchListings' => $this->listingSearchService->getListingsForSearchPage($page),
                    'searchResult' => $this->listingSearchService->searchListings($criteria, self::DefaultPageSize),
                    'searchFormObject' => $this->listingService->getSearchFormObject(),
                    'criteria' => $criteria
                ]);
            }
        } else {
            return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
        }
    }

}
