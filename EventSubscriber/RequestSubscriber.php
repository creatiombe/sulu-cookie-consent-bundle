<?php

namespace Creatiom\Bundle\SuluCookieConsentBundle\EventSubscriber;


use FOS\HttpCache\ResponseTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

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
            ResponseEvent::class => 'onKernelResponse',
        ];
    }
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }
        if(!$event->getRequest()->cookies->has('cookie_consent')) {
            $this->responseTagger->addTags(['no_cookie_consent']);
        } else {
            $this->responseTagger->addTags(['cookie_consent']);
        }
    }
}