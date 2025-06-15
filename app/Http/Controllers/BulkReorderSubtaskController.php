<?php 

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkReorderSubtaskController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'taskId' => ['required', 'integer', 'exists:tasks,id'],
            'subtasks' => ['required', 'array'],
            'subtasks.*.id' => ['required', 'integer', 'exists:subtasks,id'],
            'subtasks.*.order' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['subtasks'] as $subtaskData) {
                $subtask = Subtask::where('task_id', $validated['taskId'])
                    ->findOrFail($subtaskData['id']);

                $this->authorize('update', $subtask);

                $subtask->order = $subtaskData['order'];
                $subtask->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subtasks reordered successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder subtasks.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
