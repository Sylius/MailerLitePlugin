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

namespace Sylius\MailerLitePlugin\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;

final class CheckoutNewsletterSubscriptionHandler implements CheckoutNewsletterSubscriptionHandlerInterface
{
    public function __construct(
        private readonly NewsletterSubscriberInterface $newsletterSubscriber,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(OrderInterface $order): void
    {
        $customer = $order->getCustomer();

        if (!$customer instanceof CustomerInterface) {
            return;
        }

        $customer->setSubscribedToNewsletter(true);

        $this->populateCustomerNameFromBillingAddress($order, $customer);

        $this->entityManager->flush();

        $this->newsletterSubscriber->subscribe($customer);
    }

    private function populateCustomerNameFromBillingAddress(OrderInterface $order, CustomerInterface $customer): void
    {
        $billingAddress = $order->getBillingAddress();

        if ($billingAddress === null) {
            return;
        }

        if ($customer->getFirstName() === null && $billingAddress->getFirstName() !== null) {
            $customer->setFirstName($billingAddress->getFirstName());
        }

        if ($customer->getLastName() === null && $billingAddress->getLastName() !== null) {
            $customer->setLastName($billingAddress->getLastName());
        }
    }
}
