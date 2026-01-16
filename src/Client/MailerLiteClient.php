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

namespace Sylius\MailerLitePlugin\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MailerLiteClient implements MailerLiteClientInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $apiUrl,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function post(string $endpoint, array $data): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ];

        if ($data !== []) {
            $options['json'] = $data;
        }

        try {
            $response = $this->httpClient->request($method, $this->apiUrl . $endpoint, $options);

            return [
                'status_code' => $response->getStatusCode(),
                'data' => $response->toArray(false),
            ];
        } catch (HttpExceptionInterface $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $content = $e->getResponse()->getContent(false);

            $this->logger->error('[MailerLite] API error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
                'response' => $content,
            ]);

            return [
                'status_code' => $statusCode,
                'error' => $content,
            ];
        }
    }
}
