<?php

namespace App\Service\Account;

use App\Entity\AssetAccount;
use App\Entity\DepositTransaction;
use App\Entity\Household;
use App\Entity\HouseholdUser;
use App\Entity\TransferTransaction;
use App\Entity\User;
use App\Entity\WithdrawalTransaction;
use App\Repository\Account\AssetAccountRepository;
use App\Repository\Transaction\DepositTransactionRepository;
use App\Repository\Transaction\TransferTransactionRepository;
use App\Repository\Transaction\WithdrawalTransactionRepository;
use App\Service\DatatablesService;
use App\Service\MoneyCalculationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class AssetAccountService extends DatatablesService
{
    private AssetAccountRepository $assetAccountRepository;
    private DepositTransactionRepository $depositTransactionRepository;
    private TransferTransactionRepository $transferTransactionRepository;
    private WithdrawalTransactionRepository $withdrawalTransactionRepository;
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;
    private MoneyCalculationService $moneyCalc;

    public function __construct(AssetAccountRepository $assetAccountRepository,
                                DepositTransactionRepository $depositTransactionRepository,
                                TransferTransactionRepository $transferTransactionRepository,
                                WithdrawalTransactionRepository $withdrawalTransactionRepository,
                                UrlGeneratorInterface $urlGenerator,
                                Security $security,
                                MoneyCalculationService $moneyCalculationService)
    {
        $this->assetAccountRepository = $assetAccountRepository;
        $this->depositTransactionRepository = $depositTransactionRepository;
        $this->transferTransactionRepository = $transferTransactionRepository;
        $this->withdrawalTransactionRepository = $withdrawalTransactionRepository;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
        $this->moneyCalc = $moneyCalculationService;
    }

    public function getAssetAccountsAsDatatablesArray(Request $request, Household $household): array
    {
        $draw = $request->query->getInt('draw', 1);
        $start = $request->query->getInt('start');
        $length = $request->query->getInt('length', 10);
        $searchParam = (array) $request->query->get('search');

        if(array_key_exists('value', $searchParam)) {
            $search = $searchParam['value'];
        }else {
            $search = '';
        }

        $orderingData = $this->getOrderingData(
            ['name', 'accountType', 'iban', ],
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        $result = $this->assetAccountRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var User $user */
        $user = $this->security->getUser();
        $numberFormatter = numfmt_create($user->getUserProfile()->getLocale(), \NumberFormatter::CURRENCY);

        /** @var AssetAccount $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'name' => $row->getName(),
                'accountType' => $row->getAccountType(),
                'iban' => $row->getIban(),
                'initialBalance' => $numberFormatter->formatCurrency($row->getInitialBalance(), 'EUR'),
                'balance' => $numberFormatter->formatCurrency($this->getBalance($row), 'EUR'),
                'createdAt' => \IntlDateFormatter::formatObject($row->getCreatedAt()),
                'editLink' => $this->urlGenerator->generate('housekeepingbook_asset_account_edit', ['id' => $row->getId()]),
            ];

            /** @var HouseholdUser $owner */
            foreach($row->getOwners() as $owner) {
                $rowData['owners'][] = $owner->getUser()->getUsername();
            }

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getAssetAccountsAsSelect2Array(Request $request, Household $household): array
    {
        $page = $request->query->getInt('page', 1);
        $length = $request->query->getInt('length', 10);
        $start = $page > 1 ? $page * $length : 0;
        $search = $request->query->get('term', '');

        $orderingData = [
            [
                'name' => 'name',
                'dir' => 'asc',
            ]
        ];

        $result = $this->assetAccountRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var AssetAccount $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'text' => $row->getName(),
            ];

            $tableData[] = $rowData;
        }

        return [
            'results' => $tableData,
            'pagination' => [
                'more' => $start + $length < $result['recordsFiltered'],
            ]
        ];
    }

    public function getBalance(AssetAccount $assetAccount)
    {
        $today = (new \DateTime())->modify('midnight');

        $balance = $assetAccount->getInitialBalance();

        /** @var DepositTransaction $transaction */
        foreach($assetAccount->getDepositTransactions() as $transaction) {
            if($transaction->getBookingDate() <= $today) {
//                $balance += $transaction->getAmount();
                $balance = $this->moneyCalc->add($balance, $transaction->getAmount());
            }
        }
//        foreach($this->depositTransactionRepository->findBy(['destination' => $assetAccount]) as $transaction) {
//            $balance+= $transaction->getAmount();
//        }

        /** @var WithdrawalTransaction $transaction */
        foreach($assetAccount->getWithdrawalTransactions() as $transaction) {
            if($transaction->getBookingDate() <= $today) {
//                $balance -= $transaction->getAmount();
                $balance = $this->moneyCalc->subtract($balance, $transaction->getAmount());
            }
        }
//        foreach($this->withdrawalTransactionRepository->findBy(['source' => $assetAccount]) as $transaction) {
//            $balance-= $transaction->getAmount();
//        }

        /** @var TransferTransaction $transaction */
        foreach($assetAccount->getIncomingTransferTransactions() as $transaction) {
            if($transaction->getBookingDate() <= $today) {
//                $balance += $transaction->getAmount();
                $balance = $this->moneyCalc->add($balance, $transaction->getAmount());
            }
        }
//        foreach($this->transferTransactionRepository->findBy(['destination' => $assetAccount]) as $transaction) {
//            $balance+= $transaction->getAmount();
//        }

        /** @var TransferTransaction $transaction */
        foreach($assetAccount->getOutgoingTransferTransactions() as $transaction) {
            if($transaction->getBookingDate() <= $today) {
//                $balance -= $transaction->getAmount();
                $balance = $this->moneyCalc->subtract($balance, $transaction->getAmount());
            }
        }
//        foreach($this->transferTransactionRepository->findBy(['source' => $assetAccount]) as $transaction) {
//            $balance-= $transaction->getAmount();
//        }

        return $balance;
    }
}