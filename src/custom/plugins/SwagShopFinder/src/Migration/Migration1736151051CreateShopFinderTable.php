<?php declare(strict_types=1);

namespace SwagShopFinder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1736151051CreateShopFinderTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1736151051;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `swag_shop_finder` (
    `id` BINARY(16) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `active` TINYINT(1) NULL DEFAULT '0',
    `street` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) NULL,
    `telephone` VARCHAR(255) NULL,
    `open_times` LONGTEXT NULL,
    `country_id` BINARY(16) NULL,
    `latitude` DECIMAL(9,6),
    `longitude` DECIMAL(9,6),
    `created_at` DATETIME(3),
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`),
    KEY `fk.swag_shop_finder.country_id` (`country_id`),
    CONSTRAINT `fk.swag_shop_finder.country_id` FOREIGN KEY (`country_id`)
        REFERENCES `country` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE 
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
