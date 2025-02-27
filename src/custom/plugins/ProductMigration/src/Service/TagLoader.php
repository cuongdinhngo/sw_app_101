<?php declare(strict_types=1);

namespace ProductMigration\Service;

use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class TagLoader
{
    use DataTrait;

    public function __construct(
        private readonly EntityRepository $tagRepository
    ) {
    }

    public function loadAllTags(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $criteria = new Criteria();
        $tags = $this->tagRepository->search($criteria, $this->getDefaultContext())->getEntities()->getElements();
        $results = [];
        foreach ($tags as $tag) {
            $results[$tag->getName()] = $tag->getId();
        }

        $this->data = $results;
        unset($results, $tags);
    }

    public function mapProductTags(?string $importedTags): array
    {
        $results = [];
        foreach (array_map('trim', explode(';', $importedTags)) as $tag) {
            if (!$this->isExisted($tag)) {
                $id = Uuid::randomHex();
                $this->tagRepository->create(['id' => $id, 'name' => $tag], $this->getDefaultContext());
                $this->add($tag, $id);
            } else {
                $id = $this->getValueByKey($tag);
            }
            $results[] = ['id' => $id];
        }

        return $results;
    }
}
