<?php

namespace App\Repository;

use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Household|null find($id, $lockMode = null, $lockVersion = null)
 * @method Household|null findOneBy(array $criteria, array $orderBy = null)
 * @method Household[]    findAll()
 * @method Household[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HouseholdRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Household::class);

        $this->security = $security;
    }

    /**
    * @return Household[] Returns an array of Household objects
    */
    public function findByMember(UserInterface $user)
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.householdUsers', 'householdUsers')
            ->andWhere('householdUsers.user = :user')
            ->setParameter('user', $user)
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByAdmin(UserInterface $user): ?array
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.householdUsers', 'householdUsers')
            ->andWhere('householdUsers.user = :user')
            ->andWhere('householdUsers.isAdmin = :true')
            ->setParameter('user', $user)
            ->setParameter('true', true)
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Household[] Returns an array of Household objects
     */
    public function findAllGranted()
    {
        $households = $this->createQueryBuilder('h')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->execute()
            ;

        return array_filter($households, function (Household $household) {
            return $this->security->isGranted('view', $household);
        });
    }

    /*
    public function findOneBySomeField($value): ?Household
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
