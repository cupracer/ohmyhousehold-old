<?php

namespace App\Service\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Packaging;
use App\Repository\Supplies\PackagingRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PackagingService extends DatatablesService
{
    private PackagingRepository $packagingRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(PackagingRepository $packagingRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->packagingRepository = $packagingRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getPackagingsAsDatatablesArray(Request $request, Household $household): array
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
            ['name', ],
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        $result = $this->packagingRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var Packaging $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'usageCount' => $this->getUsageCount($row),
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_packaging_edit', ['id' => $row->getId()]);

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

    /**
     * @param Packaging $packaging
     * @return int
     */
    protected function getUsageCount(Packaging $packaging): int {
        return count($packaging->getProducts());
    }
}