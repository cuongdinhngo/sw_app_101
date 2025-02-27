<?php declare(strict_types=1);

namespace ProductMigration\Service;

use ProductMigration\Service\Trait\DataTrait;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

class PropertyGroupOptionLoader
{
    use DataTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function loadPropertyGroupOption(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $query = '
            SELECT LOWER(HEX(pgo.id)) as property_group_option_id, LOWER(HEX(pg.id)) as property_group_id, pgt.name as property_group_translation_name, pgot.name as property_group_option_translation_name
            FROM property_group_option pgo
            JOIN property_group pg ON pgo.property_group_id = pg.id
            JOIN property_group_translation pgt ON pg.id = pgt.property_group_id
            JOIN property_group_option_translation pgot ON pgot.property_group_option_id = pgo.id
            WHERE pgot.language_id = UNHEX(:languageId)
            AND pgt.language_id = UNHEX(:languageId)
            ORDER BY property_group_translation_name
        ';
        $data = $this->connection
            ->executeQuery($query, ['languageId' => Defaults::LANGUAGE_SYSTEM])
            ->fetchAllAssociative();

        $result = [];
        foreach ($data as $item) {
            $key = $item['property_group_translation_name'] . ':' . $item['property_group_option_translation_name'];
            unset($item['property_group_translation_name'], $item['property_group_option_translation_name']);
            $result[$key] = $item;
        }

        $this->data = $result;
        unset($result, $data);
    }

    public function mapProductProperty(string $importedProductProperties): array
    {
        $result = [];
        foreach (explode(';', $importedProductProperties) as $item) {
            $productProperty = $this->getValueByKey($item);
            if ($productProperty) {
                $propertyGroupOptionId = $productProperty['property_group_option_id'];
                $propertyGroupId = $productProperty['property_group_id'];
            } else {
                [$propertyGroupTranslationValue, $productPropertyOptionTranslationValue] = explode(':', $item);
                $newOption = $this->getNewPropertyGroupOptionId($propertyGroupTranslationValue, $productPropertyOptionTranslationValue);
                $propertyGroupId = $newOption['propertyGroupId'] ?? null;
                $propertyGroupOptionId = $newOption['propertyGroupOptionId'] ?? null;
                if ($propertyGroupOptionId) {
                    $this->add(
                        $item,
                        [
                            'property_group_id' => $propertyGroupId,
                            'property_group_option_id' => $propertyGroupOptionId
                        ]
                    );
                }
            }

            $result[] = ['id' => $propertyGroupOptionId];
        }

        return $result;
    }

    private function getNewPropertyGroupOptionId(string $propertyGroupTranslationValue, string $productPropertyOptionTranslationValue): array
    {
        $propertyGroupId = null;
        foreach ($this->data as $key => $value) {
            if ($propertyGroupTranslationValue === $value['property_group_translation_name']) {
                $propertyGroupId = $value['property_group_id'];
                break;
            }
        }

        return $this->createNewPropertGroupOption($propertyGroupTranslationValue, $productPropertyOptionTranslationValue, $propertyGroupId);
    }

    private function createNewPropertGroupOption(string $propertyGroupTranslationValue, string $productPropertyOptionTranslationValue, ?string $existedPropertyGroupId = null): array
    {
        $propertyGroupId = $existedPropertyGroupId ? Uuid::fromHexToBytes($existedPropertyGroupId) : Uuid::randomBytes();
        $propertyGroupOptionId = Uuid::randomBytes();
        $languageEn = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $time = $this->getFormatedCurrentDateTime();
        $this->connection->beginTransaction();
        try {
            if (null === $existedPropertyGroupId) {
                $this->connection->executeStatement(
                    'INSERT INTO property_group(`id`, `display_type`, `sorting_type`, `created_at`)
                    VALUES(:id, :displayType, :sortingType, :createdAt)',
                    [
                        'id' => $propertyGroupId,
                        'displayType' => 'text',
                        'sortingType' => 'alphanumeric',
                        'createdAt' => $time
                    ]
                );
                $this->connection->executeStatement(
                    'INSERT INTO property_group_translation(`property_group_id`, `name`, `language_id`, `created_at`)
                    VALUES(:propertyGroupId, :groupTranslationValue, :languageId, :createdAt)',
                    [
                        'propertyGroupId' => $propertyGroupId,
                        'groupTranslationValue' => $propertyGroupTranslationValue,
                        'languageId' => $languageEn,
                        'createdAt' => $time
                    ]
                );
            }

            $option = [
                'id' => $propertyGroupOptionId,
                'property_group_id' => $propertyGroupId,
                'created_at' => $this->getFormatedCurrentDateTime()
            ];
            $this->connection->insert('property_group_option', $option);

            $optionTranslation = [
                'property_group_option_id' => $propertyGroupOptionId,
                'name' => $productPropertyOptionTranslationValue, 
                'language_id' => $languageEn,
                'created_at' => $this->getFormatedCurrentDateTime()
            ];
            $this->connection->insert('property_group_option_translation', $optionTranslation);
            
            $this->connection->commit();

            $newIds = [
                'propertyGroupId' => Uuid::fromBytesToHex($propertyGroupId),
                'propertyGroupOptionId' => Uuid::fromBytesToHex($propertyGroupOptionId)
            ];

            unset($option, $optionTranslation, $propertyGroupId, $propertyGroupOptionId, $languageEn);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $newIds = [
                'propertyGroupId' => null,
                'propertyGroupOptionId' => null
            ];
        }

        return $newIds;
    }
}