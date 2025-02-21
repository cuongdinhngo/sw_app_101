<?php declare(strict_types=1);

namespace ProductMigration\Service;

use DateTime;
use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Framework\Context;
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

    public function loadAllTags(): self
    {
        if (!empty($this->data)) {
            return $this;
        }

        $criteria = new Criteria();
        $context = Context::createDefaultContext();
        $tags = $this->tagRepository->search($criteria, $context)->getEntities()->getElements();
        $results = [];
        foreach ($tags as $tag) {
            $results[$tag->getName()] = $tag->getId();
        }

        $this->data = $results;

        return $this;
    }

    public function mapProductTags(?string $importedTags): array
    {
        $importedTags = array_map('trim', explode(';', $importedTags));
        $results = [];
        foreach ($importedTags as $tag) {
            if (!$this->isExisted($tag)) {
                $id = Uuid::randomHex();
                $this->createNewTag($id, $tag);
                $this->add($tag, $id);
            } else {
                $id = $this->getValueByKey($tag);
            }
            $results[] = ['id' => $id];
        }

        return $results;
    }

    private function createNewTag(string $id, string $tag): void
    {
        $context = Context::createDefaultContext();
        $tagData = [
            'id' => $id,
            'name' => $tag
        ];
        $this->tagRepository->create([$tagData], $context);
    }
}
