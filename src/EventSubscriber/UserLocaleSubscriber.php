<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleSubscriber afterwards.
 */
class UserLocaleSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        if (null !== $user->getUserProfile() && null !== $user->getUserProfile()->getLocale()) {
            $this->session->set('_locale', $user->getUserProfile()->getLocale());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}