<?php

namespace App\Repository\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Measure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Measure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Measure[]    findAll()
 * @method Measure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeasureRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Measure::class);

        $this->security = $security;
    }

    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('m');

        $query = $qb->select($qb->expr()->count('m'))
            ->andWhere('m.household = :household')
            ->innerJoin('m.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere('m.name LIKE :search')
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

        $query = $this->createQueryBuilder('m')
            ->andWhere('m.household = :household')
            ->innerJoin('m.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            // TODO: enable searching for more columns (as defined by Datatables)
            $query->andWhere('m.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query->addOrderBy('LOWER(m.name)', $order['dir']);
                    break;
                case "physicalQuantity":
                    $query->addOrderBy('LOWER(m.physicalQuantity)', $order['dir']);
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
     * @return Measure[] Returns an array of Measure objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $measures = $this->createQueryBuilder('m')
            ->andWhere('m.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(m.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($measures, function (Measure $measure) {
            return $this->security->isGranted('view', $measure);
        });
    }

    // /**
    //  * @return Measure[] Returns an array of Measure objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Measure
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
