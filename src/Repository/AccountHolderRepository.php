<?php

namespace App\Repository;

use App\Entity\AccountHolder;
use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method AccountHolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountHolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountHolder[]    findAll()
 * @method AccountHolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountHolderRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, AccountHolder::class);

        $this->security = $security;
    }

    /**
     * @return AccountHolder[] Returns an array of DynamicBooking objects
     */
    public function findAllGrantedByHousehold(Household $household)
    {
        $accountHolders = $this->createQueryBuilder('a')
            ->andWhere('a.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(a.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($accountHolders, function (AccountHolder $accountHolder) {
            return $this->security->isGranted('view', $accountHolder);
        });
    }

    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        $qb = $this->createQueryBuilder('a');

        $query = $qb->select($qb->expr()->count('a'))
            ->andWhere('a.household = :household')
            ->innerJoin('a.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
            ->orderBy('LOWER(a.name)', 'ASC')
        ;

        if($search) {
            $query->andWhere('a.name LIKE :search')
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
    public function getAllGrantedByHouseholdData(Household $household, $start, $length, string $search = '')
    {
        $result = [
            'recordsTotal' => $this->getCountAllByHouseholdAndUser($household, $this->security->getUser()),
            'recordsFiltered' => $this->getCountAllByHouseholdAndUser($household, $this->security->getUser(), $search),
        ];

        $query = $this->createQueryBuilder('a')
            ->andWhere('a.household = :household')
            ->innerJoin('a.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->orderBy('LOWER(a.name)', 'ASC')
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            $query->andWhere('a.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $result['data'] = $query
            ->getQuery()
            ->execute()
        ;

        return $result;
    }

    // /**
    //  * @return AccountHolder[] Returns an array of AccountHolder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccountHolder
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
