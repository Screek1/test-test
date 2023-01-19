<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 30.12.2020
 *
 * @package viksemenov20
 */

namespace App\Service\Page;

use App\Criteria\ListingSearchCriteria;
use App\Entity\Page;
use App\Repository\PageRepository;
use App\Service\Listing\ListingConstants;
use App\Service\Listing\ListingService;
use Doctrine\ORM\EntityManagerInterface;

class PageService
{

    private PageRepository $pageRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PageRepository $pageRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->pageRepository = $pageRepository;
        $this->entityManager = $entityManager;

    }

    public function search(array $criteria): ?Page
    {
        return $this->pageRepository->findOneBy($criteria);
    }

    public function updatePage(Page $page, $data)
    {
        $page->setTitle($data['title']);
        $page->setDescription($data['description']);
        $page->setSlug($data['slug']);
        $page->setStatus(isset($data['status']));
        $page->setType($data['type']);

        if (isset($data['criteria'])) {
            $page->setCriteria($data['criteria']);
        }

        if (isset($data['content'])) {
            $page->setContent($data['content'] ?? null);
        }

        $this->entityManager->flush();
    }

    public function createPage($data)
    {
        $page = new Page();

        $page->getId();
        $page->setTitle($data['title']);
        $page->setDescription($data['description']);
        $page->setSlug($data['slug']);
        $page->setStatus(isset($data['status']));
        $page->setType($data['type']);

        if (isset($data['criteria'])) {
            $page->setCriteria($data['criteria']);
        }

        if (isset($data['content'])) {
            $page->setContent($data['content'] ?? null);
        }

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    public function criteriaToListingSearchFormat(Page $page)
    {
        $criteriaByPage = $page->getCriteria();

        $listCriteria = new ListingSearchCriteria();
        $listCriteria->feedId = ListingConstants::FeedDdf;
        $listCriteria->keywordsArray = $this->getKeywords($criteriaByPage);
        $listCriteria->propertyTypes = $this->getPropertyTypes($criteriaByPage);
        $listCriteria->location = $criteriaByPage['city'] . ',' . $criteriaByPage['province'];
        $listCriteria->city = $criteriaByPage['city'];
        $listCriteria->stateOrProvince = ListingService::getProvinceName($criteriaByPage['province']);
        $listCriteria->minBaths = (int)$criteriaByPage['minBath'];
        $listCriteria->minBeds = (int)$criteriaByPage['minBeds'];
        $listCriteria->minPrice = (int)$criteriaByPage['minPrice'];
        $listCriteria->maxPrice = (int)$criteriaByPage['maxPrice'];
        $listCriteria->minLivingArea = $criteriaByPage['minSquareFeet'] ? $criteriaByPage['minSquareFeet'] : null;
        $listCriteria->maxLivingArea = $criteriaByPage['maxSquareFeet'] ? $criteriaByPage['maxSquareFeet'] : null;
        $listCriteria->minLotSize = $criteriaByPage['lotSize'] != "No Min" ? $criteriaByPage['lotSize'] : null;
        $listCriteria->minYearBuilt = $criteriaByPage['minYearBuilt'] ? $criteriaByPage['minYearBuilt'] : null;
        $listCriteria->maxYearBuilt = $criteriaByPage['maxYearBuilt'] ? $criteriaByPage['maxYearBuilt'] : null;

        return $listCriteria;
    }

    private function getKeywords($criteria)
    {
        if (!isset($criteria['keywords'])) { return null; }
        return (is_string($criteria['keywords'])) ? [$criteria['keywords']] : $criteria['keywords'];
    }

    private function getPropertyTypes($criteria)
    {
        if (!isset($criteria['homeTypes'])) {
            $criteria['homeTypes'] = [];
        }
        if (!isset($criteria['listingTypes'])) {
            $criteria['listingTypes'] = [];
        }

        $criteria['homeTypes'] = is_string($criteria['homeTypes']) ? [$criteria['homeTypes']] : $criteria['homeTypes'];
        $criteria['listingTypes'] = is_string($criteria['listingTypes']) ? [$criteria['listingTypes']] : $criteria['listingTypes'];

        return array_merge($criteria['homeTypes'], $criteria['listingTypes']);
    }
}
