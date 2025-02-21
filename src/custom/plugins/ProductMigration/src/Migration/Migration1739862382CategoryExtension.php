<?php declare(strict_types=1);

namespace ProductMigration\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1739862382CategoryExtension extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1739862382;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            ALTER TABLE category
            ADD COLUMN category_identifier VARCHAR(255) NULL,
            ADD COLUMN parent_category_identifier VARCHAR(255) NULL;
        ");
    }
}
