<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'order', 'column_id', 'status', 'due_date'];

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
