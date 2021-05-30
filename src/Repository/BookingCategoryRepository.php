<?php

namespace App\Repository;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @return BookingCategory[] Returns an array of BookingCategory objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $bookingCategories = $this->createQueryBuilder('b')
            ->andWhere('b.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(b.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($bookingCategories, function (BookingCategory $bookingCategory) {
            return $this->security->isGranted('view', $bookingCategory);
        });
    }

    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('b');

        $query = $qb->select($qb->expr()->count('b'))
            ->andWhere('b.household = :household')
            ->innerJoin('b.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere('b.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $query
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @return array
     */
    public function getFilteredDataByHousehold(Household $household, $start, $length, array $orderingData, string $search = '')
    {
        // This method generates an array which is to be used for a Datatables output.
        // For performance reasons, no security voter is used. Filtering is done by the query.
        $result = [
            'recordsTotal' => $this->getCountAllByHouseholdAndUser($household, $this->security->getUser()),
        ];

        // no need to run the same query again if no search term is used.
        $result['recordsFiltered'] = $search ?
            $this->getCountAllByHouseholdAndUser($household, $this->security->getUser(), $search) :
            $result['recordsTotal'];

        $query = $this->createQueryBuilder('b')
            ->andWhere('b.household = :household')
            ->innerJoin('b.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            // TODO: enable searching for more columns (as defined by Datatables)
            $query->andWhere('b.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query->addOrderBy('LOWER(b.name)', $order['dir']);
                    break;
                case "createdAt":
                    $query->addOrderBy('b.createdAt', $order['dir']);
                    break;
            }
        }

        $result['data'] = $query
            ->getQuery()
            ->execute()
        ;

        return $result;
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
