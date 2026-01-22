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

namespace Tests\Sylius\MailerLitePlugin\Unit\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MailerLitePlugin\Subscriber\GuestCheckoutNewsletterSubscriber;
use Sylius\MailerLitePlugin\Subscriber\MailerLiteSubscriberInterface;

final class GuestCheckoutNewsletterSubscriberTest extends TestCase
{
    private MockObject&MailerLiteSubscriberInterface $mailerLiteSubscriber;

    private MockObject&EntityManagerInterface $entityManager;

    private GuestCheckoutNewsletterSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->mailerLiteSubscriber = $this->createMock(MailerLiteSubscriberInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->subscriber = new GuestCheckoutNewsletterSubscriber(
            $this->mailerLiteSubscriber,
            $this->entityManager,
        );
    }

    public function testSubscribesCustomerAndSyncsToMailerLite(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getFirstName')->willReturn('John');
        $customer->method('getLastName')->willReturn('Doe');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getBillingAddress')->willReturn(null);

        $customer->expects($this->once())->method('setSubscribedToNewsletter')->with(true);
        $this->entityManager->expects($this->once())->method('flush');
        $this->mailerLiteSubscriber->expects($this->once())->method('subscribe')->with($customer);

        $this->subscriber->subscribe($order);
    }

    public function testPopulatesCustomerNameFromBillingAddress(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getFirstName')->willReturn(null);
        $customer->method('getLastName')->willReturn(null);

        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->method('getFirstName')->willReturn('Jane');
        $billingAddress->method('getLastName')->willReturn('Smith');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        $customer->expects($this->once())->method('setSubscribedToNewsletter')->with(true);
        $customer->expects($this->once())->method('setFirstName')->with('Jane');
        $customer->expects($this->once())->method('setLastName')->with('Smith');

        $this->entityManager->expects($this->once())->method('flush');
        $this->mailerLiteSubscriber->expects($this->once())->method('subscribe')->with($customer);

        $this->subscriber->subscribe($order);
    }

    public function testDoesNotOverwriteExistingCustomerName(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getFirstName')->willReturn('ExistingFirst');
        $customer->method('getLastName')->willReturn('ExistingLast');

        $billingAddress = $this->createMock(AddressInterface::class);
        $billingAddress->method('getFirstName')->willReturn('BillingFirst');
        $billingAddress->method('getLastName')->willReturn('BillingLast');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        $customer->expects($this->once())->method('setSubscribedToNewsletter')->with(true);
        $customer->expects($this->never())->method('setFirstName');
        $customer->expects($this->never())->method('setLastName');

        $this->subscriber->subscribe($order);
    }

    public function testDoesNothingWhenOrderHasNoCustomer(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn(null);

        $this->entityManager->expects($this->never())->method('flush');
        $this->mailerLiteSubscriber->expects($this->never())->method('subscribe');

        $this->subscriber->subscribe($order);
    }
}
