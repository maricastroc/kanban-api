<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    protected $model = Board::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->words(3, true),
            'is_active' => $this->faker->boolean(),
            'user_id' => User::factory(),
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function withColumns(int $count = 3): self
    {
        return $this->afterCreating(function (Board $board) use ($count): void {
            $board->columns()->createMany(
                \Database\Factories\ColumnFactory::new()
                    ->count($count)
                    ->make()
                    ->toArray()
            );
        });
    }
}
