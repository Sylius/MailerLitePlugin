<?php

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

    public function addSubscriber(string $email, ?string $name = null, ?string $lastName = null): void
    {
        if ($this->apiKey === '') {
            $this->logger->warning('[MailerLite] API key not configured, skipping subscriber sync');

            return;
        }

        $data = ['email' => $email];

        if ($name !== null || $lastName !== null) {
            $data['fields'] = array_filter([
                'name' => $name,
                'last_name' => $lastName,
            ]);
        }

        try {
            $response = $this->httpClient->request('POST', $this->apiUrl . '/subscribers', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 201) {
                $this->logger->info('[MailerLite] Subscriber created', ['email' => $email]);
            } elseif ($statusCode === 200) {
                $this->logger->info('[MailerLite] Subscriber updated', ['email' => $email]);
            }
        } catch (HttpExceptionInterface $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $content = $e->getResponse()->getContent(false);

            $this->logger->error('[MailerLite] API error', [
                'email' => $email,
                'status_code' => $statusCode,
                'response' => $content,
            ]);
        }
    }
}
