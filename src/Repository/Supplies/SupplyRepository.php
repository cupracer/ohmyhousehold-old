<?php

namespace App\Repository\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Supply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Supply|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supply|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supply[]    findAll()
 * @method Supply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupplyRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Supply::class);

        $this->security = $security;
    }

    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('s');

        $query = $qb->select($qb->expr()->count('s'))
            ->andWhere('s.household = :household')
            ->innerJoin('s.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->leftJoin('s.category', 'c')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('s.name', ':search'),
                $query->expr()->like('c.name', ':search'),
            ))
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

        $query = $this->createQueryBuilder('s')
            ->andWhere('s.household = :household')
            ->innerJoin('s.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->leftJoin('s.category', 'c')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser());

        if($length > 0) {
            $query
                ->setFirstResult($start)
                ->setMaxResults($length);
        }

        if($search) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('s.name', ':search'),
                $query->expr()->like('c.name', ':search'),
            ))
                ->setParameter('search', '%' . $search . '%');
        }

        // count supply items
        $query
            ->leftJoin('s.products', 'p')
            ->leftJoin('p.items',
                'i',
                Join::WITH,
                $query->expr()->isNull('i.withdrawalDate')
            )
            ->addSelect('COUNT(i) AS numUsage')
        ->groupBy('s.id');


        // set order value
        $query
            ->addSelect('CASE 
                WHEN s.minimumNumber IS NULL AND COUNT(i) = 0 THEN 2
                WHEN s.minimumNumber IS NULL AND COUNT(i) > 0 THEN 1
                WHEN s.minimumNumber >= 0 AND COUNT(i) = 0 THEN 0
                WHEN s.minimumNumber >= 0 AND COUNT(i) > 0 AND COUNT(i) < s.minimumNumber THEN -1
                WHEN s.minimumNumber > 0 AND COUNT(i) >= s.minimumNumber THEN -2
                ELSE -2 END AS orderValue');

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query->addOrderBy('LOWER(s.name)', $order['dir']);
                    break;
                case "category":
                    $query->addOrderBy('LOWER(c.name)', $order['dir']);
                    break;
                case "usageCount":
                    $query
                        ->addOrderBy('orderValue', $order['dir'])
                        ->addOrderBy('LOWER(s.name)', 'ASC');
                    break;
            }
        }

        $result['data'] = $query
            ->getQuery()
            ->execute()
        ;

        return $result;
    }

    /**
     * @return Supply[] Returns an array of Supply objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $supplies = $this->createQueryBuilder('s')
            ->andWhere('s.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(s.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($supplies, function (Supply $supply) {
            return $this->security->isGranted('view', $supply);
        });
    }

    /**
     * @return Supply[] Returns an array of Supply objects, ideally just a single one (by $id)
     *
     * TODO: type hint for $id
     */
    public function findGrantedByHouseholdAndId(Household $household, $id): array
    {
        $supplies = $this->createQueryBuilder('s')
            ->andWhere('s.household = :household')
            ->andWhere('s.id = :id')
            ->setParameter('household', $household)
            ->setParameter('id', $id)
            ->orderBy('LOWER(s.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($supplies, function (Supply $supply) {
            return $this->security->isGranted('view', $supply);
        });
    }

    /**
     * @return Supply[] Returns an array of Supply objects
     */
    public function findAllRunningLowSuppliesGrantedByHousehold(Household $household): array
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(s.name)', 'ASC');

        // count supply items
        $qb
            ->leftJoin('s.products', 'p')
            ->leftJoin('p.items',
                'i',
                Join::WITH,
                $qb->expr()->isNull('i.withdrawalDate')
            )
            ->addSelect('COUNT(i) AS numUsage')
            ->groupBy('s.id')
          ;

        $qb
            ->andWhere('s.minimumNumber > 0')
            ->having('COUNT(i) < s.minimumNumber');

        $supplies = $qb
            ->getQuery()
            ->execute()
        ;

        return array_filter($supplies, function (array $supplyData) {
            /** @var Supply $supply */
            $supply = $supplyData[0];
            return $this->security->isGranted('view', $supply);
        });
    }

    // /**
    //  * @return Supply[] Returns an array of Supply objects
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
    public function findOneBySomeField($value): ?Supply
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
