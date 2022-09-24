<?php

namespace App\EventSubscriber;

use App\Repository\HouseholdRepository;
use App\Service\LocaleService;
use App\Service\Supplies\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private string $siteName;
    private Environment $twig;
    private LocaleService $localeService;
    private NotificationService $notificationService;
    private Security $security;
    private RequestStack $requestStack;
    private HouseholdRepository $householdRepository;

    public function __construct(string $siteName, Environment $twig, LocaleService $localeService, NotificationService $notificationService, Security $security, RequestStack $requestStack, HouseholdRepository $householdRepository)
    {
        $this->siteName = $siteName;
        $this->twig = $twig;
        $this->localeService = $localeService;
        $this->notificationService = $notificationService;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->householdRepository = $householdRepository;
    }

    public function onKernelController()
    {
        $this->twig->addGlobal('siteName', $this->siteName);
        $this->twig->addGlobal('supportedLocales', $this->localeService->getSupportedLocales());
//        $this->twig->addGlobal('navbarNotifications', $this->notificationService->getCategorizedNotifications());

        if($this->security->isGranted('ROLE_SUPPLIES')) {
            $this->twig->addGlobal('navbarExpiringSupplyItemNotifications', $this->notificationService->getExpiringSupplyItems());
            $this->twig->addGlobal('navbarRunningLowSuppliesNotifications', $this->notificationService->getRunningLowSupplies());
        }

        if($this->security->isGranted('ROLE_USER') && $this->requestStack->getSession()->has('current_household')) {
            $currentHousehold = $this->householdRepository->find($this->requestStack->getSession()->get('current_household'));
            $this->twig->addGlobal('current_household', $currentHousehold);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
