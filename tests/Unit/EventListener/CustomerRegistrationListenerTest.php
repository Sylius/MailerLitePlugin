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
use Sylius\MailerLitePlugin\EventListener\CustomerRegistrationListener;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CustomerRegistrationListenerTest extends TestCase
{
    private MockObject&NewsletterSubscriberInterface $newsletterSubscriber;

    private CustomerRegistrationListener $listener;

    protected function setUp(): void
    {
        $this->newsletterSubscriber = $this->createMock(NewsletterSubscriberInterface::class);
        $this->listener = new CustomerRegistrationListener($this->newsletterSubscriber);
    }

    public function testSubscribesCustomerWhenNewsletterOptedIn(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('isSubscribedToNewsletter')->willReturn(true);

        $event = new GenericEvent($customer);

        $this->newsletterSubscriber
            ->expects($this->once())
            ->method('subscribe')
            ->with($customer);

        $this->listener->onCustomerPostRegister($event);
    }

    public function testDoesNotSubscribeWhenNewsletterNotOptedIn(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('isSubscribedToNewsletter')->willReturn(false);

        $event = new GenericEvent($customer);

        $this->newsletterSubscriber
            ->expects($this->never())
            ->method('subscribe');

        $this->listener->onCustomerPostRegister($event);
    }

    public function testIgnoresNonCustomerSubjects(): void
    {
        $event = new GenericEvent(new \stdClass());

        $this->newsletterSubscriber
            ->expects($this->never())
            ->method('subscribe');

        $this->listener->onCustomerPostRegister($event);
    }
}
