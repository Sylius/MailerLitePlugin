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

use Sylius\MailerLitePlugin\EventListener\CustomerRegistrationListener;
use Sylius\MailerLitePlugin\Subscriber\MailerLiteSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.listener.customer_registration', CustomerRegistrationListener::class)
        ->args([
            service(MailerLiteSubscriberInterface::class),
        ])
        ->tag('kernel.event_listener', [
            'event' => 'sylius.customer.post_register',
            'method' => 'onCustomerPostRegister',
        ])
    ;
};
