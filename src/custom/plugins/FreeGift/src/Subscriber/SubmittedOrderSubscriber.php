<?php declare(strict_types=1);

namespace FreeGift\Subscriber;

use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Content\MailTemplate\Service\MailService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

class SubmittedOrderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityRepository $customerRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Triggered Submitted Order ...');
        $context = $event->getContext();
        $order = $event->getOrder();
        $customer = $order->getOrderCustomer();

        $customerId = $customer->getCustomerId();
        $this->customerRepository->update([
            [
                'id' => $customerId,
                'customFields' => [
                    'is_received_registration_gift' => true,
                ],
            ]
        ], $context);
    }
}
