<?php

namespace App\Repository\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Product::class);

        $this->security = $security;
    }

    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, bool $inUseOnly, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $query = $this->createQueryBuilder('p');

        $query = $query->select($query->expr()->count('p'))
            ->andWhere('p.household = :household')
            ->innerJoin('p.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->innerJoin('p.supply', 's')
            ->innerJoin('p.brand', 'b')
            ->innerJoin('s.category', 'c')
            ->leftJoin('p.packaging', 'pkg')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('p.name', ':search'),
                $query->expr()->like('s.name', ':search'),
                $query->expr()->like('b.name', ':search'),
                $query->expr()->like('p.ean', ':search'),
                $query->expr()->like('c.name', ':search'),
                $query->expr()->like('pkg.name', ':search'),
            ));

            $query->setParameter('search', '%' . $search . '%');
        }

        if($inUseOnly) {
            $query
                ->leftJoin('p.items',
                    'i',
                    Join::WITH,
                    $query->expr()->isNull('i.withdrawalDate')
                )
                ->andWhere($query->expr()->isNotNull('i'))
                ;
        }

        return $query
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @return array
     */
    public function getFilteredDataByHousehold(Household $household, $start, $length, array $orderingData, bool $inUseOnly, string $search = '')
    {
        // This method generates an array which is to be used for a Datatables output.
        // For performance reasons, no security voter is used. Filtering is done by the query.
        $result = [
            'recordsTotal' => $this->getCountAllByHouseholdAndUser($household, $this->security->getUser(), $inUseOnly),
        ];

        // no need to run the same query again if no search term is used.
        $result['recordsFiltered'] = $search ?
            $this->getCountAllByHouseholdAndUser($household, $this->security->getUser(), $inUseOnly, $search) :
            $result['recordsTotal'];

        $query = $this->createQueryBuilder('p')
            ->andWhere('p.household = :household')
            ->innerJoin('p.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->innerJoin('p.supply', 's')
            ->innerJoin('p.brand', 'b')
            ->innerJoin('s.category', 'c')
            ->leftJoin('p.packaging', 'pkg')
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
                $query->expr()->like('p.name', ':search'),
                $query->expr()->like('s.name', ':search'),
                $query->expr()->like('b.name', ':search'),
                $query->expr()->like('p.ean', ':search'),
                $query->expr()->like('c.name', ':search'),
                $query->expr()->like('pkg.name', ':search'),
            ));

            $query->setParameter('search', '%' . $search . '%');
        }

        // count supply items
        $query
            ->leftJoin('p.items',
                'i',
                Join::WITH,
                $query->expr()->isNull('i.withdrawalDate')
            )
            ->addSelect('COUNT(i) AS numUsage')
            ->groupBy('p.id');

        // set order value
        $query
            ->addSelect('CASE 
                WHEN p.minimumNumber IS NULL AND COUNT(i) = 0 THEN 2
                WHEN p.minimumNumber IS NULL AND COUNT(i) > 0 THEN 1
                WHEN p.minimumNumber >= 0 AND COUNT(i) = 0 THEN 0
                WHEN p.minimumNumber >= 0 AND COUNT(i) > 0 AND COUNT(i) < p.minimumNumber THEN -1
                WHEN p.minimumNumber > 0 AND COUNT(i) >= p.minimumNumber THEN -2
                ELSE -2 END AS orderValue');

        if($inUseOnly) {
            $query->andWhere($query->expr()->isNotNull('i'));
        }

        // add nameSortColumn (use supply name if product name is empty)
        $query->addSelect('CASE WHEN p.name IS NULL THEN LOWER(s.name) ELSE LOWER(p.name) END AS HIDDEN nameSortColumn');

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query
                        ->addOrderBy('nameSortColumn', $order['dir']);
                    break;
                case "brand":
                    $query->addOrderBy('LOWER(b.name)', $order['dir']);
                    break;
                case "ean":
                    $query->addOrderBy('ABS(p.ean)', $order['dir']);
                    break;
                case "category":
                    $query->addOrderBy('LOWER(c.name)', $order['dir']);
                    break;
                case "packaging":
                    $query->addOrderBy('LOWER(pkg.name)', $order['dir']);
                    break;
                case "usageCount":
                    $query
                        ->addOrderBy('orderValue', $order['dir'])
                        ->addOrderBy('nameSortColumn', 'ASC');
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
     * @return Product[] Returns an array of Product objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $products = $this->createQueryBuilder('p')
            ->andWhere('p.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(p.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($products, function (Product $product) {
            return $this->security->isGranted('view', $product);
        });
    }

    /**
     * @return Product[] Returns an array of Product objects, ideally just a single one (by $id)
     *
     * TODO: type hint for $id
     */
    public function findGrantedByHouseholdAndId(Household $household, $id): array
    {
        $products = $this->createQueryBuilder('p')
            ->andWhere('p.household = :household')
            ->andWhere('p.id = :id')
            ->setParameter('household', $household)
            ->setParameter('id', $id)
            ->orderBy('LOWER(p.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($products, function (Product $product) {
            return $this->security->isGranted('view', $product);
        });
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
