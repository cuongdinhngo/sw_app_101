<?php declare(strict_types=1);

namespace ProductMigration\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Content\Category\CategoryDefinition;

class CategoryExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new StringField('category_identifier', 'categoryIdentifier'),
            new StringField('parent_category_identifier', 'parentCategoryIdentifier'),
        );
    }

    public function getDefinitionClass(): string
    {
        return CategoryDefinition::class;
    }
}