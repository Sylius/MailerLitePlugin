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

use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;
use Sylius\MailerLitePlugin\Subscriber\GuestCheckoutNewsletterSubscriber;
use Sylius\MailerLitePlugin\Subscriber\GuestCheckoutNewsletterSubscriberInterface;
use Sylius\MailerLitePlugin\Subscriber\MailerLiteSubscriber;
use Sylius\MailerLitePlugin\Subscriber\MailerLiteSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.subscriber.mailerlite', MailerLiteSubscriber::class)
        ->args([
            service(MailerLiteClientInterface::class),
            service('logger')->nullOnInvalid(),
        ]);

    $services->alias(MailerLiteSubscriberInterface::class, 'sylius_mailerlite.subscriber.mailerlite');

    $services
        ->set('sylius_mailerlite.subscriber.guest_checkout', GuestCheckoutNewsletterSubscriber::class)
        ->args([
            service(MailerLiteSubscriberInterface::class),
            service('doctrine.orm.entity_manager'),
        ]);

    $services->alias(GuestCheckoutNewsletterSubscriberInterface::class, 'sylius_mailerlite.subscriber.guest_checkout');
};
