<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\Task;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $tags = Tag::query()
                ->whereHas('tasks.column.board', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => TagResource::collection($tags)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $tag = Tag::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully',
                'data' => new TagResource($tag)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function attachToTask(Request $request, Task $task, Tag $tag): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($task->column->board->user_id !== $user->id) {
                throw new \Exception('Unauthorized');
            }

            $task->tags()->attach($tag->id);

            return response()->json([
                'success' => true,
                'message' => 'Tag attached to task successfully',
                'data' => new TagResource($tag)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to attach tag to task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detachFromTask(Request $request, Task $task, Tag $tag): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($task->column->board->user_id !== $user->id) {
                throw new \Exception('Unauthorized');
            }

            $task->tags()->detach($tag->id);

            return response()->json([
                'success' => true,
                'message' => 'Tag detached from task successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to detach tag from task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$tag->tasks()->whereHas('column.board', fn($q) => $q->where('user_id', $user->id))->exists()) {
                throw new \Exception('Unauthorized');
            }

            $tag->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully',
                'data' => new TagResource($tag)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove uma tag
     */
    public function destroy(Tag $tag): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$tag->tasks()->whereHas('column.board', fn($q) => $q->where('user_id', $user->id))->exists()) {
                throw new \Exception('Unauthorized');
            }

            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}