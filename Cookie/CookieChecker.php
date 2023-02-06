<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\Cookie;

use Creatiom\Bundle\SuluCookieConsentBundle\Enum\CookieNameEnum;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class CookieChecker
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Check if cookie consent has already been saved.
     */
    public function isCookieConsentSavedByUser(): bool
    {
        return $this->request->cookies->has(CookieNameEnum::COOKIE_CONSENT_NAME);
    }

    /**
     * Check if given cookie category is permitted by user.
     */
    public function isCategoryAllowedByUser(string $category): bool
    {
        return $this->request->cookies->get(CookieNameEnum::getCookieCategoryName($category)) === 'true';
    }


    /**
     * Check if given cookie category is permitted by user.
     */
    public function hasCategoryCookieSet(string $category): bool
    {
        if(!$this->request->cookies->get(CookieNameEnum::getCookieCategoryName($category))) {
            return true;
        }
        return $this->isCategoryAllowedByUser($category);
    }
}
