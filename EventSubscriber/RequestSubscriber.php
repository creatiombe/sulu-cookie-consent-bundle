<?php

namespace Creatiom\Bundle\SuluCookieConsentBundle\EventSubscriber;

use FOS\HttpCache\ResponseTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    private ResponseTagger $responseTagger;

    public function __construct(ResponseTagger $responseTagger)
    {
        $this->responseTagger = $responseTagger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $controllerEvent)
    {
        if (!$controllerEvent->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }
        if ($controllerEvent->getRequest()->cookies->has('cookie_consent')) {
            $this->responseTagger->addTags(['cookie_consent']);
        } else {
            $this->responseTagger->addTags(['no_cookie_consent']);
            $this->responseTagger->addTags(['no_cookie_consent']);
        }
    }
}
