<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\SuluCookieConsentBundle\Enum;

class PositionEnum
{
    const POSITION_TOP     = 'top';
    const POSITION_BOTTOM  = 'bottom';
    const POSITION_LEFT    = 'left';
    const POSITION_RIGHT   = 'right';

    /**
     * @var array
     */
    private static $horizontalPositions = [
        self::POSITION_LEFT,
        self::POSITION_RIGHT,
    ];
    /**
     * @var array
     */
    private static $verticalPositions = [
        self::POSITION_TOP,
        self::POSITION_BOTTOM,
    ];

    /**
     * Get all cookie consent positions.
     */
    public static function getAvailableHorizontalPositions(): array
    {
        return self::$horizontalPositions;
    }

    /**
     * Get all cookie consent positions.
     */
    public static function getAvailableVerticalPositions(): array
    {
        return self::$verticalPositions;
    }
}
