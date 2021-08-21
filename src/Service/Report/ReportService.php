<?php

namespace App\Service\Report;

use App\Entity\AssetAccount;
use App\Entity\DepositTransaction;
use App\Entity\ExpenseAccount;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Entity\PeriodicDepositTransaction;
use App\Entity\PeriodicTransferTransaction;
use App\Entity\PeriodicWithdrawalTransaction;
use App\Entity\RevenueAccount;
use App\Entity\TransferTransaction;
use App\Entity\User;
use App\Entity\WithdrawalTransaction;
use App\Repository\HouseholdUserRepository;
use App\Repository\PeriodicTransaction\PeriodicDepositTransactionRepository;
use App\Repository\PeriodicTransaction\PeriodicTransferTransactionRepository;
use App\Repository\PeriodicTransaction\PeriodicWithdrawalTransactionRepository;
use App\Repository\Transaction\DepositTransactionRepository;
use App\Repository\Transaction\TransferTransactionRepository;
use App\Repository\Transaction\WithdrawalTransactionRepository;
use App\Service\DatatablesService;
use Symfony\Component\Security\Core\Security;

class ReportService extends DatatablesService
{
    private DepositTransactionRepository $depositTransactionRepository;
    private TransferTransactionRepository $transferTransactionRepository;
    private WithdrawalTransactionRepository $withdrawalTransactionRepository;
    private PeriodicDepositTransactionRepository $periodicDepositTransactionRepository;
    private PeriodicWithdrawalTransactionRepository $periodicWithdrawalTransactionRepository;
    private PeriodicTransferTransactionRepository $periodicTransferTransactionRepository;
    private HouseholdUserRepository $householdUserRepository;
    private Security $security;

    public function __construct(DepositTransactionRepository         $depositTransactionRepository,
                                TransferTransactionRepository        $transferTransactionRepository,
                                WithdrawalTransactionRepository      $withdrawalTransactionRepository,
                                PeriodicDepositTransactionRepository $periodicDepositTransactionRepository,
                                PeriodicWithdrawalTransactionRepository $periodicWithdrawalTransactionRepository,
                                PeriodicTransferTransactionRepository $periodicTransferTransactionRepository,
                                HouseholdUserRepository              $householdUserRepository,
                                Security                             $security)
    {
        $this->depositTransactionRepository = $depositTransactionRepository;
        $this->transferTransactionRepository = $transferTransactionRepository;
        $this->withdrawalTransactionRepository = $withdrawalTransactionRepository;
        $this->periodicDepositTransactionRepository = $periodicDepositTransactionRepository;
        $this->periodicWithdrawalTransactionRepository = $periodicWithdrawalTransactionRepository;
        $this->periodicTransferTransactionRepository = $periodicTransferTransactionRepository;
        $this->householdUserRepository = $householdUserRepository;
        $this->security = $security;
    }


