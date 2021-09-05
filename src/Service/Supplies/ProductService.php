<?php

namespace App\Service\Supplies;

use App\Entity\AccountHolder;
use App\Entity\Household;
use App\Entity\Supplies\Product;
use App\Repository\AccountHolderRepository;
use App\Repository\Supplies\ProductRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductService extends DatatablesService
{
    private ProductRepository $productRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(ProductRepository $productRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->productRepository = $productRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getProductsAsDatatablesArray(Request $request, Household $household, bool $inUseOnly): array
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
            ['name', 'brand', 'ean', 'category', 'packaging', ],
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        $result = $this->productRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $inUseOnly, $search);

        $tableData = [];

        /** @var Product $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'name' => $row->getSupply()->getName() . ($row->getName() ? ' - ' . $row->getName() : ''),
                'brand' => $row->getBrand()->getName(),
                'ean' => $row->getEan(),
                'category' => $row->getSupply()->getCategory()->getName(),
                'packaging' => $row->getPackaging() ? $row->getPackaging()->getName() : null,
                'amount' => 1*$row->getQuantity() . $row->getMeasure()->getName(),
                'usageCount' => $this->getUsageCount($row),
                'createdAt' => \IntlDateFormatter::formatObject($row->getCreatedAt())
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_product_edit', ['id' => $row->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getProductsAsSelect2Array(Request $request, Household $household, bool $inUseOnly): array
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

        $result = $this->productRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $inUseOnly, $search);

        $tableData = [];

        /** @var Product $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'text' => $row->getSupply()->getName() .
                    ($row->getName() ? ' - ' . $row->getName() : '') .
                    ' - ' . $row->getBrand()->getName() .
                    ' - ' . 1*$row->getQuantity() . $row->getMeasure()->getName(),
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
     * @param Product $product
     * @return int
     */
    protected function getUsageCount(Product $product): int {
        return count($product->getItems());
    }
}