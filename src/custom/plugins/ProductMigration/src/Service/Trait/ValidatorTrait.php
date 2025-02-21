<?php declare(strict_types=1);

namespace ProductMigration\Service\Trait;

trait ValidatorTrait
{
    protected array $rules = [
        'required' => [
            'method' => 'isRequired',
            'errorMessage' => '%s is required'
        ],
        'numbericPrice' => [
            'method' => 'isNumericPrice',
            'errorMessage' => '%s must be numberic'
        ]
    ];

    protected array $fields = [
        'product_number' => 'required',
        'name' => 'required',
        'price' => 'required|numbericPrice',
        'category_name' => 'required',
        'manufacturer' => 'required',
    ];

    public function validate(array $data): array
    {
        $errors = [];
        foreach ($this->fields as $field => $rules) {
            $rules = explode('|', $rules);
            foreach ($rules as $rule) {
                $method = $this->rules[$rule]['method'];
                $isValid = $this->$method($data, $field);
                if ($isValid) {
                    continue;
                } else {
                    $errors[$field] = sprintf($this->rules[$rule]['errorMessage'], $field);
                }
            }
        }

        return $errors;
    }

    protected function isRequired(array $data, string $field): bool
    {
        return !empty($data[$field] ?? null);
    }

    protected function isNumericPrice(array $data, string $field): bool
    {
        return is_numeric($data[$field]);
    }
}