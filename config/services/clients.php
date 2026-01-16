<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MailerLitePlugin\Client\MailerLiteClient;
use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.client.mailer_lite', MailerLiteClient::class)
        ->args([
            service('http_client'),
            param('sylius_mailerlite.api_key'),
            param('sylius_mailerlite.api_url'),
            service('logger')->nullOnInvalid(),
        ]);

    $services->alias(MailerLiteClientInterface::class, 'sylius_mailerlite.client.mailer_lite');
};
