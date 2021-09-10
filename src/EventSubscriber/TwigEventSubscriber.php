<?php

namespace App\EventSubscriber;

use App\Service\LocaleService;
use App\Service\Supplies\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private string $siteName;
    private Environment $twig;
    private LocaleService $localeService;
    private NotificationService $notificationService;
    private Security $security;

    public function __construct(string $siteName, Environment $twig, LocaleService $localeService, NotificationService $notificationService, Security $security)
    {
        $this->siteName = $siteName;
        $this->twig = $twig;
        $this->localeService = $localeService;
        $this->notificationService = $notificationService;
        $this->security = $security;
    }

    public function onKernelController()
    {
        $this->twig->addGlobal('siteName', $this->siteName);
        $this->twig->addGlobal('supportedLocales', $this->localeService->getSupportedLocales());
//        $this->twig->addGlobal('navbarNotifications', $this->notificationService->getCategorizedNotifications());

        if($this->security->isGranted('ROLE_SUPPLIES')) {
            $this->twig->addGlobal('navbarSupplyItemNotifications', $this->notificationService->getExpiringSupplyItems());
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
