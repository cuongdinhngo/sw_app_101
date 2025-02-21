<?php declare(strict_types=1);

namespace ProductMigration\Service;

use DateTime;
use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductManufacturerLoader
{
    use DataTrait;

    public function __construct(
        private readonly EntityRepository $productManufacturerRepository
    ) {
    }

    public function loadProductManufacturers(): self
    {
        if (!empty($this->data)) {
            return $this;
        }

        $criteria = new Criteria();
        $context = Context::createDefaultContext();
        $manufacturers = $this->productManufacturerRepository->search($criteria, $context)->getEntities()->getElements();
        $results = [];
        foreach ($manufacturers as $manufacturer) {
            $results[$manufacturer->getName()] = $manufacturer->getId();
        }

        $this->data = $results;

        return $this;
    }

    public function mapProductManufacturer(string $manufacturer): string
    {
        $id = $this->getValueByKey($manufacturer);
        if (!$id) {
            $id = Uuid::randomHex();
            $this->createNewManufacturer($id, $manufacturer);
            $this->add($manufacturer, $id);
        }

        return $id;
    }

    private function createNewManufacturer(string $id, string $manufacturer): void
    {
        $data = [
            'id' => $id,
            'name' => $manufacturer,
            'language_id' => Defaults::LANGUAGE_SYSTEM
        ];
        $this->productManufacturerRepository->create([$data], Context::createDefaultContext());
    }
}
