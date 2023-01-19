<?php

namespace App\Controller;

use App\Criteria\ListingSearchCriteria;
use App\Entity\SavedSearch;
use App\Entity\User;
use App\Form\UserProfileType;
use App\Model\UserProfileModel;
use App\Repository\ListingRepository;
use App\Repository\SavedSearch\SavedSearchRepository;
use App\Repository\ViewingRepository;
use App\Service\Listing\ListingSearchDataService;
use App\Service\Listing\ListingService;
use App\Service\SavedSearch\SavedSearchService;
use App\Service\User\UserService;
use App\Service\Utils\FormUtils;
use App\Service\Utils\SearchUtils;
use App\Service\Utils\UrlUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class UserProfileController extends AbstractController
{

    private Security $security;
    private ListingSearchDataService $listingSearchDataService;
    private FormFactoryInterface $formFactory;
    private UserService $userService;
    private SerializerInterface $serializer;
    private ListingRepository $listingRepository;
    private ViewingRepository $viewingRepository;
    private SavedSearchRepository $savedSearchRepository;
    private EntityManagerInterface $entityManager;
    private UrlUtils $urlUtils;

    public function __construct(
        Security                 $security,
        ListingSearchDataService $listingSearchDataService,
        FormFactoryInterface     $formFactory,
        UserService              $userService,
        SerializerInterface      $serializer,
        ListingRepository        $listingRepository,
        ViewingRepository        $viewingRepository,
        SavedSearchRepository    $savedSearchRepository,
        EntityManagerInterface   $entityManager,
        UrlUtils                 $urlUtils
    )
    {
        $this->security = $security;
        $this->listingSearchDataService = $listingSearchDataService;
        $this->formFactory = $formFactory;
        $this->userService = $userService;
        $this->serializer = $serializer;
        $this->listingRepository = $listingRepository;
        $this->viewingRepository = $viewingRepository;
        $this->savedSearchRepository = $savedSearchRepository;
        $this->entityManager = $entityManager;
        $this->urlUtils = $urlUtils;
    }

    /**
     * @Route("/my-account/edit-profile", name="edit_profile", priority="10", methods={"GET"})
     */
    public function editProfile(): Response
    {
        $user = $this->security->getUser();

        $provinces = ListingService::$provinces;
        natsort($provinces);

        return $this->render(
            'user_profile/editProfile.html.twig',
            [
                'user' => $user,
                'provinceOptions' => array_map(
                    function ($code, $item) {
                        return ['value' => $code, 'text' => $item];
                    },
                    array_keys($provinces),
                    $provinces
                ),
            ]
        );
    }

    /**
     * @Route("/my-account/edit-profile", name="save_profile", priority="10", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws \HttpException
     */
    public function saveProfile(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $userFormData = new UserProfileModel();;
        $form = $this->formFactory->createNamed('', UserProfileType::class, $userFormData);

        $form->submit(FormUtils::getJson($request));

        if (!$form->isSubmitted()) {
            return $this->json(['message' => "Could not process the request"], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return $this->json(['errors' => FormUtils::getErrorMessages($form)], Response::HTTP_BAD_REQUEST);
        }

        $updatedUser = $this->userService->updateUserProfile($user, $userFormData);

        return $this->json($this->serializer->normalize($updatedUser));
    }

    /**
     * @Route("/my-account/favourite-listings", name="favourite_listings", priority="10")
     */
    public function favouriteListings(): Response
    {
        $user = $this->security->getUser();

        $favouritesListings = [];
        if ($user instanceof User) {
            foreach ($this->listingRepository->getUserFavoriteListings($user->getId()) as $listing) {
                $favouritesListings[] = $this->listingSearchDataService->constructSearchListingData($listing);
            }
        }

        return $this->render(
            'user_profile/favouriteListings.html.twig',
            [
                'favouritesListings' => $favouritesListings,
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/my-account/saved-searches", name="saved_searches", priority="10", methods={"GET"})
     */
    public function savedSearches(): Response
    {
        $user = $this->security->getUser();
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

        return $this->render(
            'user_profile/savedSearches.html.twig',
            [
                'savedSearches' => $searchesArray,
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/my-account/scheduled-viewings", name="scheduled_viewings", priority="10")
     */
    public function scheduledViewings(): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $viewings = [];

        foreach ($this->viewingRepository->getAllByUser($user) as $viewing) {
            $array = $viewing->toArray();
            $array['listing'] = $this->listingSearchDataService->constructSearchListingData($viewing->getListing());

            $viewings[] = $array;
        }


        return $this->render(
            'user_profile/viewings.html.twig',
            [
                'viewings' => $viewings,
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/my-account/social-connections", name="social_connections", priority="10")
     */
    public function socialConnections(): Response
    {
        $user = $this->security->getUser();

        return $this->render(
            'user_profile/socialConnections.html.twig',
            [
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/my-account/saved-searches/{id}", name="delete_saved_search", priority="10", methods={"DELETE"}, requirements={"id"="\d+"})
     * @param int $id
     * @return Response
     */
    public function deleteSavedSearch(int $id): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $savedSearch = $this->savedSearchRepository->getByIdAndUser($id, $user);
        if (!$savedSearch instanceof SavedSearch) {
            throw $this->createNotFoundException();
        }

        $this->entityManager->remove($savedSearch);
        $this->entityManager->flush();

        return $this->json(SavedSearchService::toArray($savedSearch), Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/my-account/saved-searches/{id}/frequency/{frequency}", name="set_saved_search_frequency", priority="10", methods={"POST"}, requirements={"id"="\d+", "frequency"="\w+"})
     * @param int $id
     * @param string $frequency
     * @return Response
     */
    public function setSavedSearchFrequency(int $id, string $frequency): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $savedSearch = $this->savedSearchRepository->getByIdAndUser($id, $user);
        if (!$savedSearch instanceof SavedSearch) {
            throw $this->createNotFoundException();
        }

        $savedSearch->setFrequency($frequency);
        $this->entityManager->flush();

        return $this->json(SavedSearchService::toArray($savedSearch), Response::HTTP_OK);
    }
}
