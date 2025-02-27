<?php declare(strict_types=1);

namespace ProductMigration\Core\Controller;

use ProductMigration\Service\ProductLoader;
use ProductMigration\Service\Trait\ValidatorTrait;
use Psr\Log\LoggerInterface;
use SplFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ProductMigrationController extends AbstractController
{
    use ValidatorTrait;
 
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductLoader $productLoader
    ) {
    }

    #[Route(
        path: 'api/product-migration/upload',
        name: 'frontend.product-migration.upload',
        methods: ['POST'],
    )]
    public function upload(Request $request): JsonResponse
    {
        $this->logger->debug('Product Migration is starting ....');
        $file = $request->files->get('file');

        try {
            $this->productLoader->loadProductComponents();
            $data = new SplFileObject($file->getPathName());
            $data->setFlags(SplFileObject::READ_CSV);
            $processedCount = 0;
            $totalCount = 0;
            $batchSize = 200;
            $payloads = [];
            foreach ($data as $key => $product) {
                if ($key == 0) {
                    $fileHeader = $product;
                    continue;
                }

                $totalCount++;
                $product = array_combine(array_map('trim', $fileHeader), array_map('trim', $product));
                $errors = $this->validate($product);
                if (!empty($errors)) {
                    $this->logger->error(
                        'Failed Product Validator',
                        [
                            'data' => implode(',', $product),
                            'detail' => implode('|', $errors)
                        ]
                    );
                    continue;
                } else {
                    $payloads[] = $this->productLoader->buildPayload($product); // Return payload instead of upsert
                    $processedCount++;
                }

                try {
                    if (count($payloads) >= $batchSize) {
                        $this->productLoader->upsertBatch($payloads);
                        $payloads = [];
                        if ($processedCount % 100 === 0) {
                            $this->logger->info("Processed $processedCount rows | Memory: " . (memory_get_usage() / 1024 / 1024) . " MB");
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->error(
                        'Failed Product Processor',
                        [
                            [
                                'data' => implode(',', $product),
                                'detail' => $e->getMessage(),
                                'trace' => $e->getTrace()
                            ]
                        ]
                    );
                }
            }

            if (!empty($payloads)) {
                $this->productLoader->upsertBatch($payloads);
            }

            $this->logger->debug('Product Migration is DONE');
            $code = Response::HTTP_OK;
            $message = ['message' => 'File uploaded successfully. ' . $processedCount . '/' . $totalCount . ' rows were migrated'];
        } catch (\Exception $e) {
            $this->logger->error(
                'Migration was FAILED',
                [
                    'detail' => $e->getMessage(),
                    'trace' => $e->getTrace()
                ]
            );
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $message = ['message' => 'Migration was FAILED. Error detail is ' . $e->getMessage()];
        }

        return new JsonResponse($message, $code);
    }
}