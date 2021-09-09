<?php

namespace App\Repository\Account;

use App\Entity\AccountHolder;
use App\Entity\RevenueAccount;
use App\Entity\Household;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method RevenueAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method RevenueAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method RevenueAccount[]    findAll()
 * @method RevenueAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RevenueAccountRepository extends ServiceEntityRepository
{
    private Security $security;
    
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, RevenueAccount::class);

        $this->security = $security;
    }

    /**
     * @return RevenueAccount[] Returns an array of RevenueAccount objects
     */
    public function findAllGrantedByHousehold(Household $household): array
    {
        $accounts = $this->createQueryBuilder('a')
            ->andWhere('a.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(a.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($accounts, function (RevenueAccount $account) {
            return $this->security->isGranted('view', $account);
        });
    }

    /**
     * @param Household $household
     * @param AccountHolder $accountHolder
     * @return RevenueAccount|null Returns an RevenueAccount object
     * @throws NonUniqueResultException
     */
    public function findOneByHouseholdAndAccountHolder(Household $household, AccountHolder $accountHolder): ?RevenueAccount
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.accountHolder', 'ah')
            ->andWhere('a.household = :household')
            ->andWhere('ah = :accountHolder')
            ->setParameter('household', $household)
            ->setParameter('accountHolder', $accountHolder)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('a');

        $query = $qb->select($qb->expr()->count('a'))
            ->andWhere('a.household = :household')
            ->innerJoin('a.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
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
            ->innerJoin('a.accountHolder', 'ah')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            // TODO: enable searching for more columns (as defined by Datatables)
            $query->andWhere('a.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "name":
                    $query->addOrderBy('LOWER(ah.name)', $order['dir']);
                    break;
                case "iban":
                    $query->addOrderBy('LOWER(a.iban)', $order['dir']);
                    break;
                case "createdAt":
                    $query->addOrderBy('a.createdAt', $order['dir']);
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
    //  * @return Account[] Returns an array of Account objects
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
    public function findOneBySomeField($value): ?Account
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
