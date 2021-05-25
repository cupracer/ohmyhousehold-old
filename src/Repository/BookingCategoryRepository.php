<?php

namespace App\Repository;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method BookingCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookingCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookingCategory[]    findAll()
 * @method BookingCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingCategoryRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, BookingCategory::class);

        $this->security = $security;
    }

    /**
     * @return AccountHolder[] Returns an array of DynamicBooking objects
     */
    public function findAllGrantedByHousehold(Household $household)
    {
        $bookingCategories = $this->createQueryBuilder('b')
            ->andWhere('b.household = :household')
            ->setParameter('household', $household)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($bookingCategories, function (BookingCategory $bookingCategory) {
            return $this->security->isGranted('view', $bookingCategory);
        });
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
