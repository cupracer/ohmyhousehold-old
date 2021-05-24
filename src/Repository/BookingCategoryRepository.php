<?php

namespace App\Repository;

use App\Entity\BookingCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BookingCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingCategory[]    findAll()
 * @method BookingCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookingCategory::class);
    }

    // /**
    //  * @return BookingCategory[] Returns an array of BookingCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BookingCategory
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
