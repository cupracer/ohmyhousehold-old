<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Booking::class);

        $this->security = $security;
    }

    /**
    * @return Booking[] Returns an array of Booking objects
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

        return array_filter($bookings, function (Booking $booking) {
            return $this->security->isGranted('view', $booking);
        });
    }

    /*
    public function findOneBySomeField($value): ?Booking
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
