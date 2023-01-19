<?php


namespace App\Service\Notifications;


use App\Criteria\ListingSearchCriteria;
use App\Entity\Listing;
use App\Entity\User;
use App\Model\Assessment\AssessmentModel;
use App\Model\ContactUs\ContactUsModel;
use App\Service\Utils\UrlUtils;
use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailService
{
    private LoggerInterface $logger;
    private Environment $twig;
    private SesClient $client;
    private SerializerInterface $serializer;
    private UrlUtils $urlUtils;
    private UrlGeneratorInterface $router;


    const noReplyEmail = "no_reply@viksemenov.com";
    const senderEmail = "\"Dan Marusin - viksemenov\" <vadim@viksemenov.com>";
    const contactUsNotificationEmail = "\"Dan Marusin - viksemenov\" <vadim@viksemenov.com>";
    const assessmentNotificationEmail = "\"Dan Marusin - viksemenov\" <vadim@viksemenov.com>";
    const newListingAlertEmail = "\"Dan Marusin - viksemenov\" <vadim@viksemenov.com>";
    const charset = 'UTF-8';

    public function __construct(
        LoggerInterface       $logger,
        Environment           $twig,
        SesClient             $client,
        SerializerInterface   $serializer,
        UrlUtils              $urlUtils,
        UrlGeneratorInterface $router
    )
    {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->client = $client;
        $this->serializer = $serializer;
        $this->urlUtils = $urlUtils;
        $this->router = $router;
    }

    /**
     * @param User $user
     * @param Listing[] $listings
     * @param ListingSearchCriteria $criteria
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function setNewListingAlertEmail(User $user, array $listings, ListingSearchCriteria $criteria)
    {
        $searchUrl = $this->urlUtils->searchCriteriaToUri($criteria, UrlGeneratorInterface::ABSOLUTE_URL);

        $html = $this->twig->render(
            'emails/listings-alert/index.html.twig',
            [
                'user' => $user,
                'listings' => $listings,
                'searchUrl' => $searchUrl,
            ]
        );


        $subject = "New Listings " . Carbon::now()->format('Y-m-d H:i');
        $recipient = "\"{$user->getName()}\" <{$user->getEmail()}>";
        try {
            $result = $this->client->sendEmail(
                [
                    'Destination' => [
                        'ToAddresses' => [$recipient],
                    ],
                    'ReplyToAddresses' => [self::assessmentNotificationEmail],
                    'Source' => self::noReplyEmail,
                    'Message' => [
                        'Body' => [
                            'Html' => [
                                'Charset' => self::charset,
                                'Data' => $html,
                            ],
//                            'Text' => [
//                                'Charset' => self::charset,
//                                'Data' => $plainText,
//                            ],
                        ],
                        'Subject' => [
                            'Charset' => self::charset,
                            'Data' => $subject,
                        ],
                    ],
                ]
            );
            $messageId = $result['MessageId'];
            $this->logger->info("Email sent! Message ID: $messageId");
        } catch (AwsException $e) {
            // output error message if fails
            $this->logger->error($e->getMessage());
            $this->logger->error("The email was not sent. Error message: " . $e->getAwsErrorMessage());
        }
    }

    public function setAssessmentRequest(AssessmentModel $assessmentModel)
    {
        $html = $this->twig->render(
            'emails/assessment-request-notification.html.twig',
            $this->serializer->normalize($assessmentModel)
        );

        $senderEmail = "$assessmentModel->name <$assessmentModel->email>";
        $subject = "Assessment request from $assessmentModel->name";
        try {
            $result = $this->client->sendEmail(
                [
                    'Destination' => [
                        'ToAddresses' => [self::assessmentNotificationEmail],
                    ],
                    'ReplyToAddresses' => [$senderEmail],
                    'Source' => self::noReplyEmail,
                    'Message' => [
                        'Body' => [
                            'Html' => [
                                'Charset' => self::charset,
                                'Data' => $html,
                            ],
//                            'Text' => [
//                                'Charset' => self::charset,
//                                'Data' => $plainText,
//                            ],
                        ],
                        'Subject' => [
                            'Charset' => self::charset,
                            'Data' => $subject,
                        ],
                    ],
                ]
            );
            $messageId = $result['MessageId'];
            $this->logger->info("Email sent! Message ID: $messageId");
        } catch (AwsException $e) {
            // output error message if fails
            $this->logger->error($e->getMessage());
            $this->logger->error("The email was not sent. Error message: " . $e->getAwsErrorMessage());
        }
    }

    public function sendContactUsRequestNotificationEmail(ContactUsModel $contactUsModel)
    {
        $html = $this->twig->render(
            'emails/contact-us-notification.html.twig',
            $this->serializer->normalize($contactUsModel)
        );

        $senderEmail = "$contactUsModel->name <$contactUsModel->email>";
        $subject = "Contact us request from $contactUsModel->name";
        try {
            $result = $this->client->sendEmail(
                [
                    'Destination' => [
                        'ToAddresses' => [self::contactUsNotificationEmail],
                    ],
                    'ReplyToAddresses' => [$senderEmail],
                    'Source' => self::noReplyEmail,
                    'Message' => [
                        'Body' => [
                            'Html' => [
                                'Charset' => self::charset,
                                'Data' => $html,
                            ],
//                            'Text' => [
//                                'Charset' => self::charset,
//                                'Data' => $plainText,
//                            ],
                        ],
                        'Subject' => [
                            'Charset' => self::charset,
                            'Data' => $subject,
                        ],
                    ],
                ]
            );
            $messageId = $result['MessageId'];
            $this->logger->info("Email sent! Message ID: $messageId");
        } catch (AwsException $e) {
            // output error message if fails
            $this->logger->error($e->getMessage());
            $this->logger->error("The email was not sent. Error message: " . $e->getAwsErrorMessage());
        }
    }

    public function sendEmail(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        array  $templateData = []
    )
    {
        $html = $this->twig->render('emails/new-listings-notification.html.twig', $templateData);

        $recipient = "\"$recipientName\" <$recipientEmail>";
        try {
            $result = $this->client->sendEmail(
                [
                    'Destination' => [
                        'ToAddresses' => [$recipient],
                    ],
//                    'ReplyToAddresses' => [self::senderEmail],
                    'Source' => self::senderEmail,
                    'Message' => [
                        'Body' => [
                            'Html' => [
                                'Charset' => self::charset,
                                'Data' => $html,
                            ],
//                            'Text' => [
//                                'Charset' => self::charset,
//                                'Data' => $plainText,
//                            ],
                        ],
                        'Subject' => [
                            'Charset' => self::charset,
                            'Data' => $subject,
                        ],
                    ],
                ]
            );
            $messageId = $result['MessageId'];
            $this->logger->info("Email sent! Message ID: $messageId");
        } catch (AwsException $e) {
            // output error message if fails
            $this->logger->error($e->getMessage());
            $this->logger->error("The email was not sent. Error message: " . $e->getAwsErrorMessage());
        }
    }

    public function notifyRealtor($user)
    {
        $url = $this->router->generate('admin_user_details', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $html = $this->twig->render('emails/realtor-notification.html.twig', ['user' => $user, 'url' => $url]);

        $senderEmail = $user->getName() . " <" . $user->getEmail() . ">";
        $subject = "Signed up for viewing from " . $user->getName();
        try {
            $result = $this->client->sendEmail(
                [
                    'Destination' => [
                        'ToAddresses' => [self::contactUsNotificationEmail],
                    ],
                    'ReplyToAddresses' => [$senderEmail],
                    'Source' => self::noReplyEmail,
                    'Message' => [
                        'Body' => [
                            'Html' => [
                                'Charset' => self::charset,
                                'Data' => $html,
                            ],
//                            'Text' => [
//                                'Charset' => self::charset,
//                                'Data' => $plainText,
//                            ],
                        ],
                        'Subject' => [
                            'Charset' => self::charset,
                            'Data' => $subject,
                        ],
                    ],
                ]
            );
            $messageId = $result['MessageId'];
            $this->logger->info("Email sent! Message ID: $messageId");
        } catch (AwsException $e) {
            // output error message if fails
            $this->logger->error($e->getMessage());
            $this->logger->error("The email was not sent. Error message: " . $e->getAwsErrorMessage());
        }
    }
}