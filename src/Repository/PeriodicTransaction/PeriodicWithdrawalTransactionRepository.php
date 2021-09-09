<?php

namespace App\Repository\PeriodicTransaction;

use App\Entity\PeriodicWithdrawalTransaction;
use App\Entity\Household;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method PeriodicWithdrawalTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeriodicWithdrawalTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeriodicWithdrawalTransaction[]    findAll()
 * @method PeriodicWithdrawalTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodicWithdrawalTransactionRepository extends ServiceEntityRepository
{
    private Security $security;
    
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, PeriodicWithdrawalTransaction::class);

        $this->security = $security;
    }

    /**
     * @return PeriodicWithdrawalTransaction[] Returns an array of PeriodicWithdrawalTransaction objects
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

        return array_filter($accounts, function (PeriodicWithdrawalTransaction $transaction) {
            return $this->security->isGranted('view', $transaction);
        });
    }


    /**
     * @return PeriodicWithdrawalTransaction[] Returns an array of PeriodicWithdrawalTransaction objects
     */
    public function findAllByHouseholdAndDateRange(Household $household, DateTime $startDate, DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->andWhere('a.household = :household')
            ->andWhere(
                $qb->expr()->lte("DATE_ADD(a.startDate, a.bookingPeriodOffset, 'MONTH')", ':endDate'),
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('a.endDate'),
                    $qb->expr()->gte("DATE_ADD(a.endDate, a.bookingPeriodOffset, 'MONTH')", ':startDate'),
                ),
            )
            ->setParameter('household', $household)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->execute()
        ;
    }


    /**
     * @return PeriodicWithdrawalTransaction[] Returns an array of PeriodicWithdrawalTransaction objects
     */
    public function findAllGrantedByHouseholdAndDateRange(Household $household, DateTime $startDate, DateTime $endDate): array
    {
        return array_filter($this->findAllByHouseholdAndDateRange($household, $startDate, $endDate), function (PeriodicWithdrawalTransaction $transaction) {
            return $this->security->isGranted('view', $transaction);
        });
    }


    /**
     * @return PeriodicWithdrawalTransaction[] Returns an array of PeriodicWithdrawalTransaction objects
     */
    public function findAllByHouseholdAndDateRangeWithoutTransaction(Household $household, DateTime $startDate, DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('a');

        $rows = $qb
            ->andWhere('a.household = :household')
            ->andWhere(
                $qb->expr()->lte("DATE_ADD(a.startDate, a.bookingPeriodOffset, 'MONTH')", ':endDate'),
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('a.endDate'),
                    $qb->expr()->gte("DATE_ADD(a.endDate, a.bookingPeriodOffset, 'MONTH')", ':startDate'),
                ),
            )
            ->leftJoin(
                'a.withdrawalTransactions',
                't',
                Join::WITH,
                "DATE_ADD(t.bookingDate, t.bookingPeriodOffset, 'MONTH') BETWEEN :startDate AND :endDate"
            )
//            ->andWhere('a.withdrawalTransactions IS EMPTY')
//            ->having($qb->expr()->eq('COUNT(t)', 0))
            ->andWhere($qb->expr()->isNull('t'))
            ->setParameter('household', $household)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->execute()
            ;

        // TODO: the result can contain NULL items, so we filter them out here:
        return array_filter($rows, function (?PeriodicWithdrawalTransaction $transaction) {
            return $transaction ?: false;
        });
    }


    /**
     * @return PeriodicWithdrawalTransaction[] Returns an array of PeriodicWithdrawalTransaction objects
     */
    public function findAllGrantedByHouseholdAndDateRangeWithoutTransaction(Household $household, DateTime $startDate, DateTime $endDate): array
    {
        $transactions = $this->findAllByHouseholdAndDateRangeWithoutTransaction($household, $startDate, $endDate);
        return array_filter($transactions, function (?PeriodicWithdrawalTransaction $transaction) {
            if($transaction) {
                return $this->security->isGranted('view', $transaction);
            }else {
                return false;
            }
        });
    }


    /**
     * @return integer
     */
    public function getCountAllByHouseholdAndUser(Household $household, UserInterface $user, string $search = '')
    {
        // For performance reasons, no security voter is used. Filtering is done by the query.

        $qb = $this->createQueryBuilder('t');

        $query = $qb->select($qb->expr()->count('t'))
            ->andWhere('t.household = :household')
            ->innerJoin('t.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $user)
        ;

        if($search) {
            $query->andWhere('t.name LIKE :search')
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

        $query = $this->createQueryBuilder('t')
            ->andWhere('t.household = :household')
            ->innerJoin('t.household', 'hh')
            ->innerJoin('hh.householdUsers', 'hhu')
            ->innerJoin('hhu.user', 'u')
            ->innerJoin('t.source', 's')
            ->innerJoin('t.destination', 'd')
            ->innerJoin('d.accountHolder', 'dah')
            ->innerJoin('t.bookingCategory', 'bc')
            ->innerJoin('u.userProfile', 'up')
            ->andWhere('hhu.user = :user')
            ->setParameter('household', $household)
            ->setParameter('user', $this->security->getUser())
            ->setFirstResult($start)
            ->setMaxResults($length);

        if($search) {
            // TODO: enable searching for more columns (as defined by Datatables)
            $query->andWhere('t.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "startDate":
                    $query->addOrderBy('t.startDate', $order['dir']);
                    break;
                case "endDate":
                    $query->addOrderBy('t.endDate', $order['dir']);
                    break;
                case "bookingInterval":
                    $query->addOrderBy('t.bookingInterval', $order['dir']);
                    break;
                case "bookingDayOfMonth":
                    $query->addOrderBy('t.bookingDayOfMonth', $order['dir']);
                    break;
                case "user":
                    $query->addOrderBy('LOWER(up.forenames)', $order['dir']);
                    break;
                case "bookingCategory":
                    $query->addOrderBy('LOWER(bc.name)', $order['dir']);
                    break;
                case "source":
                    $query->addOrderBy('LOWER(s.name)', $order['dir']);
                    break;
                case "destination":
                    $query->addOrderBy('LOWER(dah.name)', $order['dir']);
                    break;
                case "description":
                    $query->addOrderBy('LOWER(t.description)', $order['dir']);
                    break;
                case "amount":
                    $query->addOrderBy('t.amount', $order['dir']);
                    break;
                case "createdAt":
                    $query->addOrderBy('t.createdAt', $order['dir']);
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
