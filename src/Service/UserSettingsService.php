<?php

namespace App\Service;

use App\Entity\Household;
use App\Repository\HouseholdRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsService
{
    private HouseholdRepository $householdRepository;
    private SessionInterface $session;

    public function __construct(HouseholdRepository $householdRepository, SessionInterface $session)
    {
        $this->householdRepository = $householdRepository;
        $this->session = $session;
    }

    //TODO: Use this method everywhere if possible
    public function getCurrentHousehold(UserInterface $user): ?Household
    {
        $currentHousehold = null;

        if($this->session->has('current_household')) {
            $currentHousehold = $this->householdRepository->find($this->session->get('current_household'));
        }

        if(!$currentHousehold) {
            $households = $this->householdRepository->findByMember($user);

            if($households) {
                $currentHousehold = $households[0];
                $this->session->set('current_household', $currentHousehold->getId());
            }
        }

        return $currentHousehold;
    }
}