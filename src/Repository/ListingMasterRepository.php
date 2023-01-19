<?php

namespace App\Repository;

use App\Entity\ListingMaster;
use App\Service\Listing\ListingConstants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ListingMaster|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListingMaster|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListingMaster[]    findAll()
 * @method ListingMaster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListingMasterRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;
    const INSERT_LISTING_MASTER_CHUNK_SIZE = 1000;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManager = $entityManagerInterface;
        parent::__construct($registry, ListingMaster::class);
    }

    public function insertMasterList($feedId, array $masterList = [], string $classId = null)
    {
        $chunksMasterList = array_chunk($masterList, self::INSERT_LISTING_MASTER_CHUNK_SIZE);
        foreach ($chunksMasterList as $items) {
            $values = array_fill(0, count($items), ($classId) ? "(?,?,?,?)" : "(?,?,?)");
            $valuesForQuery = implode(",", $values);

            if ($classId) {
                $sql = "insert into listing_master(feed_id,feed_listing_id,updated_time, class_id)
                        values {$valuesForQuery}
                        on conflict (feed_id,feed_listing_id)
                        do update set updated_time = EXCLUDED.updated_time";
            } else {
                $sql = "insert into listing_master(feed_id,feed_listing_id,updated_time)
                        values {$valuesForQuery}
                        on conflict (feed_id,feed_listing_id)
                        do update set updated_time = EXCLUDED.updated_time";
            }
            $rsm = new ResultSetMapping();
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $paramCounter = 1;
            foreach ($items as $item) {
                $query->setParameter($paramCounter++, $feedId);
                $query->setParameter($paramCounter++, $item->getListingKey());
                $query->setParameter($paramCounter++, $item->getLastModifyDate());
                if ($classId) {
                    $query->setParameter($paramCounter++, $item->getClassId());
                }
            }
            $query->execute();
        }
    }

    public function truncateListingMasterTable($feedId, $classId = null)
    {
        $where = 'WHERE feed_id = \'' . $feedId . '\'';
        if ($classId) {
            $where .= ' AND class_id = \'' . $classId. '\'';
        }
        $rsm = new ResultSetMapping();
        $this->entityManager->createNativeQuery('DELETE FROM listing_master ' . $where, $rsm)->execute();
    }

}
