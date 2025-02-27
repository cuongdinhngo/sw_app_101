<?php declare(strict_types=1);

namespace ProductMigration\Command;

use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use SplFileObject;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use ProductMigration\Service\ProductLoader;
use ProductMigration\Service\Trait\ValidatorTrait;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Defaults;

#[AsCommand(
    name: 'migrate-products:process-files',
    description: 'Process CSV files',
)]
class ProcessCsvFilesCommand extends Command
{
    use ValidatorTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Connection $connection,
        private readonly ProductLoader $productLoader
    ) {
        parent::__construct();
    }

    // Provides a description, printed out in bin/console
    protected function configure(): void
    {
        $this->setDescription('Does something very special.');
    }

    // Actual code executed in the command
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('ProcessCsvFilesCommand is starting ...');

        $files = $this->getSubFiles();
        if (empty($files)) {
            $output->writeln('NO FILES ...');
            return 0;
        }

        // foreach ($files as $file) {
        //     $this->handleFile($file['file']);
        // }

        $this->handleFile('');

        $output->writeln('ProcessCsvFilesCommand is finished ...');
        // Exit code 0 for success
        return 0;
    } 

    private function getSubFiles(): array
    {
        $statement = $this->connection->executeQuery(
            'SELECT `file` FROM track_files WHERE is_done = :isDone',
            [
                'isDone' => 0
            ]
        );

        return $statement->fetchAllAssociative();
    }

    private function handleFile(string $file): void
    {
        // $filePath = ProductLoader::SUBFILE_PATH . '' . $file;
        $filePath = '/var/www/html/custom/plugins/ProductMigration/src/Resources/shopware_products.csv';
        try {
            $this->productLoader->loadProductComponents();
            $data = new SplFileObject($filePath);
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
        } catch (\Exception $e) {
            $this->logger->error(
                'Migration was FAILED',
                [
                    'detail' => $e->getMessage(),
                    'trace' => $e->getTrace()
                ]
            );
        }
    }
}
