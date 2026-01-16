<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('sylius_mailerlite.api_base_url', 'https://connect.mailerlite.com/api');
};
