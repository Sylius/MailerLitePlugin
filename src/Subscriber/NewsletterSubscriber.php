<?php

declare(strict_types=1);

namespace Sylius\MailerLitePlugin\Subscriber;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MailerLitePlugin\Client\MailerLiteClientInterface;

final class NewsletterSubscriber implements NewsletterSubscriberInterface
{
    public function __construct(
        private readonly MailerLiteClientInterface $client,
    ) {
    }

    public function subscribe(CustomerInterface $customer): void
    {
        $email = $customer->getEmail();

        if ($email === null) {
            return;
        }

        $this->client->addSubscriber(
            $email,
            $customer->getFirstName(),
            $customer->getLastName(),
        );
    }
}
