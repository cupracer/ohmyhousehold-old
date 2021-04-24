<?php

namespace App\EventSubscriber;

use App\Service\LocaleService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\String\UnicodeString;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private string $siteName;
    private Environment $twig;
    private LocaleService $localeService;

    public function __construct(string $siteName, Environment $twig, LocaleService $localeService)
    {
        $this->siteName = $siteName;
        $this->twig = $twig;
        $this->localeService = $localeService;
    }

    public function onKernelController()
    {
        $this->twig->addGlobal('siteName', $this->siteName);
        $this->twig->addGlobal('localeRepresentations', $this->localeService->getLocaleRepresentations());
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
