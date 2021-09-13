<?php

namespace App\Repository\Account;

use App\Entity\AssetAccount;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method AssetAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetAccount[]    findAll()
 * @method AssetAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetAccountRepository extends ServiceEntityRepository
{
    private Security $security;
    
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, AssetAccount::class);

        $this->security = $security;
    }

    /**
     * @return AssetAccount[] Returns an array of AssetAccount objects
     */
    public function findAllViewableByHousehold(Household $household): array
    {
        $accounts = $this->createQueryBuilder('a')
            ->andWhere('a.household = :household')
            ->setParameter('household', $household)
            ->orderBy('LOWER(a.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($accounts, function (AssetAccount $account) {
            return $this->security->isGranted('view', $account);
        });
    }

    /**
     * @return AssetAccount[] Returns an array of AssetAccount objects
     */
    public function findAllOwnedAssetAccountsByHousehold(Household $household, HouseholdUser $householdUser, bool $excludeCurrent = false, bool $excludeSavings = false, bool $excludePrepaid = false, $excludePortfolio = false): array
    {
        $qb = $this->createQueryBuilder('a');

        $qb
            ->leftJoin('a.household', 'hh')
            ->leftJoin('hh.householdUsers', 'hhu')
            ->andWhere('a.household = :household')
//            ->andWhere($qb->expr()->orX(
//                $qb->expr()->andX(
//                    $qb->expr()->eq('hhu.isAdmin', 'true'),
//                    $qb->expr()->eq('hhu', ':householdUser'),
//                ),
//                $qb->expr()->isMemberOf(':householdUser', 'a.owners')
//            ))
            ->andWhere($qb->expr()->isMemberOf(':householdUser', 'a.owners'))
            ->setParameter('household', $household)
            ->setParameter('householdUser', $householdUser)
            ->orderBy('LOWER(a.name)', 'ASC');

        if($excludeCurrent) {
            $qb
                ->andWhere($qb->expr()->neq('a.accountType', ':currentAccount'))
                ->setParameter('currentAccount', AssetAccount::TYPE_CURRENT);
        }

        if($excludeSavings) {
            $qb
                ->andWhere($qb->expr()->neq('a.accountType', ':savingsAccount'))
                ->setParameter('savingsAccount', AssetAccount::TYPE_SAVINGS);
        }

        if($excludePrepaid) {
            $qb
                ->andWhere($qb->expr()->neq('a.accountType', ':prepaidAccount'))
                ->setParameter('prepaidAccount', AssetAccount::TYPE_PREPAID);
        }

        if($excludePortfolio) {
            $qb
                ->andWhere($qb->expr()->neq('a.accountType', ':portfolioAccount'))
                ->setParameter('portfolioAccount', AssetAccount::TYPE_PORTFOLIO);
        }

        return $qb
            ->getQuery()
            ->execute()
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
                case "accountType":
                    $query->addOrderBy('LOWER(a.accountType)', $order['dir']);
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


    /**
     * @return AssetAccount[] Returns an array of AssetAccount objects
     */
    public function findAllByHouseholdAndInitialBalanceDateInRange(Household $household, DateTime $startDate, DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('a');

        $accounts = $this->createQueryBuilder('a')
            ->andWhere('a.household = :household')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->gte("a.initialBalanceDate", ':startDate'),
                    $qb->expr()->lte("a.initialBalanceDate", ':endDate'),
                ),
            )
            ->setParameter('household', $household)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('LOWER(a.name)', 'ASC')
            ->getQuery()
            ->execute()
        ;

        return array_filter($accounts, function (AssetAccount $account) {
            return $this->security->isGranted('view', $account);
        });
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
