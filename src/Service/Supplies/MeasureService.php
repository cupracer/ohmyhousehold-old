<?php

namespace App\Service\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Measure;
use App\Repository\Supplies\MeasureRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MeasureService extends DatatablesService
{
    private MeasureRepository $measureRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MeasureRepository $measureRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->measureRepository = $measureRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getMeasuresAsDatatablesArray(Request $request, Household $household): array
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
            ['name', 'physicalQuantity', ],
            (array) $request->query->all('columns'),
            (array) $request->query->all('order')
        );

        $result = $this->measureRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var Measure $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'physicalQuantity' => $row->getPhysicalQuantity(),
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_measure_edit', ['id' => $row->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }
//
//    public function getAccountHoldersAsSelect2Array(Request $request, Household $household): array
//    {
//        $page = $request->query->getInt('page', 1);
//        $length = $request->query->getInt('length', 10);
//        $start = $page > 1 ? $page * $length : 0;
//        $search = $request->query->get('term', '');
//
//        $orderingData = [
//            [
//                'name' => 'name',
//                'dir' => 'asc',
//            ]
//        ];
//
//        $result = $this->accountHolderRepository->getFilteredDataByHousehold(
//            $household, $start, $length, $orderingData, $search);
//
//        $tableData = [];
//
//        /** @var AccountHolder $row */
//        foreach($result['data'] as $row) {
//            $rowData = [
//                'id' => $row->getId(),
//                'text' => $row->getName(),
//            ];
//
//            $tableData[] = $rowData;
//        }
//
//        return [
//            'results' => $tableData,
//            'pagination' => [
//                'more' => $start + $length < $result['recordsFiltered'],
//            ]
//        ];
//    }
}