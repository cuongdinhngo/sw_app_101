<?php declare(strict_types=1);

namespace FreeGift\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class CustomFieldsCleaner
{
    public function __construct(
        private readonly EntityRepository $customFieldSetRepository,
        private readonly EntityRepository $customFieldSetRelationRepository,
        private readonly EntityRepository $customFieldRepository
    ) {
    }

    public function execute(Context $context): void
    {
        $customFieldSetIds = $this->getCustomFieldSetIds($context);
        foreach ($customFieldSetIds as $setId) {
            $this->cleanCustomFieldSetRelation($setId, $context);
            $this->cleanCustomField($setId, $context);
        }
        $this->customFieldSetRepository->delete(
            array_map(fn($id) => ['id' => $id], $customFieldSetIds),
            $context
        );
    }

    /**
     * @return string[]
     */
    private function getCustomFieldSetIds(Context $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('name', CustomFieldsInstaller::CUSTOM_FIELDSET_NAME));

        return $this->customFieldSetRepository->searchIds($criteria, $context)->getIds();
    }

    private function cleanCustomFieldSetRelation(string $setId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFieldSetId', $setId));
        $ids = $this->customFieldSetRelationRepository->searchIds($criteria, $context)->getIds();
        $this->customFieldSetRelationRepository->delete(
            array_map(fn($id) => ['id' => $id], $ids),
            $context
        );
    }

    private function cleanCustomField(string $setId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFieldSetId', $setId));
        $ids = $this->customFieldRepository->searchIds($criteria, $context)->getIds();
        $this->customFieldRepository->delete(
            array_map(fn($id) => ['id' => $id], $ids),
            $context
        );
    }
}
