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

namespace Sylius\MailerLitePlugin\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Checkout\AddressType;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CheckoutAddressTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
                $form = $event->getForm();
                $order = $event->getData();

                if (!$order instanceof OrderInterface) {
                    return;
                }

                $customer = $order->getCustomer();
                $isAlreadySubscribed = $customer instanceof CustomerInterface && $customer->isSubscribedToNewsletter();

                if ($isAlreadySubscribed) {
                    return;
                }

                $form->add('subscribedToNewsletter', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'sylius_mailerlite.form.checkout.subscribe_to_newsletter',
                ]);
            })
            ->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event): void {
                $form = $event->getForm();
                $order = $event->getData();

                if (!$order instanceof OrderInterface) {
                    return;
                }

                if (!$form->has('subscribedToNewsletter')) {
                    return;
                }

                $subscribedToNewsletter = $form->get('subscribedToNewsletter')->getData();

                if ($subscribedToNewsletter !== true) {
                    return;
                }

                $customer = $order->getCustomer();

                if ($customer instanceof CustomerInterface) {
                    $customer->setSubscribedToNewsletter(true);
                }
            })
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [AddressType::class];
    }
}
