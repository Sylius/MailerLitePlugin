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

interface MailerLiteClientInterface
{
    public function post(string $endpoint, array $data): array;

    public function get(string $endpoint): array;

    public function isConfigured(): bool;
}
