<?php declare(strict_types=1);

namespace ProductMigration\Service\Trait;

use DateTime;

trait DataTrait
{
    public ?array $data = null;

    public ?DateTime $currentDateTime = null;

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getValueByKey(string $key): mixed
    {
        if ($this->isExisted($key)) {
            return $this->getData()[$key];
        }

        return null;
    }

    public function isExisted(string $key): bool
    {
        if (isset($this->getData()[$key])) {
            return true;
        }

        return false;
    }

    public function add(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function resetData(): void
    {
        unset($this->data);
    }

    public function getCurrentDateTime(): DateTime
    {
        return (new DateTime());
    }

    public function getFormatedCurrentDateTime(): string
    {
        return (new DateTime())->format('Y-m-d H:i:s');
    }
}
