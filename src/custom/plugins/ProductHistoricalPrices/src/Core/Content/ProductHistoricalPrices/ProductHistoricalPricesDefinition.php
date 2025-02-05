<?php declare(strict_types=1);

namespace ProductHistoricalPrices\Core\Content\ProductHistoricalPrices;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Content\Product\ProductDefinition;

class ProductHistoricalPricesDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'product_historical_prices';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductHistoricalPricesEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductHistoricalPricesCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new PriceField('price', 'price'))->addFlags(new Required()),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', false)
        ]);
    }
}
