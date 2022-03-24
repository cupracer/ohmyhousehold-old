<?php

namespace App\Service;

use App\Entity\Household;
use App\Repository\HouseholdRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsService
{
    private HouseholdRepository $householdRepository;
    private RequestStack $requestStack;

    public function __construct(HouseholdRepository $householdRepository, RequestStack $requestStack)
    {
        $this->householdRepository = $householdRepository;
        $this->requestStack = $requestStack;
    }

    //TODO: Use this method everywhere if possible
    public function getCurrentHousehold(UserInterface $user): ?Household
    {
        $currentHousehold = null;

        if($this->requestStack->getSession()->has('current_household')) {
            $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
        }

        if(!$currentHousehold) {
            $households = $this->householdRepository->findByMember($user);

            if($households) {
                $currentHousehold = $households[0];
                $this->requestStack->getSession()->set('current_household', $currentHousehold->getId());
            }
        }

        return $currentHousehold;
    }
}