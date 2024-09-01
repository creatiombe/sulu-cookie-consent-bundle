<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\Controller;

use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieChecker;
use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieHandler;
use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieLogger;
use Creatiom\Bundle\SuluCookieConsentBundle\Enum\CookieNameEnum;
use Creatiom\Bundle\SuluCookieConsentBundle\Form\CookieConsentType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsController]
class CookieConsentController
{
    public function __construct(
        private readonly Environment $twigEnvironment,
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
        private readonly string $cookieConsentTheme,
        private readonly string $privacyLink,
        private readonly array $cookieConsentPosition,
        private readonly TranslatorInterface $translator,
        private readonly bool $cookieConsentSimplified,
        private readonly CookieLogger $cookieLogger,
        private readonly CookieHandler $cookieHandler,
        private readonly bool $useLogger,
    ) {
    }

    #[Route('/_cookie_consent/consent', name: 'sulu_cookie_consent.show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        $response = new Response();
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('max-age', '0');
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);
        if ($this->getCookieChecker($request)->isCookieConsentSavedByUser()) {
            return $response;
        }
        $this->setLocale($request);
        $response->setContent(
            $this->twigEnvironment->render('@SuluCookieConsent/cookie_consent.html.twig', [
                'form' => $this->createCookieConsentForm()->createView(),
                'privacy_link' => $this->privacyLink,
                'theme' => $this->cookieConsentTheme,
                'position' => $this->cookieConsentPosition,
                'simplified' => $this->cookieConsentSimplified,
            ]),
        );

        // Cache in ESI should not be shared
        return $response;
    }

    #[Route('/_cookie_consent/agreement', name: 'sulu_cookie_consent.agreement', methods: ['POST'])]
    public function cookieConsentAgreement(Request $request): Response
    {
        $form = $this->createCookieConsentForm();
        $form->handleRequest($request);
        $response = new JsonResponse(['success' => false]);
        if ($form->isSubmitted() && $form->isValid()) {
            $response->setData(['success' => true]);
            /* Deactivate Cache for this token action */
            $this->handleFormSubmit($form->getData(), $request, $response);
        }

        return $response;
    }

    /**
     * Create cookie consent form.
     */
    protected function createCookieConsentForm(): FormInterface
    {
        $form = $this->formFactory->create(
            CookieConsentType::class,
            null,
            [
                'action' => $this->router->generate('sulu_cookie_consent.agreement'),
                'method' => 'POST',
            ],
        );

        return $form;
    }

    /**
     * Set locale if available as GET parameter.
     */
    protected function setLocale(Request $request)
    {
        $locale = $request->get('locale');
        if (false === empty($locale)) {
            $this->translator->setLocale($locale);
            $request->setLocale($locale);
        }
    }

    /**
     * Handle form submit.
     */
    protected function handleFormSubmit(array $data, Request $request, Response $response): void
    {
        if (false === \array_key_exists('categories', $data)) {
            return;
        }
        $cookieConsentKey = $this->getCookieConsentKey($request);

        $categories = $data['categories'];
        $this->cookieHandler->save($categories, $cookieConsentKey, $response);
        $this->cookieConsentSaved = true;
        if ($this->useLogger) {
            $this->cookieLogger->log($categories, $cookieConsentKey);
        }
    }

    /**
     *  Return existing key from cookies or create new one.
     */
    protected function getCookieConsentKey(Request $request): string
    {
        return $request->cookies->get(CookieNameEnum::COOKIE_CONSENT_KEY_NAME) ?? \uniqid('', true);
    }

    /**
     * Get instance of CookieChecker.
     */
    private function getCookieChecker(Request $request): CookieChecker
    {
        return new CookieChecker($request);
    }
}
