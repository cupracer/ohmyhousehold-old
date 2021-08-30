<?php

namespace App\Repository\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('p');

        $query = $qb->select($qb->expr()->count('p'))
            ->andWhere('p.household = :household')
            ->innerJoin('p.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->innerJoin('p.supply', 's')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('p.name', ':search'),
                $query->expr()->like('s.name', ':search'),
                $query->expr()->like('p.ean', ':search'),
            ));

            $query->setParameter('search', '%' . $search . '%');
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

        $query = $this->createQueryBuilder('p')
            ->andWhere('p.household = :household')
            ->innerJoin('p.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->innerJoin('p.supply', 's')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->like('p.name', ':search'),
                $query->expr()->like('s.name', ':search'),
                $query->expr()->like('p.ean', ':search'),
            ));

            $query->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query->addOrderBy('LOWER(p.name)', $order['dir']);
                    break;
                case "createdAt":
                    $query->addOrderBy('p.createdAt', $order['dir']);
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
