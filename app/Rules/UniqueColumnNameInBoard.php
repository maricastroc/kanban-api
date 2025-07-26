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
        $currentId = $currentColumn['id'] ?? null;
        $currentName = $value;

        return collect($this->columns)
            ->reject(function ($col) use ($currentId) {
                return isset($col['id']) && $currentId && $col['id'] == $currentId;
            })
            ->every(function ($col) use ($currentName) {
                return !isset($col['name']) || $col['name'] !== $currentName;
            });
    }

    public function message(): string
    {
        return 'The column name ":value" is duplicated.';
    }
}
