<?php

namespace App\Service\Report;

use App\Entity\AssetAccount;
use App\Entity\DepositTransaction;
use App\Entity\ExpenseAccount;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Entity\RevenueAccount;
use App\Entity\TransferTransaction;
use App\Entity\User;
use App\Entity\WithdrawalTransaction;
use App\Repository\Account\AssetAccountRepository;
use App\Repository\HouseholdUserRepository;
use App\Repository\PeriodicTransaction\PeriodicDepositTransactionRepository;
use App\Repository\PeriodicTransaction\PeriodicTransferTransactionRepository;
use App\Repository\PeriodicTransaction\PeriodicWithdrawalTransactionRepository;
use App\Repository\Transaction\DepositTransactionRepository;
use App\Repository\Transaction\TransferTransactionRepository;
use App\Repository\Transaction\WithdrawalTransactionRepository;
use App\Service\DatatablesService;
use App\Service\MoneyCalculationService;
use Symfony\Component\Security\Core\Security;

class ReportService extends DatatablesService
{
    private AssetAccountRepository $assetAccountRepository;
    private DepositTransactionRepository $depositTransactionRepository;
    private TransferTransactionRepository $transferTransactionRepository;
    private WithdrawalTransactionRepository $withdrawalTransactionRepository;
    private PeriodicDepositTransactionRepository $periodicDepositTransactionRepository;
    private PeriodicWithdrawalTransactionRepository $periodicWithdrawalTransactionRepository;
    private PeriodicTransferTransactionRepository $periodicTransferTransactionRepository;
    private HouseholdUserRepository $householdUserRepository;
    private Security $security;
    private MoneyCalculationService $moneyCalc;

    public function __construct(AssetAccountRepository $assetAccountRepository,
                                DepositTransactionRepository         $depositTransactionRepository,
                                TransferTransactionRepository        $transferTransactionRepository,
                                WithdrawalTransactionRepository      $withdrawalTransactionRepository,
                                PeriodicDepositTransactionRepository $periodicDepositTransactionRepository,
                                PeriodicWithdrawalTransactionRepository $periodicWithdrawalTransactionRepository,
                                PeriodicTransferTransactionRepository $periodicTransferTransactionRepository,
                                HouseholdUserRepository              $householdUserRepository,
                                Security                             $security,
                                MoneyCalculationService $moneyCalculationService)
    {
        $this->assetAccountRepository = $assetAccountRepository;
        $this->depositTransactionRepository = $depositTransactionRepository;
        $this->transferTransactionRepository = $transferTransactionRepository;
        $this->withdrawalTransactionRepository = $withdrawalTransactionRepository;
        $this->periodicDepositTransactionRepository = $periodicDepositTransactionRepository;
        $this->periodicWithdrawalTransactionRepository = $periodicWithdrawalTransactionRepository;
        $this->periodicTransferTransactionRepository = $periodicTransferTransactionRepository;
        $this->householdUserRepository = $householdUserRepository;
        $this->security = $security;
        $this->moneyCalc = $moneyCalculationService;
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
            'deposit' => "0",
            'upcomingDeposit' => "0",
            'withdrawal' => "0",
            'upcomingWithdrawal' => "0",
            'balance' => "0",
            'expectedBalance' => "0",
            'savings' => "0",
            'upcomingSavings' => "0",
            'expectedSavings' => "0",
        ];

        $assetAccounts = $this->getAssetAccounts($household, $currentPeriodStart, $currentPeriodEnd);
        $transactions = $this->getTransactions($household, $currentPeriodStart, $currentPeriodEnd);
        $periodicTransactions = $this->getPeriodicTransactions($household, $currentPeriodStart, $currentPeriodEnd);

        /** @var AssetAccount $assetAccount */
        foreach($assetAccounts as $assetAccount) {
            if($assetAccount->getAccountType() === AssetAccount::TYPE_CURRENT) {
                $data['deposit'] = $this->moneyCalc->add($data['deposit'], $assetAccount->getInitialBalance());
            }
        }

