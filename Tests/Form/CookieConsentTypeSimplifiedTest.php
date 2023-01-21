<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\Tests\Form;

use Creatiom\Bundle\SuluCookieConsentBundle\Cookie\CookieChecker;
use Creatiom\Bundle\SuluCookieConsentBundle\Form\CookieConsentType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class CookieConsentTypeSimplifiedTest extends TypeTestCase
{
    /**
     * @var MockObject
     */
    private $cookieChecker;

    public function setUp(): void
    {
        $this->cookieChecker = $this->createMock(CookieChecker::class);

        parent::setUp();
    }

    /**
     * Test submit of CookieConsentType.
     */
    public function testSubmitValidDate(): void
    {
        $formData = [
            'analytics'       => 'false',
            'tracking'        => 'false',
            'marketing'       => 'false',
            'use_all_cookies' => true,
        ];

        $form = $this->factory->create(CookieConsentType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame([
            'analytics'    => 'true',
            'tracking'     => 'true',
            'marketing'    => 'true',
        ], $form->getData());
    }

    protected function getExtensions(): array
    {
        $type = new CookieConsentType($this->cookieChecker, ['analytics', 'tracking', 'marketing'], true);

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
