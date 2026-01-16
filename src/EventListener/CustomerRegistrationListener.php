<?php

declare(strict_types=1);

namespace Sylius\MailerLitePlugin\EventListener;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MailerLitePlugin\Subscriber\NewsletterSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class CustomerRegistrationListener
{
    public function __construct(
        private readonly NewsletterSubscriberInterface $newsletterSubscriber,
    ) {
    }

    public function onCustomerPostRegister(GenericEvent $event): void
    {
        $customer = $event->getSubject();

        if (!$customer instanceof CustomerInterface) {
            return;
        }

        if (!$customer->isSubscribedToNewsletter()) {
            return;
        }

        $this->newsletterSubscriber->subscribe($customer);
    }
}
