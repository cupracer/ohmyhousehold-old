<?php

namespace App\Repository\Supplies;

use App\Entity\Supplies\SupplyCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupplyCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupplyCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupplyCategory[]    findAll()
 * @method SupplyCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplyCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupplyCategory::class);
    }

    // /**
    //  * @return SupplyCategory[] Returns an array of SupplyCategory objects
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
    public function findOneBySomeField($value): ?SupplyCategory
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
