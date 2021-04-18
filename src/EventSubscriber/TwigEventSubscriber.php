<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private string $siteName;
    private Environment $twig;

    public function __construct(string $siteName, Environment $twig)
    {
        $this->siteName = $siteName;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $this->twig->addGlobal('siteName', $this->siteName);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
