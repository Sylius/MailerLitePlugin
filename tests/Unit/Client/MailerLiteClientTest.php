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

    public function testPostSendsCorrectRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);
        $response->method('toArray')->willReturn(['id' => '123']);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://connect.mailerlite.com/api/subscribers',
                $this->callback(function (array $options): bool {
                    $this->assertSame('Bearer test-api-key', $options['headers']['Authorization']);
                    $this->assertSame('application/json', $options['headers']['Content-Type']);
                    $this->assertSame(['email' => 'john@example.com'], $options['json']);

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

        $result = $client->post('/subscribers', ['email' => 'john@example.com']);

        $this->assertSame(201, $result['status_code']);
        $this->assertSame(['id' => '123'], $result['data']);
    }

    public function testGetSendsCorrectRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn(['subscribers' => []]);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://connect.mailerlite.com/api/subscribers',
                $this->callback(function (array $options): bool {
                    $this->assertSame('Bearer test-api-key', $options['headers']['Authorization']);
                    $this->assertArrayNotHasKey('json', $options);

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

        $result = $client->get('/subscribers');

        $this->assertSame(200, $result['status_code']);
    }

    public function testIsConfiguredReturnsTrueWhenApiKeySet(): void
    {
        $client = new MailerLiteClient(
            $this->httpClient,
            'test-api-key',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $this->assertTrue($client->isConfigured());
    }

    public function testIsConfiguredReturnsFalseWhenApiKeyEmpty(): void
    {
        $client = new MailerLiteClient(
            $this->httpClient,
            '',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $this->assertFalse($client->isConfigured());
    }

    public function testPostLogsErrorOnApiFailure(): void
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
                $this->callback(function (array $context): bool {
                    $this->assertSame('POST', $context['method']);
                    $this->assertSame('/subscribers', $context['endpoint']);
                    $this->assertSame(422, $context['status_code']);

                    return true;
                }),
            );

        $client = new MailerLiteClient(
            $this->httpClient,
            'test-api-key',
            'https://connect.mailerlite.com/api',
            $this->logger,
        );

        $result = $client->post('/subscribers', ['email' => 'invalid']);

        $this->assertSame(422, $result['status_code']);
        $this->assertArrayHasKey('error', $result);
    }
}
