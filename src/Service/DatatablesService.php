<?php

namespace App\Service;

use App\Entity\AccountHolder;
use App\Entity\Household;
use App\Repository\AccountHolderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DatatablesService
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
        $draw = $request->query->getInt('draw');
        $start = $request->query->getInt('start');
        $length = $request->query->getInt('length');

        $searchParam = (array) $request->query->get('search');
        $search = null;

        if(array_key_exists('value', $searchParam)) {
            $search = $searchParam['value'];
        }

        $result = $this->accountHolderRepository->getAllGrantedByHouseholdData($household, $start, $length, $search);

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