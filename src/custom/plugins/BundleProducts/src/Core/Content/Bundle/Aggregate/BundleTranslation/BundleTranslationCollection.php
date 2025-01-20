<?php declare(strict_types=1);

namespace BundleProducts\Core\Content\Bundle\Aggregate\BundleTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class BundleTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BundleTranslationEntity::class;
    }
}
