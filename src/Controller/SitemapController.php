<?php

namespace App\Controller;

use App\Service\Provider\AwsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    private string $ESBLEndpointEdge;
    private string $bucket;
    private AwsProvider $awsProvider;
    public function __construct(string $ESBLEndpointEdge, string $bucket, AwsProvider $awsProvider)
    {
        $this->ESBLEndpointEdge = $ESBLEndpointEdge;
        $this->awsProvider = $awsProvider;
        $this->bucket = $bucket;
    }

    /**
     * @Route("/{filename}.xml", name="index_sitemap", priority=10)
     * @param $filename
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sitemap($filename)
    {
        try {
            $result = $this->awsProvider->getClient()->getObject(array(
                'Bucket' => $this->bucket,
                'Key' => 'sitemap/' . $filename . '.xml',
            ));
        } catch (\Exception $exception) {
            return $this->json(['message' => 'Not found']);
        }

        $client = HttpClient::create();
        $responseByCloud = $client->request('GET', $result['@metadata']['effectiveUri']);
        $response = new Response($responseByCloud->getContent());
        $response->headers->set('Content-Type', 'xml');
        return $response;
//        return $this->redirect($url, 301);
    }

    /**
     * @Route("/sitemap/image/{filename}.xml", name="image_sitemap")
     * @param $filename
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function imageSitemap($filename)
    {
        try {
            $result = $this->awsProvider->getClient()->getObject(array(
                'Bucket' => $this->bucket,
                'Key' => 'sitemap-image/image_' . $filename . '.xml',
            ));
        } catch (\Exception $exception) {
            return $this->json(['message' => 'Not found']);
        }

        $client = HttpClient::create();
        $responseByCloud = $client->request('GET', $result['@metadata']['effectiveUri']);
        $response = new Response($responseByCloud->getContent());
        $response->headers->set('Content-Type', 'xml');
        return $response;
//        return $this->redirect($url, 301);
    }
}
