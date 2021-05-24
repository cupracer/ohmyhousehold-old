<?php

namespace App\Repository;

use App\Entity\AccountHolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountHolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountHolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountHolder[]    findAll()
 * @method AccountHolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountHolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountHolder::class);
    }

    // /**
    //  * @return AccountHolder[] Returns an array of AccountHolder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccountHolder
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
