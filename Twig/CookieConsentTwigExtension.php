<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\SuluCookieConsentBundle\Twig;

use Creatiom\SuluCookieConsentBundle\Cookie\CookieChecker;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CookieConsentTwigExtension extends AbstractExtension
{
    /**
     * Register all custom twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'cookieconsent_show',
                [$this, 'showCookieConsent'],
                ['needs_context' => true]
            ),
            new TwigFunction(
                'cookieconsent_category',
                [$this, 'hasCookieCategory'],
                ['needs_context' => true]
            ),
        ];
    }

    /**
     * Checks if user has sent cookie consent form.
     */
    public function showCookieConsent(array $context): bool
    {
        /** @var Request $request */
        $request = $context['app']->getRequest();

        if ($request->attributes->has('preview')) {
            return false;
        }

        return !$this->getCookieChecker($request)->isCookieConsentSavedByUser();
    }

    /**
     * Checks if user has given permission for cookie category.
     */
    public function hasCookieCategory(array $context, string $category): bool
    {
        $request = $context['app']->getRequest();

        if ($request->attributes->has('preview')) {
            return false;
        }

        return $this->getCookieChecker($request)->isCategoryAllowedByUser($category);
    }

    /**
     * Get instance of CookieChecker.
     */
    private function getCookieChecker(Request $request): CookieChecker
    {
        return new CookieChecker($request);
    }
}
