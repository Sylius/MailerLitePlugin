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

use Sylius\MailerLitePlugin\Controller\NewsletterSubscriptionController;
use Sylius\MailerLitePlugin\Subscriber\GuestCheckoutNewsletterSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('sylius_mailerlite.controller.newsletter_subscription', NewsletterSubscriptionController::class)
        ->args([
            service('sylius.repository.order'),
            service(GuestCheckoutNewsletterSubscriberInterface::class),
            service('router'),
            service('security.csrf.token_manager'),
            service('translator'),
        ])
        ->public()
    ;
};
