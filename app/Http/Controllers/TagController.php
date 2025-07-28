<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Models\Task;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Tags",
 *     description="Tag-related operations"
 * )
 */
class TagController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="List all user tags",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tags list",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/Tag")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching tags",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $this->authorize('viewAny', Tag::class);

            $tags = $user->tags()->get();

            return response()->json([
                'data' => [
                    'tags' => TagResource::collection($tags),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tags',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Creates a new tag",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "color"},
     *
     *             @OA\Property(property="name", type="string", example="Urgent"),
     *             @OA\Property(property="color", type="string", example="#FF0000")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="ag created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag created successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tag"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error creating tag",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        try {
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tasks/{task}/tags/{tag}",
     *     summary="Attach tag to task",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag attached to task successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag attached to task successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tag"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error attaching task",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Tag] 5")
     *         )
     *     )
     *
     *
     * )
     */
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
                'data' => new TagResource($tag),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to attach tag to task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{task}/tags/{tag}",
     *     summary="Detach tag from task",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag detached from task successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag detached from task successfully!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error detaching task",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Tag] 5")
     *         )
     *     )
     * )
     */
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
                'message' => 'Tag detached from task successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to detach tag from task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tags/{tag}",
     *     summary="Updates a tag",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="Urgent"),
     *             @OA\Property(property="color", type="string", example="#FF0000")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag updated successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tag"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error updating tag",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Tag] 5")
     *         )
     *     )
     * )
     */
    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('update', $tag);

            $tag->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully!',
                'data' => new TagResource($tag),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tags/{tag}",
     *     summary="Deletes a tag",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag deleted successfully!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting tag",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Tag] 5")
     *         )
     *     )
     * )
     */
    public function destroy(Tag $tag): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('delete', $tag);

            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tag',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
