<?php

namespace App\Service\Supplies;

use App\Entity\GuiNotification;
use App\Entity\Household;
use App\Repository\HouseholdRepository;
use App\Repository\Supplies\ItemRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class NotificationService
{
    private ItemRepository $itemRepository;
    private HouseholdRepository $householdRepository;
    private SessionInterface $session;
    private Household $household;


    public function __construct(ItemRepository $itemRepository, HouseholdRepository $householdRepository, SessionInterface $session)
    {
        $this->itemRepository = $itemRepository;
        $this->householdRepository = $householdRepository;
        $this->session = $session;

        if($this->session->has('current_household')) {
            $this->household = $this->householdRepository->find($this->session->get('current_household'));
        }
    }


    public function getExpiringSupplyItems(): array
    {
        $notifications = [];

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
}