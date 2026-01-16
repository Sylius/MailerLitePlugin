<?php

declare(strict_types=1);

namespace Tests\Sylius\MailerLitePlugin\Unit\Client;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\MailerLitePlugin\Client\MailerLiteClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class MailerLiteClientTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testAddSubscriberSendsCorrectRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://connect.mailerlite.com/api/subscribers',
                $this->callback(function (array $options): bool {
                    $this->assertSame('Bearer test-api-key', $options['headers']['Authorization']);
                    $this->assertSame('application/json', $options['headers']['Content-Type']);
                    $this->assertSame('john@example.com', $options['json']['email']);
                    $this->assertSame('John', $options['json']['fields']['name']);
                    $this->assertSame('Doe', $options['json']['fields']['last_name']);

                    return true;
                }),
            )
            ->willReturn($response);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('[MailerLite] Subscriber created', ['email' => 'john@example.com']);

        $client = new MailerLiteClient(
            $this->httpClient,
            'test-api-key',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $client->addSubscriber('john@example.com', 'John', 'Doe');
    }

    public function testAddSubscriberWithEmailOnly(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://connect.mailerlite.com/api/subscribers',
                $this->callback(function (array $options): bool {
                    $this->assertSame('john@example.com', $options['json']['email']);
                    $this->assertArrayNotHasKey('fields', $options['json']);

                    return true;
                }),
            )
            ->willReturn($response);

        $client = new MailerLiteClient(
            $this->httpClient,
            'test-api-key',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $client->addSubscriber('john@example.com');
    }

    public function testAddSubscriberLogsUpdateWhenSubscriberExists(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('[MailerLite] Subscriber updated', ['email' => 'john@example.com']);

        $client = new MailerLiteClient(
            $this->httpClient,
            'test-api-key',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $client->addSubscriber('john@example.com');
    }

    public function testAddSubscriberSkipsWhenApiKeyEmpty(): void
    {
        $this->httpClient
            ->expects($this->never())
            ->method('request');

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('[MailerLite] API key not configured, skipping subscriber sync');

        $client = new MailerLiteClient(
            $this->httpClient,
            '',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $client->addSubscriber('john@example.com');
    }

    public function testAddSubscriberLogsErrorOnApiFailure(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(422);
        $response->method('getContent')->willReturn('{"message":"Invalid email"}');

        $exception = $this->createMock(ClientExceptionInterface::class);
        $exception->method('getResponse')->willReturn($response);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                '[MailerLite] API error',
                [
                    'email' => 'invalid-email',
                    'status_code' => 422,
                    'response' => '{"message":"Invalid email"}',
                ],
            );

        $client = new MailerLiteClient(
            $this->httpClient,
            'test-api-key',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $client->addSubscriber('invalid-email');
    }
}
