<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 25.01.2021
 *
 * @package viksemenov20
 */

namespace App\Controller;

use App\Service\School\SchoolService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SchoolsMapController extends AbstractController
{
    private SchoolService $schoolService;

    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    /**
     * @Route("/school/search", priority=10, name="school_search", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function schoolSearch(Request $request): Response
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

        $schools = $this->schoolService->getAllSchoolsForMapBox(
            floatval($location[0]),
            floatval($location[1]),
            floatval($location[2]),
            floatval($location[3])
        );

        $response = new JsonResponse(['collection' => json_encode($schools)]);
        $responseData = [];
        foreach ($schools as $school) {
            $responseData[] = $school['_source'];
        }
        $response->setData($responseData);

        return $response;
    }

}