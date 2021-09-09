<?php

namespace App\EventSubscriber;

use App\Service\LocaleService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
        $this->twig->addGlobal('supportedLocales', $this->localeService->getSupportedLocales());
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
