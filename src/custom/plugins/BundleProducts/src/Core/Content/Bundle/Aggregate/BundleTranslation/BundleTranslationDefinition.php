<?php declare(strict_types=1);

namespace BundleProducts\Core\Content\Bundle\Aggregate\BundleTranslation;

use BundleProducts\Core\Content\Bundle\BundleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BundleTranslationDefinition extends EntityTranslationDefinition
{
    public function getEntityName(): string
    {
        return 'bundle_translation';
    }

    public function getCollectionClass(): string
    {
        return BundleTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return BundleTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return BundleDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required()),
        ]);
    }
}
