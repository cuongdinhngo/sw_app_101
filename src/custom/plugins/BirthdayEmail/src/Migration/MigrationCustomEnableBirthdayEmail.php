<?php declare(strict_types=1);

namespace BirthdayEmail\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class MigrationCustomEnableBirthdayEmail extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1737603770;
    }

    public function update(Connection $connection): void
    {
        $setId = Uuid::randomBytes();
        $connection->insert('custom_field_set', [
            'id' => $setId,
            'name' => 'custom_birthday_email',
            'config' => json_encode(
                [
                    'label' => [
                        'en-GB' => 'Enable Birthday Email'
                    ]
                ]
            ),
            'active' => 1,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        $connection->insert('custom_field', [
            'id' => Uuid::randomBytes(),
            'custom_field_set_id' => $setId,
            'name' => 'enable_birthday_email',
            'type' => 'bool',
            'config' => json_encode([
                'type' => 'switch',
                'label' => ['en-GB' => 'Enable Birthday Email', 'de-DE' => 'Geburtstags-E-Mail aktivieren'],
                'customFieldPosition' => 1,
                'componentName' => 'sw-field',
                'customFieldType' => 'switch'
            ]),
            'active' => 1,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        $connection->insert('custom_field_set_relation', [
            'custom_field_set_id' => $setId,
            'entity_name' => 'customer',
        ]);
    }
}
