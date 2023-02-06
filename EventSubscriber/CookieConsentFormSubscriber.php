<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\EventSubscriber;

use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieHandler;
use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieLogger;
use Creatiom\Bundle\SuluCookieConsentBundle\Enum\CookieNameEnum;
use Creatiom\Bundle\SuluCookieConsentBundle\Form\CookieConsentType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CookieConsentFormSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CookieLogger
     */
    private $cookieLogger;

    /**
     * @var CookieHandler
     */
    private $cookieHandler;

    /**
     * @var bool
     */
    private $useLogger;

    public function __construct(FormFactoryInterface $formFactory, CookieLogger $cookieLogger, CookieHandler $cookieHandler, bool $useLogger)
    {
        $this->formFactory   = $formFactory;
        $this->cookieLogger  = $cookieLogger;
        $this->cookieHandler = $cookieHandler;
        $this->useLogger     = $useLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
           KernelEvents::RESPONSE => ['onResponse'],
        ];
    }

    /**
     * Checks if form has been submitted and saves users preferences in cookies by calling the CookieHandler.
     */
    public function onResponse(KernelEvent $event): void
    {
        if ($event instanceof FilterResponseEvent === false && $event instanceof ResponseEvent === false) {
            throw new \RuntimeException('No ResponseEvent class found');
        }

        $request  = $event->getRequest();
        $response = $event->getResponse();

        $form = $this->createCookieConsentForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFormSubmit($form->getData(), $request, $response);
        }
    }

    /**
     * Handle form submit.
     */
    protected function handleFormSubmit(array $data, Request $request, Response $response): void
    {

        if(array_key_exists('categories', $data) === false) {
            return;
        }
        $cookieConsentKey = $this->getCookieConsentKey($request);

        $categories = $data['categories'];
        $this->cookieHandler->save($categories, $cookieConsentKey, $response);

        if ($this->useLogger) {
            $this->cookieLogger->log($categories, $cookieConsentKey);
        }
    }

    /**
     *  Return existing key from cookies or create new one.
     */
    protected function getCookieConsentKey(Request $request): string
    {
        return $request->cookies->get(CookieNameEnum::COOKIE_CONSENT_KEY_NAME) ?? uniqid('', true);
    }

    /**
     * Create cookie consent form.
     */
    protected function createCookieConsentForm(): FormInterface
    {
        return $this->formFactory->create(CookieConsentType::class);
    }
}
