<?php

namespace App\Service\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Brand;
use App\Repository\Supplies\BrandRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BrandService extends DatatablesService
{
    private BrandRepository $brandRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(BrandRepository $brandRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->brandRepository = $brandRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getBrandsAsDatatablesArray(Request $request, Household $household): array
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

        $result = $this->brandRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var Brand $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'usageCount' => $this->getUsageCount($row),
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_brand_edit', ['id' => $row->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getBrandsAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->brandRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var Brand $row */
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
     * @param Brand $brand
     * @return int
     */
    protected function getUsageCount(Brand $brand): int {
        return count($brand->getProducts());
    }
}