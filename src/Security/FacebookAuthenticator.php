<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FacebookAuthenticator extends SocialAuthenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;
    private RouterInterface $router;
    private UserRepository $userRepository;
    private string $appUrl;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface $router,
        UserRepository $userRepository,
        string $appUrl
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->appUrl = $appUrl;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'oauth_check' && $request->get('service') === 'facebook';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getFacebookClient(), $this->getOptions('facebook'));
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $facebookUser = $this->getFacebookClient()->fetchUserFromToken($credentials);

        $email = $facebookUser->getEmail();

        $existingUser = $this->userRepository->findOneBy(['facebookId' => $facebookUser->getId()]);

        if ($existingUser) {
            return $existingUser;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $user = User::fromFacebookRequest(
                $facebookUser->getId(),
                $email,
                $facebookUser->getName()
            );
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse($this->router->generate('home'));
    }

    private function getFacebookClient()
    {
        return $this->clientRegistry->getClient('facebook');
    }

    private function getOptions(string $service): array
    {
        $context = $this->router->getContext();
        $context->setBaseUrl($this->appUrl);
        return [
            'redirect_uri' => $this->router->generate('oauth_check', ['service' => $service])
        ];
    }
}
