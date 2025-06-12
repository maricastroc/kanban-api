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
        DB::beginTransaction();

        try {
            $board = self::create([
                'name' => $data['name'],
                'user_id' => $userId,
                'is_active' => false,
            ]);

            if (! empty($data['columns'])) {
                foreach ($data['columns'] as $columnData) {
                    $board->columns()->create([
                        'name' => $columnData['name'],
                    ]);
                }
            }

            DB::commit();

            return $board;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithColumns(array $data): Board
    {
        DB::beginTransaction();

        try {
            $this->update([
                'name' => $data['name'] ?? $this->name,
                'is_active' => $data['is_active'] ?? $this->is_active,
            ]);

            if (isset($data['columns'])) {
                $existingColumnIds = $this->columns()->pluck('id')->toArray();
                $updatedColumnIds = [];

                foreach ($data['columns'] as $columnData) {
                    if (isset($columnData['uuid'])) {
                        $column = $this->columns()->where('uuid', $columnData['uuid'])->first();

                        if ($column) {
                            $column->update([
                                'name' => $columnData['name'],
                            ]);
                            $updatedColumnIds[] = $column->id;
                        } else {
                            $newColumn = $this->columns()->create([
                                'name' => $columnData['name'],
                            ]);
                            $updatedColumnIds[] = $newColumn->id;
                        }
                    } else {
                        $newColumn = $this->columns()->create([
                            'name' => $columnData['name'],
                        ]);
                        $updatedColumnIds[] = $newColumn->id;
                    }
                }

                $columnsToDelete = array_diff($existingColumnIds, $updatedColumnIds);

                if ($columnsToDelete !== []) {
                    $this->columns()->whereIn('id', $columnsToDelete)->delete();
                }
            } else {
                $this->columns()->delete();
            }

            DB::commit();

            return $this;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
