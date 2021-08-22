<?php

namespace App\Service\Account;

use App\Entity\RevenueAccount;
use App\Entity\Household;
use App\Entity\User;
use App\Repository\Transaction\DepositTransactionRepository;
use App\Repository\Account\RevenueAccountRepository;
use App\Service\DatatablesService;
use App\Service\MoneyCalculationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class RevenueAccountService extends DatatablesService
{
    private RevenueAccountRepository $revenueAccountRepository;
    private DepositTransactionRepository $depositTransactionRepository;
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;
    private MoneyCalculationService $moneyCalc;

    public function __construct(RevenueAccountRepository $revenueAccountRepository,
                                DepositTransactionRepository $depositTransactionRepository,
                                UrlGeneratorInterface $urlGenerator,
                                Security $security,
                                MoneyCalculationService $moneyCalculationService)
    {
        $this->revenueAccountRepository = $revenueAccountRepository;
        $this->depositTransactionRepository = $depositTransactionRepository;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
        $this->moneyCalc = $moneyCalculationService;
    }

    public function getRevenueAccountsAsDatatablesArray(Request $request, Household $household): array
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
            ['name', 'iban', ],
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        $result = $this->revenueAccountRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var User $user */
        $user = $this->security->getUser();
        $numberFormatter = numfmt_create($user->getUserProfile()->getLocale(), \NumberFormatter::CURRENCY);

        /** @var RevenueAccount $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'name' => $row->getAccountHolder()->getName(),
                'balance' => $numberFormatter->formatCurrency($this->getBalance($row), 'EUR'),
                'createdAt' => \IntlDateFormatter::formatObject($row->getCreatedAt()),
                'editLink' => $this->urlGenerator->generate('housekeepingbook_asset_account_edit', ['id' => $row->getId()]),
            ];

//            /** @var HouseholdUser $owner */
//            foreach($row->getOwners() as $owner) {
//                $rowData['owners'][] = $owner->getUser()->getUsername();
//            }

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getRevenueAccountsAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->revenueAccountRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var RevenueAccount $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'text' => $row->getAccountHolder()->getName(),
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

    public function getBalance(RevenueAccount $revenueAccount)
    {
        $balance = $revenueAccount->getInitialBalance();

        foreach($this->depositTransactionRepository->findBy(['source' => $revenueAccount]) as $transaction) {
            $balance = $this->moneyCalc->subtract($balance, $transaction->getAmount());
        }

        return $balance;
    }
}