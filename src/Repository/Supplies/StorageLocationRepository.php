<?php

namespace App\Repository\Supplies;

use App\Entity\Supplies\StorageLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StorageLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageLocation[]    findAll()
 * @method StorageLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StorageLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StorageLocation::class);
    }

    // /**
    //  * @return StorageLocation[] Returns an array of StorageLocation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StorageLocation
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
