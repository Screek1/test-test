<?php

namespace App\Controller\Admin;

use App\Entity\School;
use App\Repository\SchoolRepository;
use App\Service\School\SchoolService;
use App\Service\Search\SchoolSearchService;
use App\Service\Utils\FormUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", priority=10)
 */
class SchoolController extends AbstractController
{
    private SchoolSearchService $schoolSearchService;
    private SchoolService $schoolService;
    const LIMIT = 50;

    public function __construct(
        SchoolSearchService $schoolSearchService,
        SchoolService $schoolService
    ) {
        $this->schoolSearchService = $schoolSearchService;
        $this->schoolService = $schoolService;
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/schools/{page}", name="admin_schools_index", requirements={"page"="\d+"})
     */
    public function index($page = 1): Response
    {
        $schoolList = $this->schoolSearchService->getAllSchoolsWithPagination($page, self::LIMIT);

        return $this->render('admin/schools/index.html.twig', [
            'controller_name' => 'SchoolController',
            'schoolList' => $schoolList
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @Route("/school/edit/{id}", name="admin_school_edit", requirements={"id"="\d+"})
     */
    public function edit($id)
    {
        $school = $this->schoolSearchService->getSchoolById($id);
        return $this->render('admin/schools/edit.html.twig', [
            'school' => $school
        ]);
    }

    /**
     * @param Request $request
     * @Route("/school/update/{id}", name="admin_school_update", requirements={"id"="\d+"})
     */
    public function update(Request $request, School $school)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $data = FormUtils::getJson($request);
        $this->schoolService->update($school, $data);

        return $this->json(['message' => 'Success']);
    }
}
