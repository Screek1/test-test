<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Service\Listing\ListingService;
use App\Service\Page\PageService;
use App\Service\Utils\FormUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/static-pages", priority=10)
 */
class AdminStaticPageController extends AbstractController
{
    private FormFactoryInterface $formFactory;
    private PageService $pageService;
    private ListingService $listingService;

    public function __construct(
        FormFactoryInterface $formFactory,
        PageService $pageService,
        ListingService $listingService
    )
    {
        $this->formFactory = $formFactory;
        $this->pageService = $pageService;
        $this->listingService = $listingService;
    }


    /**
     * @Route("/", name="page_index", methods={"GET"})
     */
    public function index(PageRepository $pageRepository): Response
    {
        return $this->render('admin/page/index.html.twig', [
            'static_pages' => $pageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{type}", name="page_new", methods={"GET","POST"}, requirements={"type"="(static|search|landing)"})
     */
    public function new(Request $request, string $type = null): Response
    {
        return $this->render('admin/page/new.html.twig', [
            'form_type' => $type,
            'criteriaOptions' => $this->listingService->getSearchFormObject()

        ]);
    }

    /**
     * @Route("/page/create/{type}", name="page_create", methods={"POST"}, requirements={"type"="(static|search|landing)"})
     */
    public function create(Request $request, string $type = null): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $data = FormUtils::getJson($request);
        $staticPage = $this->pageService->createPage($data);

        return $this->json([
            'message' => 'created',
            'url' => $this->generateUrl('page_edit', ['id' => $staticPage->getId(), 'type' => $staticPage->getType()])
        ]);
    }

    /**
     * @Route("/{type}-{id}/edit", name="page_edit", methods={"GET"}, requirements={"type"="(static|search|landing)"})
     * @param Request $request
     * @param Page $staticPage
     * @param $type
     * @return Response
     */
    public function edit(Request $request, Page $staticPage, $type): Response
    {
        $data = ['static_page' => $staticPage, 'form_type' => $type];
        $criteria = $staticPage->getCriteria();
        $data['criteriaOptions'] = $this->listingService->getSearchFormObject();

        if ($criteria != [] && $criteria != null) {
            $data['criteria'] = $criteria;
            $data['keywords'] = isset($criteria['keywords']) ? $criteria['keywords'] : null;
            $data['selectedHomeTypes'] = isset($criteria['homeTypes']) ? $criteria['homeTypes'] : null;
            $data['selectedListingTypes'] = isset($criteria['listingTypes']) ? $criteria['listingTypes'] : null;
        }

        return $this->render('admin/page/edit.html.twig', $data);
    }

    /**
     * @Route("/{type}-{id}/update", name="page_update", methods={"POST"}, requirements={"type"="(static|search|landing)"})
     * @param Request $request
     * @param Page $staticPage
     * @param $type
     * @return Response
     */
    public function update(Request $request, Page $staticPage, $type)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $data = FormUtils::getJson($request);
        $this->pageService->updatePage($staticPage, $data);

        return $this->json(['message' => 'Success']);
    }
    /**
     * @Route("/{id}", name="page_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Page $staticPage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$staticPage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($staticPage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('page_index');
    }
}
