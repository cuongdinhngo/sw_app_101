<?php declare(strict_types=1);

namespace SwagShopFinder\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Faker\Factory;
use Ramsey\Uuid\Uuid;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Country\CountryException;

#[AsCommand(
    name: 'shop-finder:create-mock-data',
    description: 'Create mock data for Shop Finder',
)]
class CreateMockDataCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $shopFinderRepository,
        private readonly EntityRepository $countryRepository
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
        $output->writeln('### Shop Finder starts creating mock data ...');
        $context = Context::createCLIContext();
        $faker = Factory::create();
        $country = $this->getActiveCountry($context);

        $data = [];
        for ($i = 0; $i < 50; $i++) {
            $data[] = [
                'id' => Uuid::uuid4()->getHex()->toString(),
                'active' => true,
                'name' => $faker->name,
                'street' => $faker->streetAddress,
                'city' => $faker->city,
                'countryId' => $country->getId()
            ];
        }

        $this->shopFinderRepository->create($data, $context);

        $output->writeln('### DONE => Shop Finder');
        // Exit code 0 for success
        return 0;
    }

    private function getActiveCountry(Context $context): CountryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', '1'));
        $criteria->addFilter(new EqualsFilter('iso', 'VN'));
        $criteria->setLimit(1);

        $country = $this->countryRepository->search($criteria, $context)->getEntities()->first();
        if (null === $country) {
            throw CountryException::countryNotFound('');
        }

        return $country;
    }
}
