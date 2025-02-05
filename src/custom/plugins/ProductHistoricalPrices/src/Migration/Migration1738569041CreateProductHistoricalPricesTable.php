<?php declare(strict_types=1);

namespace ProductHistoricalPrices\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1738569041CreateProductHistoricalPricesTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1738569041;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `product_historical_prices` (
                `id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NOT NULL,
                `price` FLOAT NOT NULL,
                `timestamp` DATETIME(3) NOT NULL,
                PRIMARY KEY (`id`, `product_id`),
                KEY `fk.product_historical_prices.product_id` (`product_id`),
                CONSTRAINT `fk.product_historical_prices.product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
                ENGINE = InnoDB
                DEFAULT CHARSET = utf8mb4
                COLLATE = utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
