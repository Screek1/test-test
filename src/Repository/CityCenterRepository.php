<?php


namespace App\Repository;

use App\Criteria\ListingSearchCriteria;
use App\Entity\CityCenter;
use Complex\Exception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CityCenter|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityCenter|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityCenter[]    findAll()
 * @method CityCenter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityCenterRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, CityCenter::class);
        $this->entityManager = $entityManager;
    }

    public function create($params): void
    {
        try {
            $sql = "insert into city_center (city, state_or_province, latitude, longitude, zoom) values (:city, :stateOrProvince, :latitude, :longitude, :zoom)";

            $rsm = new ResultSetMapping();
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            foreach ($params as $key => $param) {
                $query->setParameter($key, $param);
            }

            $query->execute();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function update($params): void
    {
        try {
            $sql = "update city_center set city = :city, 
                       state_or_province = :stateOrProvince, 
                       latitude = :latitude, 
                       longitude = :longitude,
                       zoom = :zoom
                       where id = :id";

            $rsm = new ResultSetMapping();
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            foreach ($params as $key => $param) {
                $query->setParameter($key, $param);
            }

            $query->execute();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function delete(CityCenter $cityCenter): void
    {
        try {
            $sql = "delete from city_center where id = :id";

            $rsm = new ResultSetMapping();
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('id', $cityCenter->getId());

            $query->execute();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function checkExist($params)
    {
        try {
            $sql = "select * from city_center cc where city = :city and state_or_province = :stateOrProvince limit 1";

            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\CityCenter', 'cc');
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('city', $params['city']);
            $query->setParameter('stateOrProvince', $params['stateOrProvince']);

            return count($query->getResult());
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function findByCriteria(ListingSearchCriteria $criteria)
    {
        try {
            if (!$criteria->city || !$criteria->stateOrProvince) {
                return null;
            }

            $sql = "select * from city_center cc where city = :city and state_or_province = :stateOrProvince limit 1";

            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\CityCenter', 'cc');
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('city', $criteria->city);
            $query->setParameter('stateOrProvince', $criteria->stateOrProvince);

            return $query->getOneOrNullResult();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}