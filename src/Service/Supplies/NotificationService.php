<?php

namespace App\Service\Supplies;

use App\Entity\GuiNotification;
use App\Entity\Household;
use App\Entity\Supplies\Supply;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\ItemRepository;
use App\Repository\Supplies\SupplyRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class NotificationService
{
    private ItemRepository $itemRepository;
    private HouseholdRepository $householdRepository;
    private SessionInterface $session;
    private SupplyRepository $supplyRepository;
    private Household $household;


    public function __construct(ItemRepository $itemRepository, HouseholdRepository $householdRepository, SessionInterface $session, SupplyRepository $supplyRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->householdRepository = $householdRepository;
        $this->session = $session;
        $this->supplyRepository = $supplyRepository;

        if($this->session->has('current_household')) {
            $this->household = $this->householdRepository->find($this->session->get('current_household'));
        }
    }


    public function getExpiringSupplyItems(): array
    {
        $notifications = [];

        //TODO: Is there a better way to ensure a usable household variable?
        if(!$this->household) {
            return $notifications;
        }

        //TODO: make variables configurable
        $daysLeftLimit = 14;
        $daysLeftWarning = 7;

        foreach($this->itemRepository->findAllExpiringItemsGrantedByHousehold($this->household, $daysLeftLimit) as $item) {
            $gn = new GuiNotification();
            $gn->setCategory('supplies_item');
            $gn->setTitle($item->getProduct()->getSupply()->getName() . ($item->getProduct()->getName() ? ' - ' . $item->getProduct()->getName() : ''));
            $gn->setNote($item->getBestBeforeDate()->format('d.m.Y'));
            $gn->setItemId($item->getId());

            if($item->getBestBeforeDate() < (new DateTime())->modify("midnight")) {
                $gn->setCssClass('danger');
            }elseif($item->getBestBeforeDate() <= (new DateTime())->modify("midnight")->modify('+ ' . $daysLeftWarning . ' days')) {
                $gn->setCssClass('warning');
            }else {
                $gn->setCssClass('black');
            }

            $notifications[] = $gn;
        }

        return $notifications;
    }

    public function getRunningLowSupplies(): array
    {
        $notifications = [];

        //TODO: Is there a better way to ensure a usable household variable?
        if(!$this->household) {
            return $notifications;
        }

        foreach($this->supplyRepository->findAllRunningLowSuppliesGrantedByHousehold($this->household) as $row) {
            /** @var Supply $supply */
            $supply = $row[0];
            $numUsage = $row['numUsage'];

            $gn = new GuiNotification();
            $gn->setCategory('supply');
            $gn->setTitle($supply->getName());
            $gn->setNote($numUsage . '/' . $supply->getMinimumNumber());
            $gn->setItemId($supply->getId());

            if($numUsage == 0) {
                $gn->setCssClass('danger');
            }else {
                $gn->setCssClass('warning');
            }

            $notifications[] = $gn;
        }

        return $notifications;
    }
}