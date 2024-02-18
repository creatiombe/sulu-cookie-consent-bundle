<?php

namespace Creatiom\Bundle\SuluCookieConsentBundle\EventListener;

use FOS\HttpCache\ResponseTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    private ResponseTagger $responseTagger;

    public function __construct(ResponseTagger $responseTagger)
    {
        $this->responseTagger = $responseTagger;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 35]];
    }

    public function onRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }
        if ($event->getRequest()->cookies->has('cookie_consent')) {
            $this->responseTagger->addTags(['cookie_consent']);
        } else {
            $this->responseTagger->addTags(['no_cookie_consent']);
        }
    }
}
