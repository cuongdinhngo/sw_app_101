<?php declare(strict_types=1);

namespace ProductMigration\Service;

use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class ProductLoader
{
    use DataTrait;

    public const SUBFILE_PATH = '/tmp/shopware_tmp/subs/';

    public const MAX_ITEM = 100000;

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly PropertyGroupOptionLoader $propertyGroupOptionLoader,
        private readonly TagLoader $tagLoader,
        private readonly ProductManufacturerLoader $productManufacturerLoader,
        private readonly CategoryLoader $categoryLoader
    ) {
    }

    public function loadProductComponents(): void
    {
        $this->loadExistedProducts();
        $this->propertyGroupOptionLoader->loadPropertyGroupOption();
        $this->tagLoader->loadAllTags();
        $this->productManufacturerLoader->loadProductManufacturers();
        $this->categoryLoader->loadAllCategories();
    }

    public function upsertBatch(array $payloads): void
    {
        $this->productRepository->upsert($payloads, $this->getDefaultContext());
        unset($payload);
    }

    public function buildPayload(array $productData): array
    {
        $properties = $this->propertyGroupOptionLoader->mapProductProperty($productData['variant']);
        $tags = $this->tagLoader->mapProductTags($productData['tag']);
        $manufacturer = $this->productManufacturerLoader->mapProductManufacturer($productData['manufacturer']);
        $categories = $this->categoryLoader->mapCategories([
                'categoryIdentifier' => $productData['category_id'],
                'parentCategoryIdentifier' => $productData['parent_category_id'],
                'name' => $productData['category_name'],
            ]);

        return [
            'id' => $this->getValueByKey($productData['product_number']) ?? Uuid::randomHex(),
            'productNumber' => $productData['product_number'],
            'name' => $productData['name'],
            'description' => $productData['description'],
            'taxId' => '01938c22fc0f71d297cec046c4614d19',
            'manufacturerId' => $manufacturer,
            'stock' => 0,
            'price' => [
                [
                    'currencyId' => Defaults::CURRENCY,
                    'gross' => $productData['price'],
                    'net' => $productData['price'],
                    'linked' => false
                ]
            ],
            'properties' => $properties,
            'categories' => $categories,
            'tags' => $tags
        ];
    }

    private function loadExistedProducts(): self
    {
        if (!empty($this->data)) {
            return $this;
        }

        $criteria = new Criteria();
        $products = $this->productRepository->search($criteria, $this->getDefaultContext())->getEntities()->getElements();
        $results = [];
        foreach ($products as $product) {
            $results[$product->getProductNumber()] = $product->getId();
        }

        $this->data = $results;
        unset($results, $products, $criteria);

        return $this;
    }
}
