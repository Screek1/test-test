<?php


namespace App\Controller;


use App\Criteria\ListingSearchCriteria;
use App\Criteria\SearchCriteriaBox;
use App\Criteria\SearchCriteriaPoint;
use App\Service\Listing\ListingService;
use App\Service\Utils\SearchUtils;
use App\Service\Utils\UrlUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UrlUtilsController extends AbstractController
{
    private UrlUtils $urlUtils;
    private SerializerInterface $serializer;

    public function __construct(UrlUtils $urlUtils, SerializerInterface $serializer)
    {
        $this->urlUtils = $urlUtils;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/utils/criteria-to-uri", priority=10, name="criteria_to_url", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function criteriaToUri(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $json = json_decode($request->getContent(), true);

        $criteria = new ListingSearchCriteria();
        $filters = [];
        if (isset($json['location']) && is_string($json['location'])) {
            $locationParts = explode(',', $json['location']);
            if (count($locationParts) === 2) {
                $criteria->city = $locationParts[0];
                $criteria->stateOrProvince = ListingService::getProvinceName($locationParts[1]);
            } elseif (count($locationParts) === 4) {
                $criteria->box = implode(',', $locationParts);
            } else {
                throw new BadRequestException("unsupported 'location' format");
            }
        } else {
            throw new BadRequestException("'location' is not provided");
        }

        if (isset($json['filters']) && is_array($json['filters'])) {
            $filters = array_merge($filters, $json['filters']);
            if (isset($box)) {
                $filters['box'] = $box;
            }

            $criteria = SearchUtils::buildSearchCriteriaData($criteria, $filters);
        }

        return $this->json(['url' => $this->urlUtils->searchCriteriaToUri($criteria)]);
    }

    /**
     * @Route("/api/utils/uri-to-criteria", priority=10, name="url_to_criteria", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws ExceptionInterface
     */
    public function uriToCriteria(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $json = json_decode($request->getContent(), true);
        if (empty($json['uri'])) {
            throw $this->createNotFoundException();
        }

        $criteria = $this->urlUtils->uriToSearchCriteria($json['uri']);

        return $this->json($this->serializer->normalize($criteria));
    }
}