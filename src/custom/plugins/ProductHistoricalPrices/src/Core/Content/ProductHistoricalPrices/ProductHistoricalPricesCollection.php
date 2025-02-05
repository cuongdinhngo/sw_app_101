<?php declare(strict_types=1);

namespace ProductHistoricalPrices\Core\Content\ProductHistoricalPrices;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ProductHistoricalPricesEntity $entity)
 * @method void set(string $key, ProductHistoricalPricesEntity $entity)
 * @method ProductHistoricalPricesEntity[] getIterator()
 * @method ProductHistoricalPricesEntity[] getElements()
 * @method ProductHistoricalPricesEntity|null get(string $key)
 * @method ProductHistoricalPricesEntity|null first()
 * @method ProductHistoricalPricesEntity|null last()
 */
class ProductHistoricalPricesCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductHistoricalPricesEntity::class;
    }
}
