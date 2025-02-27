<?php declare(strict_types=1);

namespace ProductMigration\Service;

use Doctrine\DBAL\Connection;
use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Content\Category\CategoryDefinition;

class CategoryLoader
{
    use DataTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function loadAllCategories(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $sql = '
            SELECT LOWER(HEX(c.id)) as category_id, c.category_identifier, LOWER(HEX(c.parent_id)) as parent_id, c.parent_category_identifier, ct.name
            FROM category c
            JOIN category_translation ct ON c.id = ct.category_id
            ORDER BY ct.name
        ';
        $categories = $this->connection->executeQuery($sql)->fetchAllAssociative();
        $data = [];
        foreach ($categories as $category) {
            $key = $category['category_identifier'] ?? $category['name'];
            $data[$key] = [
                'categoryId' => $category['category_id'],
                'categoryIdentifier' => $category['category_identifier'],
                'parentCategoryId' => $category['parent_id'],
                'parentCategoryIdentifier' => $category['parent_category_identifier'],
            ];
        }

        $this->data = $data;
        unset($data, $categories);
    }

    public function mapCategories(array $importedCategory): array
    {
        $category = $this->getValueByKey($importedCategory['categoryIdentifier']);
        if ($category) {
            $categoryId = $category['categoryId'];
        } else {
            $newCategory = $this->handleNewCategory($importedCategory);
            $categoryId = $newCategory['categoryId'];
            $parentCategoryId = $newCategory['parentCategoryId'];
            $newCategory = [
                'categoryId' => $categoryId,
                'categoryIdentifier' => $importedCategory['categoryIdentifier'],
                'parentCategoryId' => $parentCategoryId,
                'parentCategoryIdentifier' => $importedCategory['parentCategoryIdentifier'],
            ];
            $this->add($importedCategory['categoryIdentifier'], $newCategory);
            if (!empty($importedCategory['parentCategoryIdentifier'])) {
                $newCategory = [
                    'categoryId' => $parentCategoryId,
                    'categoryIdentifier' => $importedCategory['parentCategoryIdentifier'],
                    'parentCategoryId' => null,
                    'parentCategoryIdentifier' => null,
                ];
                $this->add($importedCategory['categoryIdentifier'], $newCategory);
            }

            unset($newCategory);
            //category_id is existed but no name => that category_id have been created => update name in category_translation

        }

        return [['id' => $categoryId]];
    }

    private function handleNewCategory(array $data): array
    {
        $this->connection->beginTransaction();
        try {
            $parentId = null;
            $categoryId = Uuid::randomBytes();
            $languageId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
            $versionId = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);
            if (!empty($data['parentCategoryIdentifier'])) {
                //existed parent_id
                if ($this->getValueByKey($data['parentCategoryIdentifier'])) {
                    $parentId = Uuid::fromHexToBytes($this->getValueByKey($data['parentCategoryIdentifier'])['categoryId']);
                } else {
                    $parentId = Uuid::randomBytes();
                    $parentCategoryTranslationData = [
                        'id' => $parentId,
                        'versionId' => $versionId,
                        'languageId' => $languageId,
                        'categoryName' => $data['parentCategoryIdentifier'],
                        'createdAt' => $this->getFormatedCurrentDateTime()
                    ];
                    $parentCategoryData = [
                        'id' => $parentId,
                        'versionId' => $versionId,
                        'parentId' => null,
                        'categoryIdentifier' => $data['parentCategoryIdentifier'],
                        'parentCategoryIdentifier' => null,
                        'type' => CategoryDefinition::TYPE_PAGE,
                        'createdAt' => $this->getFormatedCurrentDateTime()
                    ];
                    $this->createNewCategory($parentCategoryTranslationData, $parentCategoryData);
                    $parentId = Uuid::fromBytesToHex($parentId);

                    unset($parentCategoryTranslationData, $parentCategoryData);
                }
            }
            $categoryTranslationData = [
                'id' => $categoryId,
                'versionId' => $versionId,
                'languageId' => $languageId,
                'categoryName' => $data['name'],
                'type' => CategoryDefinition::TYPE_PAGE,
                'createdAt' => $this->getFormatedCurrentDateTime()
            ];
            $categoryData = [
                'id' => $categoryId,
                'versionId' => $versionId,
                'parentId' => $parentId,
                'categoryIdentifier' => $data['categoryIdentifier'],
                'parentCategoryIdentifier' => $data['parentCategoryIdentifier'] ?? null,
                'type' => CategoryDefinition::TYPE_PAGE,
                'createdAt' => $this->getFormatedCurrentDateTime()
            ];
            $this->createNewCategory($categoryTranslationData, $categoryData);

            $this->connection->commit();

            $newIds = [
                'categoryId' => Uuid::fromBytesToHex($categoryId),
                'parentCategoryId' => Uuid::fromBytesToHex($parentId),
            ];
            unset($categoryTranslationData, $categoryData);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $newIds = [
                'categoryId' => null,
                'parentCategoryId' => null,
            ];
        }

        return $newIds;
    }

    private function createNewCategory(array $categoryTranslationData, array $categoryData): void
    {
        $query = '
            INSERT INTO category(`id`, `version_id`, `parent_id`, `category_identifier`, `parent_category_identifier`, `type`, `created_at`)
            VALUES(:id, :versionId, :parentId, :categoryIdentifier, :parentCategoryIdentifier, :type, :createdAt)
        ';
        $this->connection->executeStatement(
            $query,
            $categoryData
        );
        $this->connection->executeStatement(
            'INSERT INTO category_translation(`category_id`, `category_version_id`, `language_id`, `name`, `created_at`)
            VALUES(:id, :versionId, :languageId, :categoryName, :createdAt)',
            $categoryTranslationData
        );
    }
}