<?php


namespace App\Controller;


use App\Form\Assessment\AssessmentType;
use App\Model\Assessment\AssessmentModel;
use App\Service\Listing\ListingSearchDataService;
use App\Service\Notifications\EmailService;
use App\Service\Utils\FormUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssessmentController extends AbstractController
{
    private ListingSearchDataService $searchDataService;
    private FormFactoryInterface $formFactory;
    public EmailService $emailService;

    public function __construct(
        ListingSearchDataService $searchDataService,
        FormFactoryInterface $formFactory,
        EmailService $emailService
    ) {
        $this->searchDataService = $searchDataService;
        $this->formFactory = $formFactory;
        $this->emailService = $emailService;
    }

    /**
     * @Route("/assessment", name="assessment", priority=10)
     */
    public function assessment()
    {
        return $this->render('assessment/index.html.twig', ['agent' => $this->searchDataService->getAgentObject()]);
    }

    /**
     * @Route ("/price-your-home", name="price-your-home", priority=10)
     */
    public function priceYourHome()
    {
        return $this->render(
            'price_your_home/index.html.twig',
            ['agent' => $this->searchDataService->getAgentObject()]
        );
    }

    /**
     * @Route ("/assessment/request/new", name="new_assessment_request", priority=10, methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function processAssessmentRequest(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $assessmentFormData = new AssessmentModel();
        $form = $this->formFactory->createNamed('', AssessmentType::class, $assessmentFormData);

        $form->submit(FormUtils::getJson($request));

        if (!$form->isSubmitted()) {
            return $this->json(['message' => "Could not process the request"], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return $this->json(['errors' => FormUtils::getErrorMessages($form)], Response::HTTP_BAD_REQUEST);
        }

        $this->emailService->setAssessmentRequest($assessmentFormData);

        return $this->json(['message' => 'OK'], Response::HTTP_CREATED);
    }
}