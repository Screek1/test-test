<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Model\UserModel;
use App\Repository\UserRepository;
use App\Service\SavedSearch\SavedSearchService;
use App\Service\User\UserService;
use App\Service\Utils\FormUtils;
use HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route(priority=10)
 */
class UserController extends AbstractController
{
    private UserService $userService;
    private FormFactoryInterface $formFactory;
    private SerializerInterface $serializer;
    private Security $security;
    private SavedSearchService $savedSearchService;

    public function __construct(
        UserService          $userService,
        FormFactoryInterface $formFactory,
        SerializerInterface  $serializer,
        Security             $security,
        SavedSearchService   $savedSearchService
    )
    {
        $this->userService = $userService;
        $this->formFactory = $formFactory;
        $this->serializer = $serializer;
        $this->security = $security;
        $this->savedSearchService = $savedSearchService;
    }

    /**
     * @Route("/user", name="user")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render(
            'admin/user_list/front_users/index.html.twig',
            [
                'users' => $userRepository->findAll(),
            ]
        );
    }

    /**
     * @Route(path="/register", name="register", priority="10", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws ExceptionInterface
     * @throws HttpException
     */
    public function register(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $userFormData = new UserModel();
        $form = $this->formFactory->createNamed('', UserType::class, $userFormData);

        $form->submit(FormUtils::getJson($request));

        if (!$form->isSubmitted()) {
            return $this->json(['message' => "Could not process the request"], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return $this->json(['errors' => FormUtils::getErrorMessages($form)], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userService->createUser(
            $userFormData->getEmail(),
            $userFormData->getPhone(),
            $userFormData->getPhone(),
            $userFormData->getName()
        );


        return $this->json($this->serializer->normalize($user), Response::HTTP_CREATED);
    }

    /**
     * @Route("/user/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user');
        }

        return $this->render(
            'admin/user_list/front_users/new.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/user/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render(
            'admin/user_list/front_users/show.html.twig',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userObject = $this->getDoctrine()->getManager();
            $userObject->flush();

            return $this->redirectToRoute('user');
        }

        return $this->render(
            'admin/user_list/front_users/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/user/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user');
    }

    /**
     * @Route("/user/addToFavorites/{listingId}", priority=10, name="add_to_favorites", methods={"POST"})
     * @param Request $request
     * @param string $listingId
     * @return Response
     */
    public function favorite(Request $request, string $listingId): Response
    {
        if (!$request->isXmlHttpRequest() || !$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createNotFoundException();
        }
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $response = $this->userService->toggleFavoriteListing($listingId, $user->getId());

        return $this->json($response);
    }

    /**
     * @Route("/user/save-search", priority=10, name="save_search", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws ExceptionInterface
     */
    public function saveSearch(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $criteria = json_decode($request->getContent(), true);

        $savedSearch = $this->savedSearchService->create($criteria);

        return $this->json(SavedSearchService::toArray($savedSearch));
    }
}