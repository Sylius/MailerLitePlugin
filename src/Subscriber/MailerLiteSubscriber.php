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

namespace Sylius\MailerLitePlugin\Subscriber;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;

final class MailerLiteSubscriber implements MailerLiteSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly MailerLiteClientInterface $client,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function subscribe(CustomerInterface $customer): void
    {
        if (!$this->client->isConfigured()) {
            $this->logger->warning('[MailerLite] API key not configured, skipping subscriber sync');

            return;
        }

        $email = $customer->getEmail();

        if ($email === null) {
            return;
        }

        $payload = $this->buildPayload($email, $customer->getFirstName(), $customer->getLastName());
        $response = $this->client->post('/subscribers', $payload);

        $this->logResponse($response, $email);
    }

    private function buildPayload(string $email, ?string $firstName, ?string $lastName): array
    {
        $payload = ['email' => $email];

        if ($firstName !== null || $lastName !== null) {
            $payload['fields'] = array_filter([
                'name' => $firstName,
                'last_name' => $lastName,
            ]);
        }

        return $payload;
    }

    private function logResponse(array $response, string $email): void
    {
        $statusCode = $response['status_code'];

        if ($statusCode === 201) {
            $this->logger->info('[MailerLite] Subscriber created', ['email' => $email]);
        } elseif ($statusCode === 200) {
            $this->logger->info('[MailerLite] Subscriber updated', ['email' => $email]);
        }
    }
}
