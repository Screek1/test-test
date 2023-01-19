<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 04.12.2020
 *
 * @package viksemenov20
 */

namespace App\Controller;

use App\Form\ViewingRequestType;
use App\Model\ViewingRequestModel;
use App\Repository\ViewingRepository;
use App\Service\Utils\FormUtils;
use App\Service\Viewing\ViewingRequestData;
use App\Service\Viewing\ViewingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewingController extends AbstractController
{
    private FormFactoryInterface $formFactory;
    private ViewingRepository $viewingRepository;

    public function __construct(FormFactoryInterface $formFactory, ViewingRepository $viewingRepository)
    {
        $this->formFactory = $formFactory;
        $this->viewingRepository = $viewingRepository;
    }

    /**
     * @Route("/viewing/new", priority=10, name="new_viewing", methods={"POST"})
     * @param Request $request
     * @param ViewingService $viewingService
     * @return Response
     */
    public function index(Request $request, ViewingService $viewingService): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $userFormData = new ViewingRequestModel();
        $form = $this->formFactory->createNamed('', ViewingRequestType::class, $userFormData);

        $form->submit(FormUtils::getJson($request));

        if (!$form->isSubmitted()) {
            return $this->json(['message' => "Could not process the request"], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return $this->json(['errors' => FormUtils::getErrorMessages($form)], Response::HTTP_BAD_REQUEST);
        }

        $responseData = $viewingService->createViewing(
            new ViewingRequestData(
                $userFormData->getName(),
                $userFormData->getEmail(),
                $userFormData->getPhone(),
                $userFormData->getListingId()
            )
        );

        return $this->json($responseData->data, $responseData->statusCode);
    }

    /**
     * @Route("/viewing/update", priority=10, name="update_viewing", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function updateViewing(Request $request, ViewingService $viewingService)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $viewingService->updateViewing($request);

        return $this->json(['message' => 'success'], Response::HTTP_OK);
    }

    /**
     * @Route("/viewing/cancel", priority=10, name="cancel_viewing", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function cancelViewing(Request $request, ViewingService $viewingService)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $viewingService->cancelViewing($request->get('viewingId'));

        return $this->json(['message' => 'success'], Response::HTTP_OK);
    }
}