<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CityPhone;
use App\Service\CityPhone\CityPhoneService;
use App\Service\Utils\FormUtils;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("/admin", priority=10)
 */
class CityPhoneController extends AbstractController
{
    const LIMIT = 50;

    private CityPhoneService $cityPhoneService;

    public function __construct(CityPhoneService $cityPhoneService)
    {
        $this->cityPhoneService = $cityPhoneService;
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/city_phone/{page}", name="admin_city_phone_index", requirements={"page"="\d+"})
     */
    public function index(Request $request, int $page = 1)
    {
        $searchData = [
            'country' => $request->get('country'),
            'stateOrProvince' => $request->get('stateOrProvince'),
            'city' => $request->get('city'),
        ];

        $offset = ($page - 1) * self::LIMIT;

        $cityPhones = $this->cityPhoneService->getCityPhones($searchData, $page, self::LIMIT, $offset);

        return $this->render('admin/city_phone/index.html.twig', [
            'cityPhones' => $cityPhones
        ]);
    }

    /**
     * @return Response
     * @Route("/city_phone/create", name="admin_city_phone_create")
     */
    public function create()
    {
        return $this->render('admin/city_phone/create.html.twig');
    }

    /**
     * @return Response
     * @Route("/city_phone/store", name="admin_city_phone_store", methods={"POST"})
     */
    public function store(Request $request)
    {
        try {
            if (!$request->isXmlHttpRequest()) {
                throw $this->createNotFoundException();
            }

            $data = FormUtils::getJson($request);

            $this->cityPhoneService->create($data);

            return $this->json(['success' => true, 'message' => 'created', 'url' => $this->generateUrl('admin_city_phone_index')]);
        } catch (Exception|Throwable $exception) {
            return $this->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/city_phone/edit/{id}", name="admin_city_phone_edit", requirements={"page"="\d+"})
     */
    public function edit(CityPhone $cityPhone)
    {
        return $this->render('admin/city_phone/edit.html.twig', ['cityPhone' => $cityPhone]);
    }

    /**
     * @return Response
     * @Route("/city_phone/update", name="admin_city_phone_update", methods={"POST"})
     */
    public function update(Request $request)
    {
        try {
            if (!$request->isXmlHttpRequest()) {
                throw $this->createNotFoundException();
            }

            $this->cityPhoneService->update($request);

            return $this->json([
                'success' => true,
                'message' => 'created',
                'url' => $this->generateUrl('admin_city_phone_index')
            ]);
        } catch (Exception|Throwable $exception) {
            return $this->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }

    /**
     * @Route("/city_phone/delete/{id}", name="admin_city_phone_delete", methods={"GET"})
     */
    public function delete(CityPhone $cityPhone)
    {
        $this->cityPhoneService->delete($cityPhone);
        return $this->redirectToRoute('admin_city_phone_index');
    }
}