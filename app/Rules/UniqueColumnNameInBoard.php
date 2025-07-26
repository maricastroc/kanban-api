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

        if (! isset($currentColumn['name'])) {
            return true;
        }

        $currentName = $currentColumn['name'];
        $currentId = $currentColumn['id'] ?? null;

        return collect($this->columns)
            ->filter(function ($col, $index) use ($currentId): bool {
                if (isset($col['id']) && $currentId !== null) {
                    // Ignora a coluna sendo validada pelo id
                    return $col['id'] !== $currentId;
                }

                // Se não tem id, ignora pelo índice
                return (string) $index !== (string) $this->currentIndex;
            })
            ->every(fn($col): bool => ! isset($col['name']) || $col['name'] !== $currentName);
    }

    public function message(): string
    {
        return 'The column name ":value" is duplicated.';
    }
}
