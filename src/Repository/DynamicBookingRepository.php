<?php

namespace App\Repository;

use App\Entity\DynamicBooking;
use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method DynamicBooking|null find($id, $lockMode = null, $lockVersion = null)
 * @method DynamicBooking|null findOneBy(array $criteria, array $orderBy = null)
 * @method DynamicBooking[]    findAll()
 * @method DynamicBooking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DynamicBookingRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, DynamicBooking::class);

        $this->security = $security;
    }

    /**
    * @return DynamicBooking[] Returns an array of DynamicBooking objects
    */
    public function findAllGrantedByHousehold(Household $household)
    {
        $bookings = $this->createQueryBuilder('b')
            ->andWhere('b.household = :household')
            ->setParameter('household', $household)
            ->orderBy('b.bookingDate', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($bookings, function (DynamicBooking $booking) {
            return $this->security->isGranted('view', $booking);
        });
    }

    /*
    public function findOneBySomeField($value): ?DynamicBooking
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
