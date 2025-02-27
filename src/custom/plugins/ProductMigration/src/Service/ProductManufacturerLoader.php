<?php declare(strict_types=1);

namespace ProductMigration\Service;

use DateTime;
use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Defaults;
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

    public function loadProductManufacturers(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $criteria = new Criteria();
        $manufacturers = $this->productManufacturerRepository->search($criteria, $this->getDefaultContext())->getEntities()->getElements();
        $results = [];
        foreach ($manufacturers as $manufacturer) {
            $results[$manufacturer->getName()] = $manufacturer->getId();
        }

        $this->data = $results;
        unset($results, $manufacturers, $criteria);
    }

    public function mapProductManufacturer(string $manufacturer): string
    {
        $id = $this->getValueByKey($manufacturer);
        if (!$id) {
            $id = Uuid::randomHex();
            $data = [
                'id' => $id,
                'name' => $manufacturer,
                'language_id' => Defaults::LANGUAGE_SYSTEM
            ];
            $this->productManufacturerRepository->create([$data], $this->getDefaultContext());
            $this->add($manufacturer, $id);
        }

        return $id;
    }
}
