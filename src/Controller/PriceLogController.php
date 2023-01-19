<?php


namespace App\Controller;


use App\Service\Listing\PriceLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PriceLogController extends AbstractController
{
    private PriceLogService $priceLogService;

    public function __construct(PriceLogService $priceLogService)
    {
        $this->priceLogService = $priceLogService;
    }

    /**
     * @Route("/api/price-log-data/{listingId}/{range}", name="price_log_data", priority="10", methods={"GET"}, requirements={"range": "1|6|12|@", "listingId": "\d+"})
     * @param int $listingId
     * @param int $range
     * @param Request $request
     * @return Response
     */
    public function getLogData(Request $request, int $listingId, string $range = '1'): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $data = $this->priceLogService->getPriceLogDataForListing($listingId, intval($range));

        return $this->json($data);
    }

}