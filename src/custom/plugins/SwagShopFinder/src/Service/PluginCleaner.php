<?php declare(strict_types=1);

namespace SwagShopFinder\Service;

use Doctrine\DBAL\Connection;
use SwagShopFinder\Core\Content\ShopFinder\ShopFinderDefinition;

class PluginCleaner
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function execute(): void
    {
        $this->connection->executeStatement('DROP TABLE ' . ShopFinderDefinition::ENTITY_NAME);
    }
}
