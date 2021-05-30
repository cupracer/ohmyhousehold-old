<?php

namespace App\Service;

use App\Entity\AccountHolder;
use App\Entity\Household;
use App\Repository\AccountHolderRepository;
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
        $searchParam = (array) $request->query->get('search');

        if(array_key_exists('value', $searchParam)) {
            $search = $searchParam['value'];
        }else {
            $search = '';
        }

        $orderingData = $this->getOrderingData(
            new AccountHolder(),
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        dump($orderingData);

        $result = $this->accountHolderRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var AccountHolder $row */
        foreach($result['data'] as $row) {
            $rowData = $row->jsonSerialize();

            $rowData['createdAt'] = \IntlDateFormatter::formatObject($rowData['createdAt']);
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
}