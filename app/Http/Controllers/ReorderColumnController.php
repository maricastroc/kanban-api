<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReorderColumnController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Column $column): JsonResponse
    {
        $this->authorize('update', $column);

        $validated = $request->validate([
            'new_order' => ['required', 'integer', 'min:1'],
        ]);

        $newOrder = (int) $validated['new_order'];
        $currentOrder = $column->order;
        $boardId = $column->board_id;

        if ($newOrder === $currentOrder) {
            return response()->json([
                'success' => true,
                'message' => 'Column already in correct position.',
            ]);
        }

        DB::transaction(function () use ($column, $boardId, $currentOrder, $newOrder): void {
            if ($newOrder < $currentOrder) {
                Column::where('board_id', $boardId)
                    ->whereBetween('order', [$newOrder, $currentOrder - 1])
                    ->increment('order');
            } else {
                Column::where('board_id', $boardId)
                    ->whereBetween('order', [$currentOrder + 1, $newOrder])
                    ->decrement('order');
            }

            $column->update(['order' => $newOrder]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Column reordered successfully!',
        ]);
    }
}
