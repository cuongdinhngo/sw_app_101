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
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductHistoricalPricesPageSubscriber implements EventSubscriberInterface
{
    private int $daysRange = 21;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Connection $connection,
        private readonly RequestStack $requestStack
    ) {

    }

    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            ProductPageLoadedEvent::class => 'onProductHistoricalPricesLoaded'
        ];
    }

    public function onProductHistoricalPricesLoaded(ProductPageLoadedEvent $event)
    {
        $this->logger->info('#### ProductHistoricalPricesPageSubscriber .....');
        $salesChannelProduct = $event->getPage()->getProduct();
        $productId = $salesChannelProduct->getParentId();
        $request = $this->requestStack->getCurrentRequest();
        $days = $request->query->getInt('days', $this->daysRange);
        $this->logger->info('@@@ ProductId', ['productId' => $productId, 'days' => $days]);

        $event->getPage()->assign([
            'historicalPrices' => $this->getHistoricalPrices($productId, $days),
            'cheapestPrice' => $this->getCheapestPrice($productId, $days),
            'daysRange' => $days
        ]);
    }

    private function getCheapestPrice(string $productId, int $days): ?string
    {
        $query = $this->connection->createQueryBuilder()
            ->select('MIN(price) as cheapest_price')
            ->from('product_historical_prices')
            ->where('product_id = :product_id')
            ->andWhere('timestamp >= NOW() - INTERVAL :days DAY')
            ->setParameter('product_id', Uuid::fromHexToBytes($productId))
            ->setParameter('days', $days)
            ->execute();

        return $query->fetchOne() ?: null;
    }

    private function getHistoricalPrices(string $productId, ?int $days): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('price, timestamp')
            ->from('product_historical_prices')
            ->where('product_id = :product_id')
            ->andWhere('timestamp >= NOW() - INTERVAL :days DAY')
            ->setParameter('product_id', Uuid::fromHexToBytes($productId))
            ->setParameter('days', $days)
            ->orderBy('timestamp', 'DESC')
            ->execute();

        return $query->fetchAllAssociative();
    }
}
