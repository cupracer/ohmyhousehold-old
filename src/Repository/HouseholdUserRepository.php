<?php

namespace App\Repository;

use App\Entity\Household;
use App\Entity\HouseholdUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method HouseholdUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method HouseholdUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method HouseholdUser[]    findAll()
 * @method HouseholdUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HouseholdUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HouseholdUser::class);
    }

    // /**
    //  * @return HouseholdUser[] Returns an array of HouseholdUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneByUserAndHousehold(UserInterface $user, Household $household): ?HouseholdUser
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.user = :user')
            ->andWhere('h.household = :household')
            ->setParameter('user', $user)
            ->setParameter('household', $household)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
