<?php declare(strict_types=1);

namespace ProductMigration\Service;

use ProductMigration\Service\Trait\DataTrait;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
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
        $this->loadExistedProducts();
    }

    public function process(array $productData): void
    {
        $payload = $this->buildPayload($productData);
        $this->productRepository->upsert([$payload], Context::createDefaultContext());
    }

    private function buildPayload(array $productData): array
    {
        $properties = $this->propertyGroupOptionLoader
            ->loadPropertyGroupOption()
            ->mapProductProperty($productData['variant']);

        $tags = $this->tagLoader
            ->loadAllTags()
            ->mapProductTags($productData['tag']);
        
        $manufacturer = $this->productManufacturerLoader
            ->loadProductManufacturers()
            ->mapProductManufacturer($productData['manufacturer']);

        $importedCategory = [
            'categoryIdentifier' => $productData['category_id'],
            'parentCategoryIdentifier' => $productData['parent_category_id'],
            'name' => $productData['category_name'],
        ];
        $categories = $this->categoryLoader
            ->loadAllCategories()
            ->mapCategories($importedCategory);
        
        $id = $this->getValueByKey($productData['product_number']) ?? Uuid::randomHex();

        return [
            'id' => $id,
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
        $context = Context::createDefaultContext();
        $products = $this->productRepository->search($criteria, $context)->getEntities()->getElements();
        $results = [];
        foreach ($products as $product) {
            $results[$product->getProductNumber()] = $product->getId();
        }

        $this->data = $results;

        return $this;
    }
}
