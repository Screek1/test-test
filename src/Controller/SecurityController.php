<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Utils\FormUtils;
use HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private SerializerInterface $serializer;
    private Security $security;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->security = $security;
    }

    /**
     * @Route("/login", name="app_login", methods={"GET"}, priority="10")
     */
    public function login()
    {
        if (!$this->security->isGranted("IS_ANONYMOUS")) {
            return $this->redirect("/");
        }

        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/login_check", name="login_check", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws HttpException
     * @throws ExceptionInterface
     */
    public function checkLogin(Request $request): JsonResponse
    {
        $requestData = FormUtils::getJson($request);
        $user = $this->userRepository->getByEmail($requestData['email']);

        return $this->json($this->serializer->normalize($user));
    }
}
