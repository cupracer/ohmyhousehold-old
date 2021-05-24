<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\HouseholdRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class HouseholdSubscriber implements EventSubscriberInterface
{
    private $session;
    private $householdRepository;

    public function __construct(SessionInterface $session, HouseholdRepository $householdRepository)
    {
        $this->session = $session;
        $this->householdRepository = $householdRepository;
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        // The user's session should always contain a "current_household" value.
        // To ensure that it is set upon loggin in, we search for all repositories
        // where the user has admin rights and pick the first one.
        // TODO: Make current_household configurable via user preferences.
        $households = $this->householdRepository->findByAdmin($user);

        if(is_array($households)) {
            $this->session->set('current_household', $households[0]->getId());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}