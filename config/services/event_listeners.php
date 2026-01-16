<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MailerLitePlugin\EventListener\CustomerRegistrationListener;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.listener.customer_registration', CustomerRegistrationListener::class)
        ->args([
            service(NewsletterSubscriberInterface::class),
        ])
        ->tag('kernel.event_listener', [
            'event' => 'sylius.customer.post_register',
            'method' => 'onCustomerPostRegister',
        ]);
};
