<?php

namespace App\Service;

use App\Entity\BookingCategory;
use App\Entity\Household;
use App\Repository\BookingCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BookingCategoryService extends DatatablesService
{
    private BookingCategoryRepository $bookingCategoryRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(BookingCategoryRepository $bookingCategoryRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->bookingCategoryRepository = $bookingCategoryRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getBookingCategoriesAsDatatablesArray(Request $request, Household $household): array
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
            ['name', 'createdAt', ],
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        $result = $this->bookingCategoryRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var BookingCategory $row */
        foreach($result['data'] as $row) {
            $rowData = $row->jsonSerialize();
            $rowData['usageCount'] = $this->getUsageCount($row);
            $rowData['createdAt'] = \IntlDateFormatter::formatObject($rowData['createdAt']);
            $rowData['editLink'] = $this->urlGenerator->generate(
                'housekeepingbook_bookingcategory_edit', ['id' => $row->getId()]);

            $tableData[] = $rowData;
        }

        return [
            'draw' => $draw,
            'data' => $tableData,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
        ];
    }

    public function getBookingCategoriesAsSelect2Array(Request $request, Household $household): array
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

        $result = $this->bookingCategoryRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var BookingCategory $row */
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
     * @param BookingCategory $bookingCategory
     * @return int
     */
    protected function getUsageCount(BookingCategory $bookingCategory): int {
        $count = 0;

        $count+= count($bookingCategory->getDepositTransactions());
        $count+= count($bookingCategory->getTransferTransactions());
        $count+= count($bookingCategory->getWithdrawalTransactions());

        $count+= count($bookingCategory->getPeriodicDepositTransactions());
        $count+= count($bookingCategory->getPeriodicTransferTransactions());
        $count+= count($bookingCategory->getPeriodicWithdrawalTransactions());

        return $count;
    }
}