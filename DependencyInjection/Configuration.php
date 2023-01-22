<?php

declare(strict_types=1);

/*
 * This file is part of the ConnectHolland CookieConsentBundle package.
 * (c) Connect Holland.
 */

namespace Creatiom\Bundle\SuluCookieConsentBundle\DependencyInjection;

use Creatiom\Bundle\SuluCookieConsentBundle\Enum\CategoryEnum;
use Creatiom\Bundle\SuluCookieConsentBundle\Enum\PositionEnum;
use Creatiom\Bundle\SuluCookieConsentBundle\Enum\ThemeEnum;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cookie_consent');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = /* @scrutinizer ignore-deprecated */ $treeBuilder->root('cookie_consent');
        }
        $rootNode
            ->children()
                ->variableNode('categories')
                    ->defaultValue([CategoryEnum::CATEGORY_TRACKING, CategoryEnum::CATEGORY_MARKETING, CategoryEnum::CATEGORY_SOCIAL_MEDIA])
                ->end()
                ->enumNode('theme')
                    ->defaultValue(ThemeEnum::THEME_LIGHT)
                    ->values(ThemeEnum::getAvailableThemes())
                ->end()
                ->arrayNode('position')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('vertical')
                            ->defaultValue(PositionEnum::POSITION_BOTTOM)
                        ->end()
                        ->scalarNode('horizontal')
                             ->defaultValue(PositionEnum::POSITION_LEFT)
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('use_logger')
                    ->defaultTrue()
                ->end()
                ->booleanNode('simplified')
                    ->defaultFalse()
                ->end()
                ->booleanNode('http_only')
                    ->defaultTrue()
                ->end()
                ->scalarNode('form_action')
                    ->defaultNull()
                ->end()
                ->booleanNode('csrf_protection')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
