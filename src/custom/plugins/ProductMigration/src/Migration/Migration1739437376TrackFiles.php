<?php declare(strict_types=1);

namespace ProductMigration\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1739437376TrackFiles extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1739437376;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `track_files` (
                `id` INT  AUTO_INCREMENT,
                `file` VARCHAR(255) NOT NULL,
                `is_done` tinyint(1) NOT NULL DEFAULT '0',
                `extra_info` json DEFAULT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `started_at` DATETIME(3),
                `ended_at` DATETIME(3),
                PRIMARY KEY (`id`)
            )
                ENGINE = InnoDB
                DEFAULT CHARSET = utf8mb4
                COLLATE = utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($sql);
    }
}
