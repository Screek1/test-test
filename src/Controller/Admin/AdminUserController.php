<?php


namespace App\Controller\Admin;


use App\Criteria\ListingSearchCriteria;
use App\Entity\SavedSearch;
use App\Entity\User;
use App\Repository\ListingRepository;
use App\Repository\UserRepository;
use App\Repository\ViewingRepository;
use App\Service\Listing\ListingSearchDataService;
use App\Service\SavedSearch\SavedSearchService;
use App\Service\User\UserService;
use App\Service\Utils\SearchUtils;
use App\Service\Utils\UrlUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    private UserRepository $userRepository;
    private UrlUtils $urlUtils;
    private ViewingRepository $viewingRepository;
    private ListingRepository $listingRepository;
    private ListingSearchDataService $listingSearchDataService;
    private UserService $userService;

    const PageSize = 50;

    public function __construct(
        UserRepository $userRepository,
        UrlUtils $urlUtils,
        ViewingRepository $viewingRepository,
        ListingRepository $listingRepository,
        ListingSearchDataService $listingSearchDataService,
        UserService $userService
    ) {
        $this->userRepository = $userRepository;
        $this->urlUtils = $urlUtils;
        $this->viewingRepository = $viewingRepository;
        $this->listingRepository = $listingRepository;
        $this->listingSearchDataService = $listingSearchDataService;
        $this->userService = $userService;
    }

    /**
     * @Route("/admin/users/{page}", name="admin_user_list", priority="10", requirements={"page"="\d+"})
     * @param int $page
     * @return Response
     */
    public function userList(Request $request, int $page = 1): Response
    {
        $searchData = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
        ];
        $offset = ($page - 1) * self::PageSize;

        $usersResult = $this->userService->getUserList($searchData, $page, self::PageSize, $offset);

        return $this->render(
            'admin/user/list.html.twig',
            [
                'userResult' => $usersResult,
            ]
        );
    }

    /**
     * @Route("/admin/user/{id}", name="admin_user_details", priority="10", requirements={"id"="\d+"})
     * @param int $page
     * @return Response
     */
    public function userDetails(int $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $savedSearches = $user->getSavedSearches();

        $searchesArray = array_map(
            function (SavedSearch $savedSearch) {
                $array = SavedSearchService::toArray($savedSearch);
                $criteria = new ListingSearchCriteria();
                $criteria = SearchUtils::buildSearchCriteriaData($criteria, $savedSearch->getCriteria());
                $array['search_url'] = $this->urlUtils->searchCriteriaToUri($criteria);

                return $array;
            },
            $savedSearches->toArray()
        );

        $viewings = [];

        foreach ($this->viewingRepository->getAllByUser($user) as $viewing) {
            $array = $viewing->toArray();
            $array['listing'] = $this->listingSearchDataService->constructSearchListingData($viewing->getListing());

            $viewings[] = $array;
        }

        $favouritesListings = [];
        foreach ($this->listingRepository->getUserFavoriteListings($user->getId()) as $listing) {
            $favouritesListings[] = $this->listingSearchDataService->constructSearchListingData($listing);
        }

        return $this->render(
            'admin/user/details.html.twig',
            [
                'user' => $user,
                'searches' => $searchesArray,
                'viewings' => $viewings,
                'favouritesListings' => $favouritesListings,
            ]
        );
    }
}