<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SubtaskResource;
use App\Models\Subtask;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class SubtaskController extends Controller
{
    use AuthorizesRequests;

    public function toggleCompletion(Subtask $subtask): JsonResponse
    {
        $this->authorize('update', $subtask);

        $subtask->is_completed = ! $subtask->is_completed;
        $subtask->save();

        return response()->json([
            'success' => true,
            'message' => 'Subtask completion toggled successfully!',
            'data' => new SubtaskResource($subtask),
        ]);
    }
}
