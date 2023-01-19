<?php

namespace App\Repository;

use App\Criteria\ListingSearchCriteria;
use App\Entity\Listing;
use App\Model\Listing\ListingStatus;
use App\Model\Listing\ProcessingStatus;
use App\Service\Listing\ListingConstants;
use App\Service\Listing\ListingCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Listing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Listing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Listing[]    findAll()
 * @method Listing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListingRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private float $latitude;
    private float $longtitude;

    public function __construct(
        ManagerRegistry        $registry,
        EntityManagerInterface $entityManager,
        LoggerInterface        $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        parent::__construct($registry, Listing::class);
    }

    public function getActiveListingById(int $id): ?Listing
    {
        return $this->findOneBy(
            [
                'id' => $id,
                'deletedDate' => null,
            ]
        );
    }

    /**
     * @param int $limit
     * @return array|Listing[]
     */
    public function findMarkedForDeletion(int $limit = 100)
    {
        return $this->findBy([
            'deletedDate' => null,
            'taggedForDeletion' => true,
        ], null, $limit);
    }

    public function getListingCountForProcessing(): int
    {
        return $this->count(
            [
                'deletedDate' => null,
                'status' => [
                    ListingStatus::New,
                    ListingStatus::Updated,
                ],
            ]
        );
    }

    /**
     * @param $limit
     * @return Listing[]
     */
    public function getListingWithMissingPriceLog($limit): array
    {
        $sql = "select * from listing l 
                where 
                  l.status IN (:statuses) 
                  and l.deleted_date is null
                  and not exists (
                      select * from price_log pl where pl.date = CURRENT_DATE and pl.listing_id = l.id
                  ) limit :limit";
        $rsm = new ResultSetMappingBuilder($this->entityManager);

        $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('statuses', ListingStatus::VisibleStatuses);
        $query->setParameter('limit', $limit);

        return $query->getResult();
    }

    /**
     * @param string $mlsNum
     * @return Listing[]
     */
    public function getActiveByMlsNum(string $mlsNum): array
    {
        return $this->createQueryBuilder('l')
            ->where('upper(l.mlsNum) = upper(:mlsNum) and l.deletedDate is null')
            ->setParameter('mlsNum', $mlsNum)
            ->getQuery()->getResult();
    }

    public function getActiveByFeedListingID(string $feedListingID, string $feedId): ?Listing
    {
        return $this->findOneBy(
            [
                'feedID' => $feedId,
                'feedListingID' => $feedListingID,
                'deletedDate' => null,
            ]
        );
    }

    public function getListingByIdAndMls(int $id, string $mlsNum): ?Listing
    {
        return $this->findOneBy(
            [
                'id' => $id,
                'mlsNum' => $mlsNum
            ]
        );
    }

    /**
     * @param int $userId
     * @return array|Listing[]
     */
    public function getUserFavoriteListings(int $userId): array
    {
        $sql = "select * from listing 
                  where id in (select listing_id from favorite_listings where user_id = :userId) 
                    and status IN ('live', 'updated') 
                    and deleted_date is null";
        $rsm = new ResultSetMappingBuilder($this->entityManager);

        $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $userId);

        return $query->getResult();
    }

    /**
     * @return array|string[]
     */
    public function getAllListingTypes(): array
    {
        $sql = "select DISTINCT type from listing where status IN (:statuses) and deleted_date is null;";
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('type', 'type');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('statuses', ListingStatus::VisibleStatuses);

        $result = $query->getResult();

        return array_map(
            function ($item) {
                return $item['type'];
            },
            $result
        );
    }

    private const DeleteRemovedListingsSQL = "update App\Entity\Listing l SET l.deletedDate = current_timestamp() 
      where l.feedID = :feedId and l.deletedDate is null and 
      not exists (
        select lm.feedListingId from App\Entity\ListingMaster lm 
        where lm.feedListingId = l.feedListingID and lm.feedId = l.feedID
      )";

    private const TagListingsForDeletionSQL = "update App\Entity\Listing l SET l.taggedForDeletion = true 
      where l.feedID = :feedId and l.deletedDate is null and l.taggedForDeletion = false and 
      not exists (
        select lm.feedListingId from App\Entity\ListingMaster lm 
        where lm.feedListingId = l.feedListingID and lm.feedId = l.feedID
      )";

    private const TagListingsForDeletionWithClassIdSQL = "update App\Entity\Listing l SET l.taggedForDeletion = true 
      where l.feedID = :feedId and l.deletedDate is null and l.classID = :classId and l.taggedForDeletion = false and 
      not exists (
        select lm.feedListingId from App\Entity\ListingMaster lm 
        where lm.feedListingId = l.feedListingID and lm.feedId = l.feedID
        and lm.classID = l.classID
      )";

    private const SelectRemovedListingsSQL = "select l from App\Entity\Listing l
      where l.feedID = :feedId and l.deletedDate is null and 
      not exists (
        select lm.feedListingId from App\Entity\ListingMaster lm 
        where lm.feedListingId = l.feedListingID and lm.feedId = l.feedID
      )";

    private const DeleteListingsSQL = "update listing SET deleted_date = now() where deleted_date is null and id in (:ids);";

    public function deleteListings(array $listings)
    {
        $ids = array_map(function (Listing $item) {
            return $item->getId();
        }, $listings);

        $rsm = new ResultSetMapping();
        $query = $this->entityManager->createNativeQuery(self::DeleteListingsSQL, $rsm);
        $query->setParameter('ids', $ids);

        return $query->execute();
    }

    public function tagListingsForDeletion(string $feedId, string $classId = null)
    {
//        $query = $this->getEntityManager()->createQuery(self::SelectRemovedListingsSQL);
//        $query->setParameter('feedId', $feedId)
//        $removedListings = $query->getResult();
//
//        $this->logger->info(json_encode($this->count($removedListings)));

        $query = $this->entityManager->createQuery($classId ? self::TagListingsForDeletionWithClassIdSQL : self::TagListingsForDeletionSQL);

        $query->setParameter('feedId', $feedId);

        if ($classId) {
            $query->setParameter('classId', $classId);
        }

        return $query->execute();
    }

    private const InsertMissingListingSql = <<<SQL
  insert into listing(feed_id,feed_listing_id,status,processing_status,self_listing,last_update_from_feed) 
  select lm.feed_id, lm.feed_listing_id, 'new' as status,'none' as processing_status, false as self_listing, lm.updated_time as last_update_from_feed
  from listing_master lm 
  where lm.feed_id = :feedId
  on conflict (feed_id,feed_listing_id) where deleted_date is null 
  do update set last_update_from_feed = excluded.last_update_from_feed;
