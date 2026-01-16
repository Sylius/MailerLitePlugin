<?php

declare(strict_types=1);

namespace Sylius\MailerLitePlugin\Client;

interface MailerLiteClientInterface
{
    public function addSubscriber(string $email, ?string $name = null, ?string $lastName = null): void;
}
