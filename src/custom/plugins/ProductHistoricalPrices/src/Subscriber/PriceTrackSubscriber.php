<?php declare(strict_types=1);

namespace ProductHistoricalPrices\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\ProductEvents;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

class PriceTrackSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Connection $connection
    ) {

    }

    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductTrackPrice'
        ];
    }

    public function onProductTrackPrice(EntityWrittenEvent $event)
    {
        $this->logger->info('#### PriceTrackSubscriber');
        $payloads = $event->getPayloads();
        foreach ($payloads as $payload) {
            $this->logger->info(json_encode($payload));
            if (!isset($payload['id'], $payload['price'])) {
                continue;
            }

            $this->logger->info('Price tracking ...');
            $this->logger->info(json_encode($payload));

            $productId = $payload['id'];
            $newPrice = null;
            if ($payload['price'] instanceof PriceCollection) {
                $price = $payload['price']->getCurrencyPrice(Defaults::CURRENCY);
                if ($price) {
                    $newPrice = $price->getGross();
                    $this->logger->info('NEW PRICE ...', ['newPrice' => $newPrice]);
                }
            }

            $query = $this->connection->createQueryBuilder()
                ->select('price')
                ->from('product_historical_prices')
                ->where('product_id = :product_id')
                ->setParameter('product_id', $productId)
                ->orderBy('timestamp', 'DESC')
                ->setMaxResults(1)
                ->execute();

            $oldPrice = $query->fetchOne();

            // If the price changed, insert a new record
            if (
                ($oldPrice !== false && (float)$oldPrice !== (float)$newPrice) ||
                empty($oldPrice)
            ) {
                $this->connection->insert('product_historical_prices', [
                    'id' => Uuid::randomBytes(), // Generate a UUID
                    'product_id' => Uuid::fromHexToBytes($productId),
                    'price' => $newPrice,
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
