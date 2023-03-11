<?php

namespace Creatiom\Bundle\SuluCookieConsentBundle\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class RequestSubscriber implements EventSubscriberInterface
{
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
            $response = $event->getResponse();
            $response->setSharedMaxAge(-1);
            $response->headers->addCacheControlDirective('must-revalidate', true);
        }
    }
}