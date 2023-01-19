<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 18.09.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Feed;


use App\Entity\Feed;
use App\Repository\FeedRepository;
use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class FeedService
{
    private EntityManagerInterface $entityManager;
    private FeedRepository $feedRepository;

    public function __construct(EntityManagerInterface $entityManager, FeedRepository $feedRepository)
    {
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
    }

    public function setBusyByFeedName(string $name, bool $busy, $classId = null): ?DateTimeInterface
    {
        $data = ['name' => $name];

        if ($classId) {
            $data['classID'] = $classId;
        }

        $setBusyFeed = $this->feedRepository->findOneBy($data);
        $setBusyFeed->setBusy($busy);
        $this->entityManager->flush();
        return $setBusyFeed->getLastRunTime();
    }

    public function isFeedBusy(string $name, $classId = null)
    {
        $data = ['name' => $name];

        if ($classId) {
            $data['classID'] = $classId;
        }

        $busyFeed = $this->feedRepository->findOneBy($data);
        return $busyFeed->isBusy();
    }

    public function setLastRunTimeByFeedName(string $name, DateTimeInterface $date, $classId = null)
    {
        $data = ['name' => $name];

        if ($classId) {
            $data['classID'] = $classId;
        }

        $lastRunTimeFeed = $this->feedRepository->findOneBy($data);
        $lastRunTimeFeed->setLastRunTime($date);
        $this->entityManager->flush();
    }

    public function refreshFeedByName(string $name): Feed
    {
        $feed = $this->feedRepository->findOneBy([
            'name' => $name
        ]);
        $feed->setLastRunTime(Carbon::now());
        $feed->setBusy(false);
        $this->entityManager->flush();

        return $feed;
    }
}