SQL;

    private const InsertMissingIdxListingSql = <<<SQL
  insert into listing(feed_id,feed_listing_id,status,processing_status,self_listing,last_update_from_feed,class_id) 
  select lm.feed_id, lm.feed_listing_id, 'new' as status,'none' as processing_status, false as self_listing, lm.updated_time as last_update_from_feed, lm.class_id
  from listing_master lm 
  where lm.feed_id = :feedId
  and lm.class_id = :classId
  on conflict (feed_id,feed_listing_id) where deleted_date is null 
  do update set last_update_from_feed = excluded.last_update_from_feed, class_id = excluded.class_id;
SQL;


    public function createMissingListingsFromDdfListingMaster(string $feedId)
    {
        $rsm = new ResultSetMapping();
        $this->getEntityManager()->createNativeQuery(
            self::InsertMissingListingSql,
            $rsm
        )->setParameter('feedId', $feedId)->execute();
    }

    public function createMissingListingsFromIdxListingMaster(string $feedId, string $classId)
    {
        $rsm = new ResultSetMapping();
        $this->getEntityManager()->createNativeQuery(
            self::InsertMissingIdxListingSql,
            $rsm
        )->setParameter('feedId', $feedId)
            ->setParameter('classId', $classId)
            ->execute();
    }

    /**
     * @param float $neLat
     * @param float $neLng
     * @param float $swLat
     * @param float $swLng
     * @return array
     * @deprecated
     */
    public function getAllListingsInMapBox(float $neLat, float $neLng, float $swLat, float $swLng): array
    {
        $boxString = "box '((" . $neLat . ", " . $neLng . "),(" . $swLat . ", " . $swLng . "))'";
        try {
            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
            $sql = "select * from listing where status IN ('" . ListingStatus::Live . "', '" .
                ListingStatus::Updated . "') and processing_status != '" .
                ProcessingStatus::Error .
                "' and coordinates IS NOT NULL and coordinates <@ $boxString AND deleted_date IS NULL";
            $query = $this->entityManager->createNativeQuery($sql, $rsm);

            return $query->getResult();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }

    public function getSimilarListings(
        ?string $type,
        ?string $ownershipType,
        ?int    $bedRooms,
        ?int    $livingArea,
        ?int    $lotSize,
        ?int    $yearBuild,
        ?array  $coordinates,
        ?string $mlsNum
    ): array
    {
        try {
            $this->latitude = $coordinates['lat'];
            $this->longtitude = $coordinates['lon'];
            $sqlArray = [];
            $params = [];
            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
            if ($type) {
                $sqlArray[] = 'type = :propertyType';
                $params['propertyType'] = $type;
            }
            if ($ownershipType) {
                $sqlArray[] = 'ownership_type = :ownershipType';
                $params['ownershipType'] = $ownershipType;
            }
            if (isset($bedRooms)) {
                $sqlArray[] = 'bedrooms = :bedroomsCount';
                $params['bedroomsCount'] = $bedRooms;
            }
            if ($livingArea) {
                if ($livingArea > 150) {
                    $sqlArray[] = 'living_area <@ int4range(:livingAreaFrom,:livingAreaTo)';
                    $params['livingAreaFrom'] = $livingArea - 150;
                    $params['livingAreaTo'] = $livingArea + 150;
                } else {
                    $sqlArray[] = 'living_area <= :livingArea';
                    $params['livingArea'] = $livingArea + 150;
                }
            }
            if ($lotSize) {
                if ($lotSize > 500) {
                    $sqlArray[] = 'lot_size <@ int4range(:lotSizeFrom,:lotSizeTo)';
                    $params['lotSizeFrom'] = $lotSize - 500;
                    $params['lotSizeTo'] = $lotSize + 500;
                } else {
                    $sqlArray[] = 'lot_size < :lotSize';
                    $params['lotSize'] = $lotSize + 500;
                }
            }
            if ($yearBuild) {
                $sqlArray[] = 'year_built <@ int4range(:yearBuiltFrom,:yearBuiltTo)';
                $params['yearBuiltFrom'] = $yearBuild - 5;
                $params['yearBuiltTo'] = $yearBuild + 5;
            }
            $sqlArray[] = 'circle (\'(' . $this->latitude . ',' . $this->longtitude . ')\',' .
                ListingConstants::SEARCH_RADIUS / 100 . ') @> coordinates';
            if (isset($mlsNum)) {
                $sqlArray[] = 'mls_num != :mlsNumber';
                $params['mlsNumber'] = $mlsNum;
            }
            $sql = "select * from listing where status IN (:statuses) and deleted_date is null";
            if (!empty($sqlArray)) {
                $sql .= ' and ' . implode(' and ', $sqlArray);
            }
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('statuses', ListingStatus::VisibleStatuses);
            foreach ($params as $key => $param) {
                $query->setParameter($key, $param);
            }

            return $query->getResult();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }

    const CityCountersSql = <<<SQL
select city, count 
from city_stats_view 
where city in (:cities) 
  and state_or_province = :stateOrProvince 
SQL;


    public function getCounters(array $cities, string $stateOrProvince): ?array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('city', 'city');
        $rsm->addScalarResult('count', 'count');
        $query = $this->entityManager->createNativeQuery(self::CityCountersSql, $rsm);
        $query->setParameter('cities', $cities)
            ->setParameter('stateOrProvince', $stateOrProvince);

        return $query->getResult();
    }

    const CityStatsSql = <<<SQL
select city, count 
from city_stats_view 
where state_or_province = :stateOrProvince 
SQL;

    /**
     * @param string $stateOrProvince
     * @return array
     * @deprecated
     */
    public function getAllCityStats(string $stateOrProvince): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('city', 'city');
        $rsm->addScalarResult('count', 'count');
        $query = $this->entityManager->createNativeQuery(self::CityStatsSql, $rsm);
        $query->setParameter('stateOrProvince', $stateOrProvince);

        return $query->getResult();
    }

    /**
     * @param ListingCriteria $criteria
     * @param int $page
     * @param int $pageSize
     * @return int|mixed|string
     * @deprecated
     */
    public function getListingsByCriteria(ListingCriteria $criteria, int $page = 1, int $pageSize = 50)
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
        $query = $this->entityManager->createNativeQuery(
            "SELECT l.* FROM listing l
                WHERE l.feed_id = :feedId 
                    AND l.deleted_date IS NULL 
                    AND l.status IN (:statuses) 
                ORDER BY l.contract_date DESC NULLS LAST 
                LIMIT :limit 
                OFFSET :offset",
            $rsm
        );
        $query->setParameter('feedId', $criteria->feedId);
        $query->setParameter('statuses', $criteria->statuses);
        $query->setParameter('limit', $pageSize);
        $query->setParameter('offset', ($page - 1) * $pageSize);

        return $query->getResult();
    }

    public function searchListingsByMapCriteria(ListingSearchCriteria $criteria): ?array
    {
        $sqlArray = [];
        $params = [];
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
        if ($criteria->city) {
            $sqlArray[] = 'city = :city';
            $params['city'] = $criteria->city;
        }
        if ($criteria->stateOrProvince) {
            $sqlArray[] = 'state_or_province = :stateOrProvince';
            $params['stateOrProvince'] = $criteria->stateOrProvince;
        }
        $sql = "select * from listing where status IN ('" . ListingStatus::Live . "', '" .
            ListingStatus::Updated . "') and deleted_date is null";
        if (!empty($sqlArray)) {
            $sql .= ' and ' . implode(' and ', $sqlArray);
        }
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        foreach ($params as $key => $param) {
            $query->setParameter($key, $param);
        }

        return $query->getResult();
    }

    public function refreshCityStatsView(): void
    {
        $refreshCityStatsViewSql = "refresh materialized view concurrently city_stats_view";
        $rsm = new ResultSetMapping();
        $this->entityManager->createNativeQuery($refreshCityStatsViewSql, $rsm)->execute();
    }

    // For sitemap
    public function listingForSitemapByStatueOrProvince($stateOrProvinces)
    {
        $sql = 'select l.* from listing l
        where l.deleted_date is null
        and l.status in (:statuses)
        and l.state_or_province = :stateOrProvince';
        $params = ['statuses' => ListingStatus::VisibleStatuses];

        $params['stateOrProvince'] = $stateOrProvinces;


        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Listing', 'l');
        $query = $this->entityManager->createNativeQuery($sql, $rsm);

        foreach ($params as $key => $param) {
            $query->setParameter($key, $param);
        }

        return $query->getResult();
    }

    public function getStateOrProvinces()
    {
        $sql = "select distinct(l.stateOrProvince) from App\Entity\Listing l where l.stateOrProvince IS NOT NULL and l.stateOrProvince <> ''";
        $query = $this->entityManager->createQuery($sql);

        return $query->getResult();
    }

    public function getCities()
    {
        $sql = "select distinct(l.city) as city, l.stateOrProvince from App\Entity\Listing l where l.stateOrProvince IS NOT NULL and l.stateOrProvince <> ''";
        $query = $this->entityManager->createQuery($sql);

        return $query->getResult();
    }

    /**
     * @param $stateOrProvince
     * @return int|mixed|string
     * @deprecated
     */
    public function getStreetsByProvince($stateOrProvince)
    {
        $sql = "select l.streetName, count(l.streetName) as quantity from App\Entity\Listing l 
            where l.streetName IS NOT NULL 
                and l.stateOrProvince = :stateOrProvince
                and l.status in (:statuses)
                and l.deletedDate IS NULL
            group by l.streetName
            order by l.streetName asc";
        $query = $this->entityManager->createQuery($sql);
        $query->setParameter('stateOrProvince', $stateOrProvince);
        $query->setParameter('statuses', ListingStatus::VisibleStatuses);

        return $query->getResult();
    }

    public function getIsManualRemoved($feedListingID): int
    {
        $sql = "select l.feedListingID, l.isManualRemoved from App\Entity\Listing l where l.isManualRemoved = true and l.feedListingID = :feedListingID";
        $query = $this->entityManager->createQuery($sql);
        $query->setParameter('feedListingID', $feedListingID);

        return count($query->getResult());
    }
}
