<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColumnFactory extends Factory
{
    protected $model = Column::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->words(2, true),
            'board_id' => Board::factory(),
        ];
    }

    public function withTasks(int $count = 3): self
    {
        return $this->afterCreating(function (Column $column) use ($count): void {
            $column->tasks()->createMany(
                \Database\Factories\TaskFactory::new()
                    ->count($count)
                    ->make()
                    ->toArray()
            );
        });
    }

    public function forBoard(Board $board): self
    {
        return $this->state(fn(array $attributes): array => [
            'board_id' => $board->id,
        ]);
    }
}
