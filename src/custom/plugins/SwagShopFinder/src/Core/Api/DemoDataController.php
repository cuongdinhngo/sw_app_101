<?php declare(strict_types=1);

namespace SwagShopFinder\Core\Api;

use Faker\Factory;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Country\CountryException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class DemoDataController extends AbstractController
{
    public function __construct(
        private readonly EntityRepository $countryRepository,
        private readonly EntityRepository $shopFinderRepository
    ) {
    }

    #[Route(
        path: '/swag-shop-finder/generate',
        name: 'frontend.custom.swag_shop_finder',
        methods: ['GET', 'POST'],
    )]
    public function generate(Context $context): Response
    {
        $faker = Factory::create();
        $country = $this->getActiveCountry($context);

        $data = [];
        for ($i = 0; $i < 50; $i++) {
            $data[] = [
                'id' => Uuid::uuid4()->getHex()->toString(),
                'active' => true,
                'name' => $faker->name,
                'street' => $faker->streetAddress,
                'postCode' => $faker->postcode,
                'city' => $faker->city,
                'countryId' => $country->getId()
            ];
        }

        $this->shopFinderRepository->create($data, $context);

        return new Response('OK', Response::HTTP_OK);
    }

    private function getActiveCountry(Context $context): CountryEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', '1'));
        $criteria->setLimit(1);

        $country = $this->countryRepository->search($criteria, $context)->getEntities()->first();
        if (null === $country) {
            throw CountryException::countryNotFound('');
        }

        return $country;
    }
}
