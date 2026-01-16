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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MailerLitePlugin\Handler\CheckoutNewsletterSubscriptionHandler;
use Sylius\MailerLitePlugin\Handler\CheckoutNewsletterSubscriptionHandlerInterface;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.handler.checkout_newsletter_subscription', CheckoutNewsletterSubscriptionHandler::class)
        ->args([
            service(NewsletterSubscriberInterface::class),
            service('doctrine.orm.entity_manager'),
        ])
    ;

    $services->alias(CheckoutNewsletterSubscriptionHandlerInterface::class, 'sylius_mailerlite.handler.checkout_newsletter_subscription');
};
