<?php

declare(strict_types=1);

namespace Sylius\MailerLitePlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_mailerlite');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->defaultValue('%env(MAILERLITE_API_KEY)%')
                    ->info('MailerLite API key')
                ->end()
                ->scalarNode('api_url')
                    ->defaultValue('%env(default::MAILERLITE_API_URL)%')
                    ->info('MailerLite API base URL (optional, uses default if not set)')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
