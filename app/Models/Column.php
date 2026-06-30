<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['name', 'board_id', 'uuid', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        // New columns are appended to the end of their board (1-based order).
        static::creating(function (Column $column): void {
            if (is_null($column->order)) {
                $maxOrder = self::where('board_id', $column->board_id)->max('order');
                $column->order = is_null($maxOrder) ? 1 : $maxOrder + 1;
            }
        });
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }
}
