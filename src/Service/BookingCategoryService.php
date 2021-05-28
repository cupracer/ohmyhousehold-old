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
        $search = null;

        if(array_key_exists('value', $searchParam)) {
            $search = $searchParam['value'];
        }

        $orderingData = $this->getOrderingData(
            new BookingCategory(),
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        dump($orderingData);

        $result = $this->bookingCategoryRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var BookingCategory $row */
        foreach($result['data'] as $row) {
            $rowData = $row->jsonSerialize();

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
}