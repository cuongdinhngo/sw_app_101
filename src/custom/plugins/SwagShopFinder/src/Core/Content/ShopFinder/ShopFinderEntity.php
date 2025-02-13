<?php declare(strict_types=1);

namespace SwagShopFinder\Core\Content\ShopFinder;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Country\CountryEntity;

class ShopFinderEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name;

    protected bool $active;

    protected string $street;

    protected string $postCode;

    protected string $city;

    protected ?string $url;

    protected ?string $telephone;

    protected ?string $openTimes;

    protected ?float $latitude;

    protected ?float $longitude;

    protected ?CountryEntity $country;

    /**
     * @return CountryEntity|null
     */
    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    /**
     * @param CountryEntity|null $country
     */
    public function setCountry(?CountryEntity $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * @param string|null $telephone
     */
    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function setOpenTimes(?string $openTimes): void
    {
        $this->openTimes = $openTimes;
    }

    public function getOpenTimes(): ?string
    {
        return $this->openTimes;
    }


    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }
}
