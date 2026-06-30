<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->authorize('viewAny', Tag::class);

        $tags = $user->tags()->get();

        return response()->json([
            'data' => [
                'tags' => TagResource::collection($tags),
            ],
        ], 200);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $this->authorize('create', Tag::class);

        $user = Auth::user();

        $tag = Tag::create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully!',
            'data' => new TagResource($tag),
        ], 201);
    }

    public function attachToTask(Request $request, Task $task, Tag $tag): JsonResponse
    {
        $this->authorize('attachTag', [$task, $tag]);

        if ($task->tags()->where('tag_id', $tag->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tag is already linked to the task',
            ], 409);
        }

        $task->tags()->attach($tag->id);

        return response()->json([
            'success' => true,
            'message' => 'Tag attached to task successfully',
            'data' => new TagResource($tag),
        ]);
    }

    public function detachFromTask(Request $request, Task $task, Tag $tag): JsonResponse
    {
        $this->authorize('detachTag', [$task, $tag]);

        if (! $task->tags()->where('tag_id', $tag->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tag is already unlinked from the task',
            ], 409);
        }

        $task->tags()->detach($tag->id);

        return response()->json([
            'success' => true,
            'message' => 'Tag detached from task successfully!',
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        $tag->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully!',
            'data' => new TagResource($tag),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully!',
        ]);
    }
}
