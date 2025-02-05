<?php declare(strict_types=1);

namespace ProductHistoricalPrices\Core\Content\ProductHistoricalPrices;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Content\Product\ProductEntity;

class ProductHistoricalPricesEntity extends Entity
{
    use EntityIdTrait;

    protected ?ProductEntity $product;

    protected ?float $price;

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getPrice(): ?price
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }
}
