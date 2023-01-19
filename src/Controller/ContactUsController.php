<?php


namespace App\Controller;


use App\Form\ContactUs\ContactUsType;
use App\Form\ViewingRequestType;
use App\Model\ContactUs\ContactUsModel;
use App\Model\ViewingRequestModel;
use App\Service\Notifications\EmailService;
use App\Service\Utils\FormUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactUsController extends AbstractController
{
    private FormFactoryInterface $formFactory;
    private EmailService $emailService;

    public function __construct(FormFactoryInterface $formFactory, EmailService $emailService)
    {
        $this->formFactory = $formFactory;
        $this->emailService = $emailService;
    }

    /**
     * @Route("/contact-us", name="contact_us", priority=10, methods={"GET"})
     */
    public function contactUs()
    {
        $contactUsArr = [
            'phone'   => '778-918-5990',
            'address' => '600-777 Hornby Street, Vancouver, BC V6Z 1S4',
            'email'   => 'vadim@viksemenov.com',
            'lat'     => 49.2837736,
            'lon'     => -123.1222891,
        ];
        return $this->render('contact_us/index.html.twig', [
            'contactsData' => $contactUsArr,
        ]);
    }

    /**
     * @Route("/contact-us", priority=10, name="contact_us_process", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function processContactUsForm(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $contactUsFormData = new ContactUsModel();
        $form = $this->formFactory->createNamed('', ContactUsType::class, $contactUsFormData);

        $form->submit(FormUtils::getJson($request));

        if (!$form->isSubmitted()) {
            return $this->json(['message' => "Could not process the request"], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return $this->json(['errors' => FormUtils::getErrorMessages($form)], Response::HTTP_BAD_REQUEST);
        }

        $this->emailService->sendContactUsRequestNotificationEmail($contactUsFormData);

        return $this->json([]);
    }
}