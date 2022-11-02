<?php

namespace App\Service\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\StorageLocation;
use App\Repository\Supplies\StorageLocationRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StorageLocationService extends DatatablesService
{
    private StorageLocationRepository $storageLocationRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(StorageLocationRepository $storageLocationRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->storageLocationRepository = $storageLocationRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getStorageLocationsAsDatatablesArray(Request $request, Household $household): array
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
            ['name', ],
            (array) $request->query->all('columns'),
            (array) $request->query->all('order')
        );

        $result = $this->storageLocationRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var StorageLocation $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'usageCount' => $this->getUsageCount($row),
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_storagelocation_edit', ['id' => $row->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getStorageLocationsAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->storageLocationRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var StorageLocation $row */
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
     * @param StorageLocation $storageLocation
     * @return int
     */
    protected function getUsageCount(StorageLocation $storageLocation): int {
        //TODO: This also includes items which have already been checked out. Should we exclude them?
        return count($storageLocation->getSupplyItems());
    }
}