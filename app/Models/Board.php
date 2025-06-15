<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Board extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['name', 'is_active', 'user_id', 'uuid'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function createWithColumns(array $data, $userId): Board
    {
        return DB::transaction(function () use ($data, $userId) {
            $board = self::create([
                'name' => $data['name'],
                'user_id' => $userId,
                'is_active' => true,
            ]);

            if (! empty($data['columns'])) {
                foreach ($data['columns'] as $columnData) {
                    $board->columns()->create([
                        'name' => $columnData['name'],
                    ]);
                }
            }

            $board->deactivateOtherBoards();

            return $board;
        });
    }

    public function updateWithColumns(array $data): Board
    {
        return DB::transaction(function () use ($data): static {
            $this->update([
                'name' => $data['name'] ?? $this->name,
                'is_active' => true,
            ]);

            if (isset($data['columns'])) {
                $this->syncColumns($data['columns']);
            } else {
                $this->columns()->delete();
            }

            $this->deactivateOtherBoards();

            return $this;
        });
    }

    protected function syncColumns(array $columnsData): void
    {
        $existingColumnIds = $this->columns()->pluck('id')->toArray();
        $updatedColumnIds = [];

        foreach ($columnsData as $columnData) {
            if (isset($columnData['id'])) {
                $column = $this->columns()->find($columnData['id']);

                if ($column) {
                    $column->update(['name' => $columnData['name']]);
                    $updatedColumnIds[] = $column->id;
                }
            } else {
                $newColumn = $this->columns()->create(['name' => $columnData['name']]);
                $updatedColumnIds[] = $newColumn->id;
            }
        }

        $columnsToDelete = array_diff($existingColumnIds, $updatedColumnIds);

        if (! empty($columnsToDelete)) {
            $this->columns()->whereIn('id', $columnsToDelete)->delete();
        }
    }

    public static function getActiveBoard(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['columns.tasks.subtasks', 'user'])
            ->first();
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
        $this->deactivateOtherBoards();
    }

    public function deactivateOtherBoards(): void
    {
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class);
    }
}
