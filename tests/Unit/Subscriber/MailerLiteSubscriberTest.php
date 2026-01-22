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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;
use Sylius\MailerLitePlugin\Subscriber\MailerLiteSubscriber;

final class MailerLiteSubscriberTest extends TestCase
{
    private MailerLiteClientInterface&MockObject $client;

    private LoggerInterface&MockObject $logger;

    private MailerLiteSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->client = $this->createMock(MailerLiteClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->subscriber = new MailerLiteSubscriber($this->client, $this->logger);
    }

    public function testSubscribeCallsClientWithCorrectPayload(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('john@example.com');
        $customer->method('getFirstName')->willReturn('John');
        $customer->method('getLastName')->willReturn('Doe');

        $this->client->method('isConfigured')->willReturn(true);

        $this->client
            ->expects($this->once())
            ->method('post')
            ->with(
                '/subscribers',
                [
                    'email' => 'john@example.com',
                    'fields' => [
                        'name' => 'John',
                        'last_name' => 'Doe',
                    ],
                ],
            )
            ->willReturn(['status_code' => 201, 'data' => []]);

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeSkipsFieldsWhenNamesAreNull(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('john@example.com');
        $customer->method('getFirstName')->willReturn(null);
        $customer->method('getLastName')->willReturn(null);

        $this->client->method('isConfigured')->willReturn(true);

        $this->client
            ->expects($this->once())
            ->method('post')
            ->with(
                '/subscribers',
                ['email' => 'john@example.com'],
            )
            ->willReturn(['status_code' => 201, 'data' => []]);

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeSkipsWhenEmailIsNull(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn(null);

        $this->client->method('isConfigured')->willReturn(true);

        $this->client
            ->expects($this->never())
            ->method('post');

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeSkipsWhenClientNotConfigured(): void
    {
        $customer = $this->createMock(CustomerInterface::class);

        $this->client->method('isConfigured')->willReturn(false);

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('[MailerLite] API key not configured, skipping subscriber sync');

        $this->client
            ->expects($this->never())
            ->method('post');

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeLogsCreatedOnStatusCode201(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('john@example.com');
        $customer->method('getFirstName')->willReturn(null);
        $customer->method('getLastName')->willReturn(null);

        $this->client->method('isConfigured')->willReturn(true);
        $this->client->method('post')->willReturn(['status_code' => 201, 'data' => []]);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('[MailerLite] Subscriber created', ['email' => 'john@example.com']);

        $this->subscriber->subscribe($customer);
    }

    public function testSubscribeLogsUpdatedOnStatusCode200(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getEmail')->willReturn('john@example.com');
        $customer->method('getFirstName')->willReturn(null);
        $customer->method('getLastName')->willReturn(null);

        $this->client->method('isConfigured')->willReturn(true);
        $this->client->method('post')->willReturn(['status_code' => 200, 'data' => []]);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('[MailerLite] Subscriber updated', ['email' => 'john@example.com']);

        $this->subscriber->subscribe($customer);
    }
}
