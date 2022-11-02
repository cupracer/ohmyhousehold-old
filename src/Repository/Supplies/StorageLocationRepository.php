<?php

namespace App\Repository\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\StorageLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method StorageLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method StorageLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method StorageLocation[]    findAll()
 * @method StorageLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StorageLocationRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, StorageLocation::class);

        $this->security = $security;
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

        $query = $this->createQueryBuilder('a')
            ->andWhere('a.household = :household')
            ->innerJoin('a.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser());

        if($length > 0) {
            $query
                ->setFirstResult($start)
                ->setMaxResults($length);
        }

        if($search) {
            // TODO: enable searching for more columns (as defined by Datatables)
            $query->andWhere('a.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query->addOrderBy('LOWER(a.name)', $order['dir']);
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
     * @return StorageLocation[] Returns an array of StorageLocation objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $storageLocations = $this->createQueryBuilder('b')
            ->andWhere('b.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(b.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($storageLocations, function (StorageLocation $storageLocation) {
            return $this->security->isGranted('view', $storageLocation);
        });
    }

    /**
     * @return StorageLocation[] Returns an array of StorageLocation objects, ideally just a single one (by $id)
     *
     * TODO: type hint for $id
     */
    public function findGrantedByHouseholdAndId(Household $household, $id): array
    {
        $storageLocations = $this->createQueryBuilder('b')
            ->andWhere('b.household = :household')
            ->andWhere('b.id = :id')
            ->setParameter('household', $household)
            ->setParameter('id', $id)
            ->orderBy('LOWER(b.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($storageLocations, function (StorageLocation $storageLocation) {
            return $this->security->isGranted('view', $storageLocation);
        });
    }


    // /**
    //  * @return StorageLocation[] Returns an array of StorageLocation objects
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
    public function findOneBySomeField($value): ?StorageLocation
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
