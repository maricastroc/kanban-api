<?php

namespace Database\Factories;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'column_id' => Column::factory(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn(array $attributes): array => [
            'status' => 'pending',
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(fn(array $attributes): array => [
            'status' => 'in_progress',
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn(array $attributes): array => [
            'status' => 'completed',
        ]);
    }

    public function withSubtasks(int $count = 2): self
    {
        return $this->afterCreating(function (Task $task) use ($count): void {
            $task->subtasks()->createMany(
                \Database\Factories\SubtaskFactory::new()
                    ->count($count)
                    ->make()
                    ->toArray()
            );
        });
    }

    public function withTags(int $count = 1): self
    {
        return $this->afterCreating(function (Task $task) use ($count): void {
            $tags = \App\Models\Tag::factory()
                ->count($count)
                ->create();

            $task->tags()->sync($tags->pluck('id'));
        });
    }

    public function forColumn(Column $column): self
    {
        return $this->state(fn(array $attributes): array => [
            'column_id' => $column->id,
        ]);
    }

    public function overdue(): self
    {
        return $this->state(fn(array $attributes): array => [
            'due_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    public function noDueDate(): self
    {
        return $this->state(fn(array $attributes): array => [
            'due_date' => null,
        ]);
    }
}
