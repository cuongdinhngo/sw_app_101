<?php declare(strict_types=1);

namespace FreeGift\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Psr\Log\LoggerInterface;
use FreeGift\Service\FreeGiftService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class CustomCartCollector implements CartDataCollectorInterface, CartProcessorInterface
{
    private const FREE_GIFT_ITEM_ID = 'free_registration_gift';
    private const LINE_ITEM_ID = 'free_registration_gift_item';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FreeGiftService $freeGiftService,
        private readonly EntityRepository $productRepository,
        private readonly QuantityPriceCalculator $calculator
    ) {
    }

    /**
     * Collect method - Ensures free gift is preloaded in the cart
     */
    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Triggered Collector ...');

        if (empty($context->getCustomer()) || $this->isReceivedFreeRegistrationGift($context)) {
            return;
        }

        $giftProductId = $this->freeGiftService->getFreeGiftProductId();
        if (!$giftProductId) {
            $this->logger->debug('###[FREE_REGISTRATION_GIFT] No Gift Configured.');
            return;
        }

        // Check if free gift is already preloaded
        if ($data->has(self::FREE_GIFT_ITEM_ID)) {
            return;
        }

        $criteria = new Criteria([$giftProductId]);
        $product = $this->productRepository->search($criteria, $context->getContext())->first();

        if (!$product) {
            $this->logger->error('###[FREE_REGISTRATION_GIFT] Free gift product not found.', [
                'giftProductId' => $giftProductId
            ]);
            return;
        }

        // Store the product in CartDataCollection before Shopware processes the cart
        $data->set(self::FREE_GIFT_ITEM_ID, $product);

        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Preloaded Free Gift into CartDataCollection', [
            'giftProductId' => $giftProductId
        ]);
    }

    /**
     * Process method - Adds free gift to cart and ensures correct pricing
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Triggered Processor ...');

        if (empty($context->getCustomer()) || $this->isReceivedFreeRegistrationGift($context)) {
            return;
        }

        $giftProductId = null;
        if (!$data->has(self::FREE_GIFT_ITEM_ID)) {
            $this->logger->debug('###[FREE_REGISTRATION_GIFT] NO FREE GIFT in Cart Data Collection');
        } else {
            $product = $data->get(self::FREE_GIFT_ITEM_ID);
            $giftProductId = $product->getId();
        }

        // Check if the free gift is already in the cart
        foreach ($toCalculate->getLineItems() as $lineItem) {
            if ($lineItem->getId() === self::LINE_ITEM_ID) {
                $this->logger->debug('###[FREE_REGISTRATION_GIFT] Gift is already in the cart');
                return;
            }
        }

        // Create Free Gift Line Item
        $giftLineItem = $this->createLineItem($product, $context);
        // Add Free Gift to Cart
        $toCalculate->add($giftLineItem);

        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Free gift added successfully.', [
            'giftProductId' => $giftProductId,
            'cartItemId' => $giftLineItem->getId(),
            'calculatedPrice' => $giftLineItem->getPrice()->getTotalPrice(),
        ]);
    }

    private function createLineItem(ProductEntity $product, SalesChannelContext $context): LineItem
    {
        // Create Free Gift Line Item
        $giftLineItem = new LineItem(self::LINE_ITEM_ID, LineItem::PRODUCT_LINE_ITEM_TYPE, $product->getId(), 1);
        $giftLineItem->setLabel($product->getTranslation('name'));

        // Define price as 0.00 explicitly
        $taxRules = new TaxRuleCollection();
        $priceDefinition = new QuantityPriceDefinition(0.00, $taxRules, 1);
        $giftLineItem->setPriceDefinition($priceDefinition);
        $calculatedPrice = $this->calculator->calculate($priceDefinition, $context);
        $giftLineItem->setPrice($calculatedPrice);

        return $giftLineItem;
    }

    private function isReceivedFreeRegistrationGift(SalesChannelContext $context): ?bool
    {
        $customer = $context->getCustomer();

        return $customer?->getCustomFieldsValue('is_received_registration_gift');
    }
}