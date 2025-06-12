<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'order', 'is_completed', 'task_id'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    #[\Override]
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Subtask $subtask): void {
            $subtask->is_completed = false;

            if ($subtask->task_id) {
                $maxOrder = Subtask::where('task_id', $subtask->task_id)->max('order');
                $subtask->order = is_null($maxOrder) ? 1 : $maxOrder + 1;
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
