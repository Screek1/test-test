<?php


namespace App\Controller\Yelp;


use App\Yelp\Service\YelpService;
use Psr\Log\LoggerInterface;
use Stevenmaguire\Yelp\Exception\InvalidVersionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class YelpController extends AbstractController
{
    private YelpService $yelpService;
    private LoggerInterface $logger;

    public function __construct(YelpService $yelpService, LoggerInterface $logger)
    {
        $this->yelpService = $yelpService;
        $this->logger = $logger;
    }

    /**
     * @Route("/api/business/search", priority=10, name="business_search", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $params = json_decode($request->getContent(), true);

        try {
            $results = $this->yelpService->search($params);

            return $this->json($results);
        } catch (InvalidVersionException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());

            return $this->json(["message" => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }


    }
}