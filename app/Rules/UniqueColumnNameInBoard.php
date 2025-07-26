<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueColumnNameInBoard implements Rule
{
    public function __construct(protected array $columns, protected int|string $currentIndex) {}

public function passes($attribute, $value): bool
{
    $currentColumn = $this->columns[$this->currentIndex] ?? null;

    return collect($this->columns)
        ->reject(function ($col, $i) use ($currentColumn): bool {
            if (isset($col['id']) && isset($currentColumn['id'])) {
                return $col['id'] === $currentColumn['id'];
            }

            return (string) $i === (string) $this->currentIndex;
        })
        ->every(fn ($col): bool => ! isset($col['name']) || $col['name'] !== $currentColumn['name']);
}

    public function message(): string
    {
        return 'The column name ":value" is duplicated.';
    }
}
