<?php

namespace App\Controller\Admin;

use App\Repository\FeedRepository;
use App\Service\Feed\FeedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", priority=10)
 */
class FeedController extends AbstractController
{
    private FeedService $feedService;
    private FeedRepository $feedRepository;

    public function __construct(FeedService $feedService, FeedRepository $feedRepository)
    {
        $this->feedRepository = $feedRepository;
        $this->feedService = $feedService;
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/feeds/{page}", name="admin_feeds_index", requirements={"page"="\d+"})
     */
    public function index($page = 1): Response
    {
        $feeds = $this->feedRepository->findAll();
        return $this->render('admin/feed/index.html.twig', [
            'feeds' => $feeds
        ]);
    }

    /**
     * @param int $page
     * @return Response
     * @Route("/feed/{name}", name="admin_feed_refresh", methods={"POST"})
     */
    public function refresh(string $name): Response
    {
        $feed = $this->feedService->refreshFeedByName($name);
        return $this->json([
            'lastRunTime' => $feed->getLastRunTime()->format('Y-m-d H:i:s'),
            'isBusy' => $feed->isBusy() ? 'Running' : 'Stop'
        ]);
    }
}