        //TODO: initial Balance von Savings in Report berücksichtigen?

        foreach($transactions as $transaction) {
            switch(true) {
                case $transaction instanceof DepositTransaction:
                    if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                        $data['deposit'] = $this->moneyCalc->add($data['deposit'], $transaction->getAmount());
                    }else {
                        $data['upcomingDeposit'] = $this->moneyCalc->add($data['upcomingDeposit'], $transaction->getAmount());
                    }
                    break;
                case $transaction instanceof WithdrawalTransaction:
                    if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                        $data['withdrawal'] = $this->moneyCalc->add($data['withdrawal'], $transaction->getAmount());
                    }else {
                        $data['upcomingWithdrawal'] = $this->moneyCalc->add($data['upcomingWithdrawal'], $transaction->getAmount());
                    }
                    break;
                case $transaction instanceof TransferTransaction:
                    if($transaction->getSource()->getAccountType() === AssetAccount::TYPE_CURRENT && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_SAVINGS) {
                        if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                            $data['savings'] = $this->moneyCalc->add($data['savings'], $transaction->getAmount());
                        }else {
                            $data['upcomingSavings'] = $this->moneyCalc->add($data['upcomingSavings'], $transaction->getAmount());
                        }
                    }elseif ($transaction->getSource()->getAccountType() === AssetAccount::TYPE_SAVINGS && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_CURRENT) {
                        if($transaction->getBookingDate() <= (new \DateTime())->modify('midnight')) {
                            $data['savings'] = $this->moneyCalc->subtract($data['savings'], $transaction->getAmount());
                        }else {
                            $data['upcomingSavings'] = $this->moneyCalc->subtract($data['upcomingSavings'], $transaction->getAmount());
                        }
                    }
                    break;
            }
        }

        foreach($periodicTransactions as $transaction) {
            switch(true) {
                case $transaction instanceof DepositTransaction:
                    $data['upcomingDeposit'] = $this->moneyCalc->add($data['upcomingDeposit'], $transaction->getAmount());
                    break;
                case $transaction instanceof WithdrawalTransaction:
                    $data['upcomingWithdrawal'] = $this->moneyCalc->add($data['upcomingWithdrawal'], $transaction->getAmount());
                    break;
                case $transaction instanceof TransferTransaction:
                    if($transaction->getSource()->getAccountType() === AssetAccount::TYPE_CURRENT && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_SAVINGS) {
                        $data['upcomingSavings'] = $this->moneyCalc->add($data['upcomingSavings'], $transaction->getAmount());
                    }elseif ($transaction->getSource()->getAccountType() === AssetAccount::TYPE_SAVINGS && $transaction->getDestination()->getAccountType() === AssetAccount::TYPE_CURRENT) {
                        $data['upcomingSavings'] = $this->moneyCalc->subtract($data['upcomingSavings'], $transaction->getAmount());
                    }
                    break;
            }
        }

        $data['balance'] = $this->moneyCalc->subtract($data['deposit'], $data['withdrawal']);
        $data['balance'] = $this->moneyCalc->subtract($data['balance'], $data['savings']);

        $data['expectedBalance'] = $this->moneyCalc->add($data['balance'], $data['upcomingDeposit']);
        $data['expectedBalance'] = $this->moneyCalc->subtract($data['expectedBalance'], $data['upcomingWithdrawal']);
        $data['expectedBalance'] = $this->moneyCalc->subtract($data['expectedBalance'], $data['upcomingSavings']);

        $data['expectedSavings'] = $this->moneyCalc->add($data['savings'], $data['upcomingSavings']);

        /** @var User $user */
        $user = $this->security->getUser();
        $householdUser = $this->householdUserRepository->findOneByUserAndHousehold($user, $household);

        foreach($transactions as $transaction) {
            $data['table'][] = $this->getAsArray($transaction, $householdUser);
        }

        foreach($periodicTransactions as $transaction) {
            $data['table'][] = $this->getAsArray($transaction, $householdUser, true);
        }

        return $data;
    }


    protected function getAsArray($transaction, HouseholdUser $householdUser, bool $isPeriodic = false): array|null
    {
        $result = null;

        if($transaction instanceof DepositTransaction && !$isPeriodic) {
            $result['bookingType'] = "deposit";
        }elseif ($transaction instanceof WithdrawalTransaction && !$isPeriodic) {
            $result['bookingType'] = "withdrawal";
        }elseif ($transaction instanceof TransferTransaction && !$isPeriodic) {
            $result['bookingType'] = "transfer";
        }elseif ($transaction instanceof DepositTransaction && $isPeriodic) {
            $result['bookingType'] = "periodicDeposit";
        }elseif ($transaction instanceof WithdrawalTransaction && $isPeriodic) {
            $result['bookingType'] = "periodicWithdrawal";
        }elseif ($transaction instanceof TransferTransaction && $isPeriodic) {
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
            if(method_exists($transaction, 'getBookingCategory')) {
                $result['bookingCategory'] = $transaction->getBookingCategory()->getName();
            }
            $result['description'] = $transaction->getDescription();
            $result['amount'] = $numberFormatter->formatCurrency($transaction->getAmount(), 'EUR');
            $result['amount_filter'] = $transaction->getAmount();
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

    /**
     * @param Household $household
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     *
     * Get AssetAccounts that have their initial balance date within the specified range.
     */
    public function getAssetAccounts(Household $household, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->assetAccountRepository->findAllByHouseholdAndInitialBalanceDateInRange($household, $startDate, $endDate);
    }


    public function getTransactions(Household $household, \DateTime $startDate, \DateTime $endDate): array
    {
        $tableData = [];

        $tableData = array_merge($tableData, $this->depositTransactionRepository->findAllByHouseholdAndDateRange($household, $startDate, $endDate));
        $tableData = array_merge($tableData, $this->withdrawalTransactionRepository->findAllByHouseholdAndDateRange($household, $startDate, $endDate));

        return array_merge($tableData, $this->transferTransactionRepository->findAllByHouseholdAndDateRange($household, $startDate, $endDate));
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

                    // ensure that we don't miss the end of a month
                    if($row->getBookingDayOfMonth() >
                        cal_days_in_month(CAL_GREGORIAN, intval($bookingDate->format('m')), intval($bookingDate->format('Y')))) {
                        $bookingDate->modify('last day of this month');
                    }else {
                        $bookingDate->setDate($bookingDate->format('Y'), $bookingDate->format('m'), $row->getBookingDayOfMonth());
                    }

                    $transaction = new DepositTransaction();
                    $transaction->setHousehold($row->getHousehold());
                    $transaction->setBookingDate(clone $bookingDate);
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

                    // ensure that we don't miss the end of a month
                    if($row->getBookingDayOfMonth() >
                        cal_days_in_month(CAL_GREGORIAN, intval($bookingDate->format('m')), intval($bookingDate->format('Y')))) {
                        $bookingDate->modify('last day of this month');
                    }else {
                        $bookingDate->setDate($bookingDate->format('Y'), $bookingDate->format('m'), $row->getBookingDayOfMonth());
                    }

                    $transaction = new WithdrawalTransaction();
                    $transaction->setHousehold($row->getHousehold());
                    $transaction->setBookingDate(clone $bookingDate);
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

                    // ensure that we don't miss the end of a month
                    if($row->getBookingDayOfMonth() >
                        cal_days_in_month(CAL_GREGORIAN, intval($bookingDate->format('m')), intval($bookingDate->format('Y')))) {
                        $bookingDate->modify('last day of this month');
                    }else {
                        $bookingDate->setDate($bookingDate->format('Y'), $bookingDate->format('m'), $row->getBookingDayOfMonth());
                    }

                    $transaction = new TransferTransaction();
                    $transaction->setHousehold($row->getHousehold());
                    $transaction->setBookingDate(clone $bookingDate);
                    $transaction->setHouseholdUser($row->getHouseholdUser());
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