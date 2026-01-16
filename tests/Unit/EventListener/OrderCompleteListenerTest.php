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

namespace Tests\Sylius\MailerLitePlugin\Unit\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\MailerLitePlugin\EventListener\OrderCompleteListener;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class OrderCompleteListenerTest extends TestCase
{
    private MockObject&NewsletterSubscriberInterface $newsletterSubscriber;

    private OrderCompleteListener $listener;

    protected function setUp(): void
    {
        $this->newsletterSubscriber = $this->createMock(NewsletterSubscriberInterface::class);
        $this->listener = new OrderCompleteListener($this->newsletterSubscriber);
    }

    public function testSubscribesCustomerWhenSubscribedToNewsletter(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('isSubscribedToNewsletter')->willReturn(true);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $event = new GenericEvent($order);

        $this->newsletterSubscriber
            ->expects($this->once())
            ->method('subscribe')
            ->with($customer);

        $this->listener->onOrderComplete($event);
    }

    public function testDoesNotSubscribeWhenNotSubscribedToNewsletter(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('isSubscribedToNewsletter')->willReturn(false);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $event = new GenericEvent($order);

        $this->newsletterSubscriber
            ->expects($this->never())
            ->method('subscribe');

        $this->listener->onOrderComplete($event);
    }

    public function testIgnoresNonOrderSubjects(): void
    {
        $event = new GenericEvent(new \stdClass());

        $this->newsletterSubscriber
            ->expects($this->never())
            ->method('subscribe');

        $this->listener->onOrderComplete($event);
    }

    public function testIgnoresOrdersWithoutCustomer(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn(null);

        $event = new GenericEvent($order);

        $this->newsletterSubscriber
            ->expects($this->never())
            ->method('subscribe');

        $this->listener->onOrderComplete($event);
    }
}
