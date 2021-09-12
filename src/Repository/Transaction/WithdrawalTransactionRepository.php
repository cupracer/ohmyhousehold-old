<?php

namespace App\Repository\Transaction;

use App\Entity\WithdrawalTransaction;
use App\Entity\Household;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method WithdrawalTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method WithdrawalTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method WithdrawalTransaction[]    findAll()
 * @method WithdrawalTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WithdrawalTransactionRepository extends ServiceEntityRepository
{
    private Security $security;
    
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, WithdrawalTransaction::class);

        $this->security = $security;
    }

    /**
     * @return WithdrawalTransaction[] Returns an array of WithdrawalTransaction objects
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

        return array_filter($accounts, function (WithdrawalTransaction $transaction) {
            return $this->security->isGranted('view', $transaction);
        });
    }


    /**
     * @return WithdrawalTransaction[] Returns an array of WithdrawalTransaction objects
     */
    public function findAllByHouseholdAndDateRange(Household $household, DateTime $startDate, DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->andWhere('a.household = :household')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->gte("DATE_ADD(a.bookingDate, a.bookingPeriodOffset, 'MONTH')", ':startDate'),
                    $qb->expr()->lte("DATE_ADD(a.bookingDate, a.bookingPeriodOffset, 'MONTH')", ':endDate'),
                ),
            )
            ->setParameter('household', $household)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('LOWER(a.bookingDate)', 'ASC')
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
            ->innerJoin('t.source', 's')
            ->innerJoin('t.destination', 'd')
            ->innerJoin('d.accountHolder', 'dah')
            ->innerJoin('t.bookingCategory', 'bc')
            ->innerJoin('hhu.user', 'u')
            ->innerJoin('u.userProfile', 'up')
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
            $query->andWhere('t.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        foreach($orderingData as $order) {
            switch ($order['name']) {
                case "bookingDate":
                    $query->addOrderBy('t.bookingDate', $order['dir']);
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
