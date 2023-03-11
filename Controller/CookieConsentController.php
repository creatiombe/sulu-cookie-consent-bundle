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
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\HttpCache\ResponseTagger;
use Twig\Environment;

class CookieConsentController
{
    /**
     * @var Environment
     */
    private $twigEnvironment;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CookieChecker
     */
    private $cookieChecker;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $cookieConsentTheme;

    /**
     * @var string
     */
    private $privacyLink;

    /**
     * @var array
     */
    private $cookieConsentPosition;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var bool
     */
    private $cookieConsentSimplified;

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
    private ResponseTagger $responseTagger;

    public function __construct(
        Environment $twigEnvironment,
        FormFactoryInterface $formFactory,
        CookieChecker $cookieChecker,
        RouterInterface $router,
        string $cookieConsentTheme,
        string $privacyLink,
        array $cookieConsentPosition,
        TranslatorInterface $translator,
        bool $cookieConsentSimplified = false,
        CookieLogger $cookieLogger,
        CookieHandler $cookieHandler,
        bool $useLogger = false,
        ResponseTagger $responseTagger
    ) {
        $this->twigEnvironment          = $twigEnvironment;
        $this->formFactory              = $formFactory;
        $this->cookieChecker            = $cookieChecker;
        $this->router                   = $router;
        $this->cookieConsentTheme       = $cookieConsentTheme;
        $this->privacyLink              = $privacyLink;
        $this->cookieConsentPosition    = $cookieConsentPosition;
        $this->translator               = $translator;
        $this->cookieConsentSimplified  = $cookieConsentSimplified;
        $this->cookieLogger             = $cookieLogger;
        $this->cookieHandler            = $cookieHandler;
        $this->useLogger                = $useLogger;
        $this->responseTagger           = $responseTagger;
    }

    /**
     * Show cookie consent.
     *
     * @Route("/cookie_consent", name="sulu_cookie_consent.show", methods={"GET"})
     */
    public function show(Request $request): Response
    {
        $this->setLocale($request);
        $form = $this->createCookieConsentForm();
        $response = new Response(
            $this->twigEnvironment->render('@SuluCookieConsent/cookie_consent.html.twig', [
                'form'          => $this->createCookieConsentForm()->createView(),
                'privacy_link'  => $this->privacyLink,
                'theme'         => $this->cookieConsentTheme,
                'position'      => $this->cookieConsentPosition,
                'simplified'    => $this->cookieConsentSimplified,
            ])
        );

        // Cache in ESI should not be shared
        $response->setVary('Cookies', true);
        $response->setMaxAge(0);
        $response->setPrivate();
        $this->responseTagger->addTags(['sulu_cookie_consent','notset']);
        return $response;
    }

    /**
     * Show cookie consent.
     *
     * @Route("/cookie_consent_no_agreement", name="sulu_cookie_consent.no_agreement", methods={"GET"})
     */
    public function showIfCookieConsentNotSet(Request $request): Response
    {
        if ($this->cookieChecker->isCookieConsentSavedByUser() === false) {
            return $this->show($request);
        }
        // Cache in ESI should not be shared
        $response = new Response();
        /* Deactivate Cache for this token action */
        $response->setVary('Cookies', true);
        $response->setMaxAge(0);
        $response->setPrivate();
        $this->responseTagger->addTags(['sulu_cookie_consent','set']);
        return $response;
    }

    /**
     * Show cookie consent.
     *
     * @Route("/_cookie/agreement", name="sulu_cookie_consent.agreement", methods={"POST"})
     */
    public function cookieConsentAgreement(Request $request): Response
    {
        $form = $this->createCookieConsentForm();
        $form->handleRequest($request);
        $response = new JsonResponse(['success' => false]);
        if ($form->isSubmitted() && $form->isValid()) {
            $response->setData(['success' => true]);
            /* Deactivate Cache for this token action */
            $response->setVary('Cookies', true);
            $response->setMaxAge(0);
            $response->setPrivate();
            $this->responseTagger->addTags(['sulu_cookie_consent','submitted']);
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
            ]
        );

        return $form;
    }

    /**
     * Set locale if available as GET parameter.
     */
    protected function setLocale(Request $request)
    {
        $locale = $request->get('locale');
        if (empty($locale) === false) {
            $this->translator->setLocale($locale);
            $request->setLocale($locale);
        }
    }

    /**
     * Handle form submit.
     */
    protected function handleFormSubmit(array $data, Request $request, Response $response): void
    {
        if (false === array_key_exists('categories', $data)) {
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
        return $request->cookies->get(CookieNameEnum::COOKIE_CONSENT_KEY_NAME) ?? uniqid('', true);
    }
}
