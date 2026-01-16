<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriber;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.subscriber.newsletter', NewsletterSubscriber::class)
        ->args([
            service(MailerLiteClientInterface::class),
            service('logger')->nullOnInvalid(),
        ]);

    $services->alias(NewsletterSubscriberInterface::class, 'sylius_mailerlite.subscriber.newsletter');
};
