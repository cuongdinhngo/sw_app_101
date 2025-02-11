<?php declare(strict_types=1);

namespace FreeGift\Service;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFieldsInstaller
{
    public const CUSTOM_FIELDSET_NAME = 'custom_registration_gift';

    private const CUSTOM_FIELDSET = [
        'name' => self::CUSTOM_FIELDSET_NAME,
        'config' => [
            'label' => [
                'en-GB' => 'Free registration gift',
                'de-DE' => '[DE] Free registration gift',
            ]
        ],
        'customFields' => [
            [
                'name' => 'is_allocated_registration_gift',
                'type' => 'bool',
                'config' => [
                    'type' => 'switch',
                    'componentName' => 'sw-field',
                    'customFieldType' => 'switch',
                    'label' => [
                        'en-GB' => 'Is allocated registration gift',
                        'de-DE' => '[DE] Is allocated registration gift'
                    ],
                    'customFieldPosition' => 1
                ]
            ],
            [
                'name' => 'is_received_registration_gift',
                'type' => 'bool',
                'config' => [
                    'type' => 'switch',
                    'componentName' => 'sw-field',
                    'customFieldType' => 'switch',
                    'label' => [
                        'en-GB' => 'Is received registration gift',
                        'de-DE' => '[DE] Is received registration gift'
                    ],
                    'customFieldPosition' => 1
                ]
            ]
        ]
    ];

    private const ENTITY_RELATIONS = [
        CustomerDefinition::ENTITY_NAME,
        MailTemplateTranslationDefinition::ENTITY_NAME
    ];

    public function __construct(
        private readonly EntityRepository $customFieldSetRepository,
        private readonly EntityRepository $customFieldSetRelationRepository
    ) {
    }

    public function install(Context $context): void
    {
        $this->customFieldSetRepository->upsert([
            self::CUSTOM_FIELDSET
        ], $context);
    }

    public function addRelations(Context $context): void
    {
        $customFieldSetIds = $this->getCustomFieldSetIds($context);
        $relations = [];
        foreach ($customFieldSetIds as $customFieldSetId) {
            foreach (self::ENTITY_RELATIONS as $entity) {
                $relations[] = [
                    'customFieldSetId' => $customFieldSetId,
                    'entityName' => $entity
                ];
            }
        }

        $this->customFieldSetRelationRepository->upsert($relations, $context);
    }

    /**
     * @return string[]
     */
    private function getCustomFieldSetIds(Context $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('name', self::CUSTOM_FIELDSET_NAME));

        return $this->customFieldSetRepository->searchIds($criteria, $context)->getIds();
    }
}
