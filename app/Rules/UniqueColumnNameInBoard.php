<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueColumnNameInBoard implements Rule
{
    public function __construct(protected array $columns, protected int|string $currentIndex)
    {
    }

    public function passes($attribute, $value): bool
    {
        return collect($this->columns)
            ->reject(fn ($col, $i): bool => (string) $i === (string) $this->currentIndex)
            ->every(fn ($col): bool => ! isset($col['name']) || $col['name'] !== $value);
    }

    public function message(): string
    {
        return 'The column name ":value" is duplicated.';
    }
}
