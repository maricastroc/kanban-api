<?php

namespace Database\Factories;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubtaskFactory extends Factory
{
    protected $model = Subtask::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->sentence(3),
            'is_completed' => false,
            'task_id' => Task::factory(),
        ];
    }

    public function completed(): self
    {
        return $this->state(fn(array $attributes): array => [
            'is_completed' => true,
        ]);
    }

    public function incomplete(): self
    {
        return $this->state(fn(array $attributes): array => [
            'is_completed' => false,
        ]);
    }

    public function forTask(Task $task): self
    {
        return $this->state(fn(array $attributes): array => [
            'task_id' => $task->id,
        ]);
    }
}
