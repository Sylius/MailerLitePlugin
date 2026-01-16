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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderCompleteListener
{
    public function __construct(
        private readonly NewsletterSubscriberInterface $newsletterSubscriber,
    ) {
    }

    public function onOrderComplete(GenericEvent $event): void
    {
        $order = $event->getSubject();

        if (!$order instanceof OrderInterface) {
            return;
        }

        $customer = $order->getCustomer();

        if (!$customer instanceof CustomerInterface) {
            return;
        }

        if (!$customer->isSubscribedToNewsletter()) {
            return;
        }

        $this->newsletterSubscriber->subscribe($customer);
    }
}
