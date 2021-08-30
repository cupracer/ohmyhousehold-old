<?php

namespace App\Repository\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Item::class);

        $this->security = $security;
    }

    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('i');

        $query = $qb->select($qb->expr()->count('i'));

        $query
            ->andWhere('i.household = :household')
            ->innerJoin('i.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->andWhere($query->expr()->isNull('i.withdrawalDate'))
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere('i.name LIKE :search')
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

        $query = $this->createQueryBuilder('i');

        $query
            ->andWhere('i.household = :household')
            ->innerJoin('i.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->andWhere($query->expr()->isNull('i.withdrawalDate'))
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            // TODO: enable searching for more columns (as defined by Datatables)
            $query->andWhere('i.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "purchaseDate":
                    $query->addOrderBy('i.purchaseDate', $order['dir']);
                    break;
                case "product":
                    $query
                        ->innerJoin('i.product', 'p')
                        ->innerJoin('p.supply', 's')
                        ->select('i')
                        ->addSelect('CASE WHEN p.name IS NULL THEN LOWER(s.name) ELSE LOWER(p.name) END AS HIDDEN nameOrderColumn')
                        ->addOrderBy('nameOrderColumn', $order['dir']);
                    break;
                case "bestBeforeDate":
                    $query->addOrderBy('i.bestBeforeDate', $order['dir']);
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
     * @return Item[] Returns an array of Item objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $items = $this->createQueryBuilder('i')
            ->andWhere('i.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(i.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($items, function (Item $item) {
            return $this->security->isGranted('view', $item);
        });
    }

    // /**
    //  * @return Item[] Returns an array of Item objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Item
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
