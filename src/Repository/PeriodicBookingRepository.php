<?php

namespace App\Repository;

use App\Entity\Household;
use App\Entity\PeriodicBooking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method PeriodicBooking|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeriodicBooking|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeriodicBooking[]    findAll()
 * @method PeriodicBooking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodicBookingRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, PeriodicBooking::class);

        $this->security = $security;
    }

    /**
     * @return PeriodicBooking[] Returns an array of PeriodicBooking objects
     */
    public function findAllGrantedByHousehold(Household $household)
    {
        $periodicBookings = $this->createQueryBuilder('p')
            ->andWhere('p.household = :household')
            ->setParameter('household', $household)
            ->orderBy('p.startDate', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($periodicBookings, function (PeriodicBooking $periodicBooking) {
            return $this->security->isGranted('view', $periodicBooking);
        });
    }

    // /**
    //  * @return PeriodicBooking[] Returns an array of PeriodicBooking objects
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
    public function findOneBySomeField($value): ?PeriodicBooking
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
