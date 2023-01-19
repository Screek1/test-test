<?php

namespace App\Controller;

use App\Service\BusStop\BusStopService;
use App\Service\Demography\CrimeService;
use App\Service\Demography\DemographyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DemographyController extends AbstractController
{
    private CrimeService $crimeService;
    private DemographyService $demographyService;
    private BusStopService $busStopService;

    public function __construct(CrimeService $crimeService, DemographyService $demographyService, BusStopService $busStopService)
    {
        $this->busStopService = $busStopService;
        $this->crimeService = $crimeService;
        $this->demographyService = $demographyService;
    }

    /**
     * @Route("/search/crime", name="crime_search", methods={"POST"})
     */
    public function crime(Request $request): Response
    {
        ini_set('memory_limit', '-1');
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $jsonBody = json_decode($request->getContent(), true);

        $locationString = $jsonBody['location'];
        $location = explode(',', $locationString);

        if (count($location) !== 4) {
            return $this->json([], Response::HTTP_BAD_REQUEST);
        }

        $crime = $this->crimeService->getAllCrimeInMapBox(
            floatval($location[0]),
            floatval($location[1]),
            floatval($location[2]),
            floatval($location[3])
        );

        $response = new JsonResponse(['collection' => json_encode($crime)]);
        $responseData = [];
        foreach ($crime as $item) {
            $responseData[] = $item['_source'];
        }

        $response->setData($responseData);

        return $response;
    }

    /**
     * @Route("/search/demography", name="demography_search", methods={"POST"})
     */
    public function demography(Request $request): Response
    {
        ini_set('memory_limit', '-1');
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $jsonBody = json_decode($request->getContent(), true);

        $locationString = $jsonBody['location'];
        $location = explode(',', $locationString);

        if (count($location) !== 4) {
            return $this->json([], Response::HTTP_BAD_REQUEST);
        }

        $demography = $this->demographyService->getAllDemographyInMapBox(
            floatval($location[0]),
            floatval($location[1]),
            floatval($location[2]),
            floatval($location[3])
        );

        $response = new JsonResponse(['collection' => json_encode($demography)]);
        $responseData = [];
        foreach ($demography as $item) {
            $responseData[] = $item['_source'];
        }

        $response->setData($responseData);

        return $response;
    }

    /**
     * @Route("/search/bus-stops", name="bus-stops_search", methods={"POST"})
     */
    public function busStops(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $jsonBody = json_decode($request->getContent(), true);

        $locationString = $jsonBody['location'];
        $location = explode(',', $locationString);

        if (count($location) !== 4) {
            return $this->json([], Response::HTTP_BAD_REQUEST);
        }

        $demography = $this->busStopService->getAllDemographyInMapBox(
            floatval($location[0]),
            floatval($location[1]),
            floatval($location[2]),
            floatval($location[3])
        );

        $response = new JsonResponse(['collection' => json_encode($demography)]);
        $responseData = [];
        foreach ($demography as $item) {
            $responseData[] = $item['_source'];
        }

        $response->setData($responseData);

        return $response;
    }
}
