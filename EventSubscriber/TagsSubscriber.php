<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Creatiom\SuluCookieConsentBundle\EventSubscriber;

use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds tags to the Symfony response tagger from all available reference stores.
 */
class TagsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SymfonyResponseTagger $symfonyResponseTagger,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['addTags', 35],
            KernelEvents::REQUEST => ['addTags', 35],
        ];
    }

    /**
     * Adds tags from the reference store to the response tagger.
     */
    public function addTags(): void
    {
        if (($this->requestStack->getMainRequest() && $this->requestStack->getMainRequest()->cookies->has('cookie_consent'))
            || ($this->requestStack->getCurrentRequest() && $this->requestStack->getCurrentRequest()->cookies->has('cookie_consent'))) {
            $this->symfonyResponseTagger->addTags(['cookie-consent']);
        } else {
            $this->symfonyResponseTagger->addTags(['no-cookie-consent']);
        }
    }
}
