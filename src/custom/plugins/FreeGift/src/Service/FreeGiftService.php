<?php declare(strict_types=1);

namespace FreeGift\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Psr\Log\LoggerInterface;

class FreeGiftService
{
    private const string CONFIGURATION_KEY = 'FreeGift.config.freeGiftProduct';

    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function assignFreeGift(string $customerId, Context $context): void
    {
        $giftProductId = $this->systemConfigService->get(self::CONFIGURATION_KEY);
        
        if (!$giftProductId) {
            $this->logger->debug('###[FREE_REGISTRATION_GIFT] NO AVAILABLE FREE REGISTRATION GIFT');
            return;
        }

        $this->customerRepository->update([
            [
                'id' => $customerId,
                'customFields' => [
                    'is_received_registration_gift' => false,
                    'is_allocated_registration_gift' => true
                ],
            ]
        ], $context);

        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Customer respositry updated',
            [
                'customer_id' => $customerId,
                'is_received_registration_gift' => false,
                'is_allocated_registration_gift' => true,
                'product_id' => $giftProductId
            ]
        );
    }

    public function getFreeGiftProductId(): ?string
    {
        return $this->systemConfigService->get(self::CONFIGURATION_KEY);
    }
}