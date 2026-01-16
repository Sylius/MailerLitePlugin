<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
                    ->defaultValue('https://connect.mailerlite.com/api')
                    ->info('MailerLite API base URL')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
