<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SubtaskResource;
use App\Models\Subtask;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class SubtaskController extends Controller
{
    use AuthorizesRequests;

    public function toggleCompletion(Subtask $subtask): JsonResponse
    {
        try {
            $this->authorize('update', $subtask);

            $subtask->is_completed = ! $subtask->is_completed;
            $subtask->save();

            return response()->json([
                'success' => true,
                'message' => 'Subtask completion toggled successfully!',
                'data' => new SubtaskResource($subtask),
            ]);
        } catch (AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'This action is unauthorized.',
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle subtask completion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
