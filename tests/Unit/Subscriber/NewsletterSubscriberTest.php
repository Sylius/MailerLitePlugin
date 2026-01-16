<?php

declare(strict_types=1);

namespace Tests\Sylius\MailerLitePlugin\Unit\Subscriber;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriber;

final class NewsletterSubscriberTest extends TestCase
{
    private MailerLiteClientInterface&MockObject $client;

    private NewsletterSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->client = $this->createMock(MailerLiteClientInterface::class);
        $this->subscriber = new NewsletterSubscriber($this->client);
    }

    public function testSubscribeCallsClientWithCustomerData(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('john@example.com');
        $customer->method('getFirstName')->willReturn('John');
        $customer->method('getLastName')->willReturn('Doe');

        $this->client
            ->expects($this->once())
            ->method('addSubscriber')
            ->with('john@example.com', 'John', 'Doe');

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeSkipsWhenEmailIsNull(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn(null);

        $this->client
            ->expects($this->never())
            ->method('addSubscriber');

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeWorksWithNullNames(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('john@example.com');
        $customer->method('getFirstName')->willReturn(null);
        $customer->method('getLastName')->willReturn(null);

        $this->client
            ->expects($this->once())
            ->method('addSubscriber')
            ->with('john@example.com', null, null);

        $this->subscriber->subscribe($customer);
    }
}
