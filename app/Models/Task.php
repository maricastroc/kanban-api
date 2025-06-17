<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['name', 'description', 'order', 'column_id', 'status', 'due_date', 'uuid'];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    #[\Override]
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Task $task): void {
            if ($task->column_id) {
                $maxOrder = Task::where('column_id', $task->column_id)->max('order');
                $task->order = is_null($maxOrder) ? 1 : $maxOrder + 1;
            }
        });
    }

    public static function createWithSubtasks(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $subtasks = $data['subtasks'] ?? [];
            $tags = $data['tags'] ?? [];

            unset($data['subtasks'], $data['tags']);

            $task = self::create($data);

            foreach ($subtasks as $subtaskData) {
                $task->subtasks()->create([
                    'name' => $subtaskData['name'],
                    'is_completed' => $subtaskData['is_completed'] ?? false,
                ]);
            }

            if (!empty($tags)) {
                $task->tags()->sync($tags);
            }

            return $task;
        });
    }

    public function updateWithSubtasks(array $data): Task
    {
        DB::beginTransaction();

        try {
            $this->update([
                'name' => $data['name'] ?? $this->name,
                'description' => $data['description'] ?? $this->description,
                'order' => $data['order'] ?? $this->order,
                'status' => $data['status'] ?? $this->status,
                'due_date' => $data['due_date'] ?? $this->due_date,
            ]);

            if (isset($data['subtasks'])) {
                $existingSubtaskIds = $this->subtasks()->pluck('id')->toArray();
                $updatedSubtaskIds = [];

                foreach ($data['subtasks'] as $subtaskData) {
                    if (isset($subtaskData['uuid'])) {
                        $subtask = $this->subtasks()->where('uuid', $subtaskData['uuid'])->first();
                        if ($subtask) {
                            $subtask->update([
                                'name' => $subtaskData['name'],
                            ]);
                            $updatedSubtaskIds[] = $subtask->id;
                        } else {
                            $newSubtask = $this->subtasks()->create([
                                'name' => $subtaskData['name'],
                            ]);
                            $updatedSubtaskIds[] = $newSubtask->id;
                        }
                    } else {
                        $newSubtask = $this->subtasks()->create([
                            'name' => $subtaskData['name'],
                        ]);
                        $updatedSubtaskIds[] = $newSubtask->id;
                    }
                }

                $subtasksToDelete = array_diff($existingSubtaskIds, $updatedSubtaskIds);
                if ($subtasksToDelete !== []) {
                    $this->subtasks()->whereIn('id', $subtasksToDelete)->delete();
                }
            } else {
                $this->subtasks()->delete();
            }

            // Atualizar tags
            if (isset($data['tags']) && is_array($data['tags'])) {
                $this->tags()->sync($data['tags']);
            }

            DB::commit();
            return $this;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('order');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'task_tag')
            ->using(\App\Models\TaskTag::class)
            ->withPivot('created_at')
            ->as('pivot');
    }
}
