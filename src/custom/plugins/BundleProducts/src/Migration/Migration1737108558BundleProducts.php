<?php declare(strict_types=1);

namespace BundleProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;

/**
 * @internal
 */
class Migration1737108558BundleProducts extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1737108558;
    }

    public function update(Connection $connection): void
    {
        $this->createBundleTable($connection);
        $this->createBundleTranslationTable($connection);
        $this->createBundleProductTable($connection);

        $this->updateInheritance($connection, 'product', 'bundles');
    }

    private function createBundleTable(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `bundle` (
    `id` BINARY(16) NOT NULL,
    `discountType` VARCHAR(255) NOT NULL,
    `discount` DOUBLE NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function createBundleTranslationTable(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `bundle_translation` (
    `name` VARCHAR(255),
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    `bundle_id` BINARY(16) NOT NULL,
    `language_id` BINARY(16) NOT NULL,
    PRIMARY KEY (`bundle_id`,`language_id`),
    KEY `fk.bundle_translation.bundle_id` (`bundle_id`),
    KEY `fk.bundle_translation.language_id` (`language_id`),
    CONSTRAINT `fk.bundle_translation.bundle_id` FOREIGN KEY (`bundle_id`) REFERENCES `bundle` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.bundle_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function createBundleProductTable(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `bundle_product` (
    `bundle_id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    PRIMARY KEY (`bundle_id`,`product_id`,`product_version_id`),
    KEY `fk.bundle_product.bundle_id` (`bundle_id`),
    KEY `fk.bundle_product.product_id` (`product_id`,`product_version_id`),
    CONSTRAINT `fk.bundle_product.bundle_id` FOREIGN KEY (`bundle_id`) REFERENCES `bundle` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.bundle_product.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
