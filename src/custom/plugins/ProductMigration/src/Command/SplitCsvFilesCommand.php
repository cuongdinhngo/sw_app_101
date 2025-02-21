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

#[AsCommand(
    name: 'migrate-products:split-files',
    description: 'Split CSV files',
)]
class SplitCsvFilesCommand extends Command
{
    private const PRODUCT_CSV_FILE = '/tmp/shopware_tmp/shopware_products.csv';

    private const SUBFILE_PATH = '/tmp/shopware_tmp/subs/';

    private const MAX_ITEM = 100000;

    public function __construct(
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    // Provides a description, printed out in bin/console
    protected function configure(): void
    {
        $this->addOption('maxItem', null, InputOption::VALUE_OPTIONAL, 'Max Items per File');
    }

    // Actual code executed in the command
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('SplitCsvFilesCommand is starting ...');

        $fileHeader = [];
        $count = 0;
        $fileNumber = 0;
        $content = [];
        $maxItems = $input->getOption('maxItem') ?? self::MAX_ITEM;
        try {
            $data = new SplFileObject(SELF::PRODUCT_CSV_FILE);
            $data->setFlags(SplFileObject::READ_CSV);
            foreach ($data as $key => $product) {
                if ($key == 0) {
                    $fileHeader = $product;
                    continue;
                }
    
                $content[] = $product;
                $count++;
                if ($count == $maxItems) {
                    $fileNumber++;
                    $this->createSubFiles($fileHeader, $content, $fileNumber);
                    $count = 0;
                    $content = [];
                    continue;
                }
            }
            $this->createSubFiles($fileHeader, $content, $fileNumber += 1);
        } catch (\Exception $e) {
            $output->writeln('FILE IS NOT EXISTED ...');
        }

        // Exit code 0 for success
        return 0;
    }

    private function createSubFiles(array $header, array $data, int $fileNumber): void
    {
        $fileName = 'products_' . $fileNumber . '.csv';
        $fp = fopen(self::SUBFILE_PATH . $fileName, 'w');

        fputcsv($fp, $header);
        foreach ($data as $product) {
            fputcsv($fp, $product);
        }

        fclose($fp);

        $this->connection->executeStatement(
            'INSERT INTO track_files(`file`, `is_done`, `created_at`) VALUES (:filePath, :isDone, :createdAt)',
            [
                'filePath' => $fileName,
                'isDone' => 0,
                'createdAt' => (new DateTime())->format('Y-m-d H:i:s')
            ]
        );
    }
}
