<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\SuluCookieConsentBundle\Tests\Twig;

use Creatiom\SuluCookieConsentBundle\Twig\CookieConsentTwigExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\HttpFoundation\Request;

class CookieConsentTwigExtensionTest extends TestCase
{
    /**
     * @var CookieConsentTwigExtension
     */
    private $cookieconsentTwigExtension;

    public function setUp(): void
    {
        $this->cookieconsentTwigExtension = new CookieConsentTwigExtension();
    }

    public function testGetFunctions(): void
    {
        $functions = $this->cookieconsentTwigExtension->getFunctions();

        $this->assertCount(2, $functions);
        $this->assertSame('cookieconsent_isCookieConsentSavedByUser', $functions[0]->getName());
        $this->assertSame('cookieconsent_isCategoryAllowedByUser', $functions[1]->getName());
    }

    public function testIsCookieConsentSavedByUser(): void
    {
        $request  = new Request();

        $appVariable = $this->createMock(AppVariable::class);
        $appVariable
            ->expects($this->once())
            ->method('getRequest')
            ->wilLReturn($request);

        $context = ['app' => $appVariable];
        $result  = $this->cookieconsentTwigExtension->isCookieConsentSavedByUser($context);

        $this->assertSame($result, false);
    }

    public function testIsCategoryAllowedByUser(): void
    {
        $request  = new Request();

        $appVariable = $this->createMock(AppVariable::class);
        $appVariable
            ->expects($this->once())
            ->method('getRequest')
            ->wilLReturn($request);

        $context = ['app' => $appVariable];
        $result  = $this->cookieconsentTwigExtension->isCategoryAllowedByUser($context, 'analytics');

        $this->assertSame($result, false);
    }
}
