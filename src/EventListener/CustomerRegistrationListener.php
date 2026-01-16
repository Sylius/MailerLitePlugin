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

namespace Sylius\MailerLitePlugin\EventListener;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MailerLitePlugin\Subscriber\MailerLiteSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CustomerRegistrationListener
{
    public function __construct(
        private readonly MailerLiteSubscriberInterface $mailerLiteSubscriber,
    ) {
    }

    public function onCustomerPostRegister(GenericEvent $event): void
    {
        $customer = $event->getSubject();

        if (!$customer instanceof CustomerInterface) {
            return;
        }

        if (!$customer->isSubscribedToNewsletter()) {
            return;
        }

        $this->mailerLiteSubscriber->subscribe($customer);
    }
}
