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

namespace Sylius\MailerLitePlugin\Controller;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\MailerLitePlugin\Handler\CheckoutNewsletterSubscriptionHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NewsletterSubscriptionController
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CheckoutNewsletterSubscriptionHandlerInterface $subscriptionHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function subscribeAction(Request $request, string $orderToken): Response
    {
        $token = new CsrfToken('newsletter_subscribe', $request->request->get('_token'));

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new NotFoundHttpException();
        }

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($orderToken);

        if ($order === null) {
            throw new NotFoundHttpException();
        }

        $this->subscriptionHandler->handle($order);

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('success', $this->translator->trans('sylius_mailerlite.newsletter_subscribed'));

        return new RedirectResponse(
            $this->urlGenerator->generate('sylius_shop_order_thank_you'),
        );
    }
}
