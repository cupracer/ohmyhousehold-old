<?php

namespace App\Service;

use App\Entity\AccountHolder;
use App\Entity\Household;
use App\Repository\AccountHolderRepository;
use IntlDateFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountHolderService extends DatatablesService
{
    private AccountHolderRepository $accountHolderRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(AccountHolderRepository $accountHolderRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->accountHolderRepository = $accountHolderRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getAccountHoldersAsDatatablesArray(Request $request, Household $household): array
    {
        $draw = $request->query->getInt('draw', 1);
        $start = $request->query->getInt('start');
        $length = $request->query->getInt('length', 10);
        $searchParam = (array) $request->query->all('search');

        if(array_key_exists('value', $searchParam)) {
            $search = $searchParam['value'];
        }else {
            $search = '';
        }

        $orderingData = $this->getOrderingData(
            ['name', 'createdAt', ],
            (array) $request->query->all('columns'),
            (array) $request->query->all('order')
        );

        $result = $this->accountHolderRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var AccountHolder $row */
        foreach($result['data'] as $row) {
            $rowData = $row->jsonSerialize();
            $rowData['usageCount'] = $this->getUsageCount($row);
            $rowData['createdAt'] = IntlDateFormatter::formatObject($rowData['createdAt']);
            $rowData['editLink'] = $this->urlGenerator->generate(
                'housekeepingbook_accountholder_edit', ['id' => $row->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getAccountHoldersAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->accountHolderRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var AccountHolder $row */
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

    /**
     * @param AccountHolder $accountHolder
     * @return int
     */
    protected function getUsageCount(AccountHolder $accountHolder): int {
        $count = 0;

        $count+= count($accountHolder->getExpenseAccounts());
        $count+= count($accountHolder->getRevenueAccounts());

        return $count;
    }
}