<?php

namespace App\Service\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Supply;
use App\Repository\Supplies\SupplyRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SupplyService extends DatatablesService
{
    private SupplyRepository $supplyRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(SupplyRepository $supplyRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->supplyRepository = $supplyRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getSuppliesAsDatatablesArray(Request $request, Household $household): array
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
            ['name', 'category', 'usageCount', ],
            (array) $request->query->all('columns'),
            (array) $request->query->all('order')
        );

        $result = $this->supplyRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        foreach($result['data'] as $row) {
            /** @var Supply $supply */
            $supply = $row[0];

            $rowData = [
                'id' => $supply->getId(),
                'name' => $supply->getName(),
                'category' => $supply->getCategory() ? $supply->getCategory()->getName() : '',
                'minimumNumber' => $supply->getMinimumNumber(),
                'usageCount' => $row['numUsage'],
                'orderValue' => $row['orderValue'],
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_supply_edit', ['id' => $supply->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getSuppliesAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->supplyRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        foreach($result['data'] as $row) {
            /** @var Supply $supply */
            $supply = $row[0];

            $rowData = [
                'id' => $supply->getId(),
                'text' => $supply->getName(),
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
}