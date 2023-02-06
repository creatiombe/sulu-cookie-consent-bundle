<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\Enum;

class CookieNameEnum
{
    const COOKIE_CONSENT_NAME = 'cookie_consent';

    const COOKIE_CONSENT_KEY_NAME = 'cookie_consent_key';

    const COOKIE_CATEGORY_NAME_PREFIX = 'cookie_consent_category_';

    /**
     * Get cookie category name.
     */
    public static function getCookieCategoryName(string $category): string
    {
        return self::COOKIE_CATEGORY_NAME_PREFIX.$category;
    }
}
