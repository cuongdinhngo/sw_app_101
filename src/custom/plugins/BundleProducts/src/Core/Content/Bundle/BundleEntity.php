<?php declare(strict_types=1);

namespace BundleProducts\Core\Content\Bundle;

use BundleProducts\Core\Content\Bundle\Aggregate\BundleTranslation\BundleTranslationCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class BundleEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name;

    protected string $discountType;

    protected float $discount;

    protected ?BundleTranslationCollection $translations = null;

    protected ?ProductCollection $products = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDiscountType(): string
    {
        return $this->discountType;
    }

    public function setDiscountType(string $discountType): void
    {
        $this->discountType = $discountType;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function getTranslations(): ?BundleTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(BundleTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }

    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}
