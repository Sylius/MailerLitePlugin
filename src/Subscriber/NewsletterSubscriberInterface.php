<?php

declare(strict_types=1);

namespace Sylius\MailerLitePlugin\Subscriber;

use Sylius\Component\Core\Model\CustomerInterface;

interface NewsletterSubscriberInterface
{
    public function subscribe(CustomerInterface $customer): void;
}
