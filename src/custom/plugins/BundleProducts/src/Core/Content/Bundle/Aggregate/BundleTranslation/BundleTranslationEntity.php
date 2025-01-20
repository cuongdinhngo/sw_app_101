<?php declare(strict_types=1);

namespace BundleProducts\Core\Content\Bundle\Aggregate\BundleTranslation;

use BundleProducts\Core\Content\Bundle\BundleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class BundleTranslationEntity extends TranslationEntity
{
    protected string $bundleId;

    protected string $name;

    protected BundleEntity $bundle;

    public function getBundleId(): string
    {
        return $this->bundleId;
    }

    public function setBundleId(string $bundleId): void
    {
        $this->bundleId = $bundleId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBundle(): BundleEntity
    {
        return $this->bundle;
    }

    public function setBundle(BundleEntity $bundle): void
    {
        $this->bundle = $bundle;
    }
}
