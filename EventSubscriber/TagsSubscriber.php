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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds tags to the Symfony response tagger from all available reference stores.
 */
class TagsSubscriber implements EventSubscriberInterface
{
    private ?SymfonyResponseTagger $symfonyResponseTagger = null;
    
    public function __construct(
        private RequestStack $requestStack,
        private ContainerInterface $container,
    ) {
        // Try to retrieve the Symfony response tagger from the container.
        if($this->container->has(SymfonyResponseTagger::class)) {
            $this->symfonyResponseTagger = $this->container->get(SymfonyResponseTagger::class);
        }
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
        // Check if the Symfony response tagger is available.
        if (!$this->symfonyResponseTagger) {
            return;
        }
        
        if (($this->requestStack->getMainRequest() && $this->requestStack->getMainRequest()->cookies->has('cookie_consent'))
            || ($this->requestStack->getCurrentRequest() && $this->requestStack->getCurrentRequest()->cookies->has('cookie_consent'))) {
            $this->symfonyResponseTagger->addTags(['cookie-consent']);
        } else {
            $this->symfonyResponseTagger->addTags(['no-cookie-consent']);
        }
    }
}
