<?php


namespace App\Controller\Admin;


use App\Entity\CityCenter;
use App\Repository\CityCenterRepository;
use App\Service\CityCenter\CityCenterService;
use App\Service\Utils\FormUtils;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Throwable;

/**
 * @Route("/admin", priority=10)
 */
class CityCenterController extends AbstractController
{
    const LIMIT = 50;

    private CityCenterService $cityCenterService;

    public function __construct(CityCenterService $cityCenterService)
    {
        $this->cityCenterService = $cityCenterService;
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/city_center/{page}", name="admin_city_centers_index", requirements={"page"="\d+"})
     */
    public function index(int $page = 1)
    {
        $offset = ($page - 1) * self::LIMIT;

        $cityCenters = $this->cityCenterService->getCityCenters($page, self::LIMIT, $offset);

        return $this->render('admin/city_center/index.html.twig', [
            'cityCenters' => $cityCenters
        ]);
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/city_center/create", name="admin_city_center_create")
     */
    public function create()
    {
        return $this->render('admin/city_center/create.html.twig');
    }

    /**
     * @return Response
     * @Route("/city_center/store", name="admin_city_center_store", methods={"POST"})
     */
    public function store(Request $request)
    {
        try {
            if (!$request->isXmlHttpRequest()) {
                throw $this->createNotFoundException();
            }

            $data = FormUtils::getJson($request);

            $this->cityCenterService->create($data);

            return $this->json(['success' => true, 'message' => 'created', 'url' => $this->generateUrl('admin_city_centers_index')]);
        } catch (Exception | Throwable $exception) {
            return $this->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/city_center/edit/{id}", name="admin_city_center_edit", requirements={"page"="\d+"})
     */
    public function edit(CityCenter $cityCenter)
    {
        return $this->render('admin/city_center/edit.html.twig', ['cityCenter' => $cityCenter]);
    }

    /**
     * @return Response
     * @Route("/city_center/update", name="admin_city_center_update", methods={"POST"})
     */
    public function update(Request $request)
    {
        try {
            if (!$request->isXmlHttpRequest()) {
                throw $this->createNotFoundException();
            }

            $this->cityCenterService->update($request);

            return $this->json([
                'success' => true,
                'message' => 'created',
                'url' => $this->generateUrl('admin_city_centers_index')
             ]);
        } catch (Exception | Throwable $exception) {
            return $this->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }

    /**
     * @Route("/city_center/delete/{id}", name="admin_city_center_delete", methods={"GET"})
     */
    public function delete(CityCenter $cityCenter)
    {
        $this->cityCenterService->delete($cityCenter);
        return $this->redirectToRoute('admin_city_centers_index');
    }


}