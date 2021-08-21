<?php

namespace App\Service\Account;

use App\Entity\ExpenseAccount;
use App\Entity\Household;
use App\Entity\User;
use App\Repository\Account\ExpenseAccountRepository;
use App\Repository\Transaction\WithdrawalTransactionRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class ExpenseAccountService extends DatatablesService
{
    private ExpenseAccountRepository $expenseAccountRepository;
    private WithdrawalTransactionRepository $withdrawalTransactionRepository;
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;

    public function __construct(ExpenseAccountRepository $expenseAccountRepository,
                                WithdrawalTransactionRepository $withdrawalTransactionRepository,
                                UrlGeneratorInterface $urlGenerator,
                                Security $security)
    {
        $this->expenseAccountRepository = $expenseAccountRepository;
        $this->withdrawalTransactionRepository = $withdrawalTransactionRepository;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }

    public function getExpenseAccountsAsDatatablesArray(Request $request, Household $household): array
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

        $result = $this->expenseAccountRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var User $user */
        $user = $this->security->getUser();
        $numberFormatter = numfmt_create($user->getUserProfile()->getLocale(), \NumberFormatter::CURRENCY);

        /** @var ExpenseAccount $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'name' => $row->getAccountHolder()->getName(),
                'balance' => $numberFormatter->formatCurrency(floatval($this->getBalance($row)), 'EUR'),
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

    public function getExpenseAccountsAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->expenseAccountRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var ExpenseAccount $row */
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

    public function getBalance(ExpenseAccount $expenseAccount)
    {
        $balance = $expenseAccount->getInitialBalance();

        foreach($this->withdrawalTransactionRepository->findBy(['destination' => $expenseAccount]) as $transaction) {
            $balance+= $transaction->getAmount();
        }

        return $balance;
    }
}