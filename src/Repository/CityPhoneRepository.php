<?php

namespace App\Repository;

use App\Entity\CityPhone;
use Complex\Exception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CityPhone|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityPhone|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityPhone[]    findAll()
 * @method CityPhone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityPhoneRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, CityPhone::class);
        $this->entityManager = $entityManager;
    }

    public function createFromCommand($params): void
    {
        try {
            $sql = "insert into city_phone (country, city, state_or_province, phone) values (:country, :city, :state_or_province, :Phone)";

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

    public function create($params): void
    {
        try {
            $sql = "insert into city_phone (country, city, state_or_province, phone) values (:country, :city, :stateOrProvince, :phone)";

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

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(CityPhone $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByListing($city, $state, $country)
    {
        try {
            if (!$city && !$state && !$country) {
                return null;
            }

            $sql = "select * from city_phone cp where country = :country and state_or_province = :stateOrProvince and city = :city limit 1";

            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\CityPhone', 'cp');
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('country', $country);
            $query->setParameter('stateOrProvince', $state);
            $query->setParameter('city', $city);

            return $query->getOneOrNullResult();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function update($params): void
    {
        try {
            $sql = "update city_phone set country = :country, 
                        state_or_province = :stateOrProvince, 
                        city = :city, 
                        phone = :phone
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

    public function delete(CityPhone $cityPhone): void
    {
        try {
            $sql = "delete from city_phone where id = :id";

            $rsm = new ResultSetMapping();
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('id', $cityPhone->getId());

            $query->execute();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function checkExist($params)
    {
        try {
            $sql = "select * from city_phone cp where country = :country and city = :city and state_or_province = :stateOrProvince limit 1";

            $rsm = new ResultSetMappingBuilder($this->entityManager);
            $rsm->addRootEntityFromClassMetadata('App\Entity\CityPhone', 'cp');
            $query = $this->entityManager->createNativeQuery($sql, $rsm);
            $query->setParameter('country', $params['country']);
            $query->setParameter('stateOrProvince', $params['stateOrProvince']);
            $query->setParameter('city', $params['city']);

            return count($query->getResult());
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
