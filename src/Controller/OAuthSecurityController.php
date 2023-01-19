<?php


namespace App\Controller;


use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OAuthSecurityController extends AbstractController
{
    private UrlGeneratorInterface $router;
    private string $appUrl;

    public function __construct(UrlGeneratorInterface $router, string $appUrl)
    {
        $this->router = $router;
        $this->appUrl = $appUrl;
    }

    /**
     * @Route("/connect/google", name="connect_google_start")
     */
    public function connectGoogle(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect(['email'], $this->getOptions('google'));
    }

    /**
     * @Route("/connect/facebook", name="connect_facebook_start")
     */
    public function connectFacebook(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('facebook')
            ->redirect([
                'public_profile', 'email'
            ], $this->getOptions('facebook'));
    }

    /**
     * @Route("/connect/check/{service}", name="oauth_check")
     */
    public function checkService($service)
    {
        return ['message' => 'ok'];
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