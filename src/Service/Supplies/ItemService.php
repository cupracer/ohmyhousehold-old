<?php

namespace App\Service\Supplies;

use App\Entity\Household;
use App\Entity\Supplies\Item;
use App\Entity\User;
use App\Repository\Supplies\ItemRepository;
use App\Service\DatatablesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class ItemService extends DatatablesService
{
    private ItemRepository $itemRepository;
    private UrlGeneratorInterface $urlGenerator;
    private Security $security;

    public function __construct(ItemRepository $itemRepository, UrlGeneratorInterface $urlGenerator, Security $security)
    {
        $this->itemRepository = $itemRepository;
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
    }

    public function getItemsAsDatatablesArray(Request $request, Household $household): array
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
            ['purchaseDate', 'product', 'bestBeforeDate', ],
            (array) $request->query->get('columns'),
            (array) $request->query->get('order')
        );

        $result = $this->itemRepository->getFilteredDataByHousehold(
            $household, $start, $length, $orderingData, $search);

        $tableData = [];

        /** @var User $user */
        $user = $this->security->getUser();
        $dateFormatter = new \IntlDateFormatter($user->getUserProfile()->getLocale(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);

        /** @var Item $row */
        foreach($result['data'] as $row) {
            $rowData = [
                'id' => $row->getId(),
                'product' => $row->getProduct()->getSupply()->getName() . ($row->getProduct()->getName() ? ' - ' . $row->getProduct()->getName() : ''),
                'brand' => $row->getProduct()->getBrand()->getName(),
                'category' => $row->getProduct()->getSupply()->getCategory()->getName(),
                'amount' => 1*$row->getProduct()->getQuantity() . $row->getProduct()->getMeasure()->getName(),
                'purchaseDate' => $row->getPurchaseDate() ? $dateFormatter->format($row->getPurchaseDate()) : null,
                'bestBeforeDate' => $row->getBestBeforeDate() ? $dateFormatter->format($row->getBestBeforeDate()) : null,
                'createdAt' => \IntlDateFormatter::formatObject($row->getCreatedAt()),
            ];

            $rowData['editLink'] = $this->urlGenerator->generate(
                'supplies_item_edit', ['id' => $row->getId()]);

            $rowData['checkoutLink'] = $this->urlGenerator->generate(
                'supplies_item_checkout', ['id' => $row->getId()]);

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