    public function getDataAsArray(Household $household, int $year, int $month): array
    {
        $requestedDate = \DateTime::createFromFormat('Y-m-d', $year . '-' . $month . '-01');
        //TODO check if data is valid

        $currentPeriodStart = (clone $requestedDate)->modify('first day of this month')->modify('midnight');
        $currentPeriodEnd = (clone $requestedDate)->modify('first day of next month')->modify('midnight')->modify('-1 second');

        $data = [
            'startDate' => $currentPeriodStart,
            'endDate' => $currentPeriodEnd,
            'table' => [],
            'deposit' => 0,
            'upcomingDeposit' => 0,
            'withdrawal' => 0,
            'upcomingWithdrawal' => 0,
            'balance' => 0,
            'expectedBalance' => 0,
            'savings' => 0,
            'upcomingSavings' => 0,
            'expectedSavings' => 0,
        ];

        $transactionsArray = $this->getTransactions($household, $currentPeriodStart, $currentPeriodEnd);
        $periodicTransactionsArray = $this->getPeriodicTransactions($household, $currentPeriodStart, $currentPeriodEnd);

        foreach($transactionsArray as $transaction) {
            switch(true) {
                case $transaction instanceof DepositTransaction:
                    if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                        $data['deposit'] += $transaction->getAmount();
                    }else {
                        $data['upcomingDeposit'] += $transaction->getAmount();
                    }
                    break;
                case $transaction instanceof WithdrawalTransaction:
                    if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                        $data['withdrawal'] += $transaction->getAmount();
                    }else {
                        $data['upcomingWithdrawal'] += $transaction->getAmount();
                    }
                    break;
                case $transaction instanceof TransferTransaction:
                    if($transaction->getSource()->getAccountType() === AssetAccount::TYPE_CURRENT && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_SAVINGS) {
                        if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                            $data['savings'] += $transaction->getAmount();
                        }else {
                            $data['upcomingSavings'] += $transaction->getAmount();
                        }
                    }elseif ($transaction->getSource()->getAccountType() === AssetAccount::TYPE_SAVINGS && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_CURRENT) {
                        if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                            $data['savings'] -= $transaction->getAmount();
                        }else {
                            $data['upcomingSavings'] -= $transaction->getAmount();
                        }
                    }
                    break;
            }
        }

        foreach($periodicTransactionsArray as $transaction) {
            switch(true) {
                case $transaction instanceof PeriodicDepositTransaction:
                    $data['upcomingDeposit'] += $transaction->getAmount();
                    break;
                case $transaction instanceof PeriodicWithdrawalTransaction:
                    $data['upcomingWithdrawal'] += $transaction->getAmount();
                    break;
                case $transaction instanceof PeriodicTransferTransaction:
                    if($transaction->getSource()->getAccountType() === AssetAccount::TYPE_CURRENT && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_SAVINGS) {
                        $data['upcomingSavings'] += $transaction->getAmount();
                    }elseif ($transaction->getSource()->getAccountType() === AssetAccount::TYPE_SAVINGS && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_CURRENT) {
                        $data['upcomingSavings'] -= $transaction->getAmount();
                    }
                    break;
            }
        }

        $data['balance'] = $data['deposit'] + $data['withdrawal'];
        $data['expectedBalance'] = $data['balance'] + $data['upcomingDeposit'] + $data['upcomingWithdrawal'];
        $data['expectedSavings'] = $data['savings'] + $data['upcomingSavings'];

        /** @var User $user */
        $user = $this->security->getUser();
        $householdUser = $this->householdUserRepository->findOneByUserAndHousehold($user, $household);

        foreach(array_merge($transactionsArray, $periodicTransactionsArray) as $transaction) {
            $data['table'][] = $this->getAsArray($transaction, $householdUser);
        }

        return $data;
    }


    protected function getAsArray($transaction, HouseholdUser $householdUser): array|null
    {
        $result = null;

        if($transaction instanceof DepositTransaction) {
            $result['bookingType'] = "deposit";
        }elseif ($transaction instanceof WithdrawalTransaction) {
            $result['bookingType'] = "withdrawal";
        }elseif ($transaction instanceof TransferTransaction) {
            $result['bookingType'] = "transfer";
        }elseif ($transaction instanceof PeriodicDepositTransaction) {
            $result['bookingType'] = "periodicDeposit";
        }elseif ($transaction instanceof PeriodicWithdrawalTransaction) {
            $result['bookingType'] = "periodicWithdrawal";
        }elseif ($transaction instanceof PeriodicTransferTransaction) {
            $result['bookingType'] = "periodicTransfer";
        }else {
            //TODO: do some error handling
            return null;
        }

        $numberFormatter = numfmt_create($householdUser->getUser()->getUserProfile()->getLocale(), \NumberFormatter::CURRENCY);
        $dateFormatter = new \IntlDateFormatter($householdUser->getUser()->getUserProfile()->getLocale(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);

        $result['bookingDate'] = $dateFormatter->format($transaction->getBookingDate());
        $result['bookingDate_obj'] = $transaction->getBookingDate();
        $result['bookingDate_sort'] = $transaction->getBookingDate()->getTimestamp();
        $result['user'] = $transaction->getHouseholdUser()->getUser()->getUsername();
        $result['private'] = $transaction->getPrivate();

        if($this->security->isGranted('view', $transaction)) {
            $result['bookingCategory'] = $transaction->getBookingCategory()->getName();
            $result['description'] = $transaction->getDescription();
            $result['amount'] = $numberFormatter->formatCurrency(floatval($transaction->getAmount()), 'EUR');
            $result['amount_filter'] = floatval($transaction->getAmount());
            $result['hidden'] = false;

            if($transaction->getSource() instanceof RevenueAccount || $transaction->getSource() instanceof ExpenseAccount) {
                $result['source'] = $transaction->getSource()->getAccountHolder()->getName();
            }else {
                $result['source'] = $transaction->getSource()->getName();
            }

            if($transaction->getDestination() instanceof RevenueAccount || $transaction->getDestination() instanceof ExpenseAccount) {
                $result['destination'] = $transaction->getDestination()->getAccountHolder()->getName();
            }else {
                $result['destination'] = $transaction->getDestination()->getName();
            }
        }else {
            $result['bookingCategory'] = 'hidden';
            $result['description'] = 'hidden';
            $result['amount'] = 'hidden';
            $result['amount_filter'] = 'hidden';
            $result['hidden'] = true;
            $result['source'] = 'hidden';
            $result['destination'] = 'hidden';
        }

        return $result;
    }


    public function getTransactions(Household $household, \DateTime $startDate, \DateTime $endDate): array
    {
        $tableData = [];

        $tableData = array_merge($tableData, $this->depositTransactionRepository->findAllByHouseholdAndDateRange($household, $startDate, $endDate));
        $tableData = array_merge($tableData, $this->withdrawalTransactionRepository->findAllByHouseholdAndDateRange($household, $startDate, $endDate));
        $tableData = array_merge($tableData, $this->transferTransactionRepository->findAllByHouseholdAndDateRange($household, $startDate, $endDate));

        return $tableData;
    }


    public function getPeriodicTransactions(Household $household, \DateTime $startDate, \DateTime $endDate): array
    {
        $tableData = [];

        $periodicDepositTransactions = $this->periodicDepositTransactionRepository->findAllByHouseholdAndDateRangeWithoutTransaction($household, $startDate, $endDate);

        foreach ($periodicDepositTransactions as $row) {

            /** @var \DateTime $bookingDate */
            $bookingDate = clone $row->getStartDate();
            /** @var \DateTime $intervalDate */
            $intervalDate = clone $row->getStartDate();
            $intervalDate->modify('+ ' . $row->getBookingPeriodOffset() . ' months');

            if($row->getEndDate()) {
                $loopEndDate = clone $row->getEndDate();
            }else {
                $loopEndDate = clone $endDate;
            }

            $loopEndDate->modify('+ ' . $row->getBookingPeriodOffset() . ' months');

            while($intervalDate <= $loopEndDate) {
                if($intervalDate >= $startDate && $intervalDate <= $endDate) {
                    $transaction = new DepositTransaction();
                    $transaction->setHousehold($row->getHousehold());
                    $transaction->setBookingDate(clone $bookingDate->setDate($bookingDate->format('Y'), $bookingDate->format('m'), $row->getBookingDayOfMonth()));
                    $transaction->setHouseholdUser($row->getHouseholdUser());
                    $transaction->setBookingCategory($row->getBookingCategory());
                    $transaction->setSource($row->getSource());
                    $transaction->setDestination($row->getDestination());
                    $transaction->setDescription($row->getDescription());
                    $transaction->setAmount($row->getAmount());
                    $transaction->setPrivate($row->getPrivate());
                    $transaction->setBookingPeriodOffset($row->getBookingPeriodOffset());

                    $tableData[] = $transaction;
                }

                $bookingDate->modify('+ ' . $row->getBookingInterval() . ' months');
                $intervalDate->modify('+ ' . $row->getBookingInterval() . ' months');
            }
        }

        $periodicWithdrawalTransactions = $this->periodicWithdrawalTransactionRepository->findAllByHouseholdAndDateRangeWithoutTransaction($household, $startDate, $endDate);

        foreach ($periodicWithdrawalTransactions as $row) {
            /** @var \DateTime $bookingDate */
            $bookingDate = clone $row->getStartDate();
            /** @var \DateTime $intervalDate */
            $intervalDate = clone $row->getStartDate();
            $intervalDate->modify('+ ' . $row->getBookingPeriodOffset() . ' months');

            if($row->getEndDate()) {
                $loopEndDate = clone $row->getEndDate();
            }else {
                $loopEndDate = clone $endDate;
            }

            $loopEndDate->modify('+ ' . $row->getBookingPeriodOffset() . ' months');

            while($intervalDate <= $loopEndDate) {
                if($intervalDate >= $startDate && $intervalDate <= $endDate) {

                    $transaction = new WithdrawalTransaction();
                    $transaction->setHousehold($row->getHousehold());
                    $transaction->setBookingDate(clone $bookingDate->setDate($bookingDate->format('Y'), $bookingDate->format('m'), $row->getBookingDayOfMonth()));
                    $transaction->setHouseholdUser($row->getHouseholdUser());
                    $transaction->setBookingCategory($row->getBookingCategory());
                    $transaction->setSource($row->getSource());
                    $transaction->setDestination($row->getDestination());
                    $transaction->setDescription($row->getDescription());
                    $transaction->setAmount($row->getAmount());
                    $transaction->setPrivate($row->getPrivate());
                    $transaction->setBookingPeriodOffset($row->getBookingPeriodOffset());

                    $tableData[] = $transaction;
                }

                $bookingDate->modify('+ ' . $row->getBookingInterval() . ' months');
                $intervalDate->modify('+ ' . $row->getBookingInterval() . ' months');
            }
        }

        $periodicTransferTransactions = $this->periodicTransferTransactionRepository->findAllByHouseholdAndDateRangeWithoutTransaction($household, $startDate, $endDate);

        foreach ($periodicTransferTransactions as $row) {
            /** @var \DateTime $bookingDate */
            $bookingDate = clone $row->getStartDate();
            /** @var \DateTime $intervalDate */
            $intervalDate = clone $row->getStartDate();
            $intervalDate->modify('+ ' . $row->getBookingPeriodOffset() . ' months');

            if($row->getEndDate()) {
                $loopEndDate = clone $row->getEndDate();
            }else {
                $loopEndDate = clone $endDate;
            }

            $loopEndDate->modify('+ ' . $row->getBookingPeriodOffset() . ' months');

            while($intervalDate <= $loopEndDate) {
                if($intervalDate >= $startDate && $intervalDate <= $endDate) {

                    $transaction = new TransferTransaction();
                    $transaction->setHousehold($row->getHousehold());
                    $transaction->setBookingDate(clone $bookingDate->setDate($bookingDate->format('Y'), $bookingDate->format('m'), $row->getBookingDayOfMonth()));
                    $transaction->setHouseholdUser($row->getHouseholdUser());
                    $transaction->setBookingCategory($row->getBookingCategory());
                    $transaction->setSource($row->getSource());
                    $transaction->setDestination($row->getDestination());
                    $transaction->setDescription($row->getDescription());
                    $transaction->setAmount($row->getAmount());
                    $transaction->setPrivate($row->getPrivate());
                    $transaction->setBookingPeriodOffset($row->getBookingPeriodOffset());

                    $tableData[] = $transaction;
                }

                $bookingDate->modify('+ ' . $row->getBookingInterval() . ' months');
                $intervalDate->modify('+ ' . $row->getBookingInterval() . ' months');
            }
        }

        return $tableData;
    }
}