<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="Board API",
 *     version="1.0.0",
 *     description="API para gerenciamento de quadros (boards)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class BoardController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/boards",
     *     summary="List all user boards",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="with",
     *         in="query",
     *         description="Relationships to include (columns,user)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Boards list",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="boards",
     *                     type="array",
     *
     *                     @OA\Items(ref="#/components/schemas/Board")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('viewAny', Board::class);

            $boards = Board::where('user_id', $user->id)
                ->when(
                    $request->string('with')->contains('columns'),
                    fn ($query) => $query->with('columns.tasks.subtasks')
                )
                ->when(
                    $request->string('with')->contains('user'),
                    fn ($query) => $query->with('user')
                )
                ->get();

            return response()->json([
                'data' => [
                    'boards' => BoardResource::collection($boards),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/boards/{id}",
     *     summary="Shows a specific board",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Board data",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="board",
     *                     ref="#/components/schemas/Board"
     *                 )
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
     *         description="Internal error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(Board $board): JsonResponse
    {
        try {
            $this->authorize('view', $board);

            $board->activate();

            $board->load([
                'columns.tasks.subtasks',
                'user',
            ]);

            return response()->json([
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'This action is unauthorized.',
                'error' => $e->getMessage(),
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreBoardRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $board = Board::createWithColumns($request->only(['name', 'columns']), $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Board created successfully!',
                'data' => [
                    'board' => new BoardResource($board->load('columns.tasks.subtasks')),
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/boards/{id}",
     *     summary="Update an existing board",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
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
     *             @OA\Property(property="name", type="string", example="Updated board"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="columns",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="To Do"),
     *                     @OA\Property(property="order", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Board updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Board updated successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="board",
     *                     ref="#/components/schemas/Board"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Falha ao atualizar quadro",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateBoardRequest $request, Board $board): JsonResponse
    {
        try {
            $this->authorize('update', $board);

            $board = $board->updateWithColumns($request->only(['name', 'is_active', 'columns']));

            return response()->json([
                'success' => true,
                'message' => 'Board updated successfully!',
                'data' => [
                    'board' => new BoardResource($board->load('columns.tasks.subtasks')),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/boards/{id}",
     *     summary="Delete a board",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Board deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Board deleted successfully!")
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
     *         description="Failed deleting a board",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(Board $board): JsonResponse
    {
        try {
            $user = Auth::user();

            $this->authorize('delete', $board);

            if (! $user || $board->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 401);
            }

            $board->delete();

            return response()->json([
                'success' => true,
                'message' => 'Board deleted successfully!',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'This action is unauthorized.',
                'error' => $e->getMessage(),
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function active(): JsonResponse
    {
        try {
            $board = Board::getActiveBoard(Auth::id());

            if (! $board instanceof \App\Models\Board) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active boards found.',
                ], 404);
            }

            return response()->json([
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching for active board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/boards/{id}/active",
     *     summary="Sets a board as active",
     *     tags={"Boards"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Board set as active",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Board set as active."),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(
     *                     property="board",
     *                     ref="#/components/schemas/Board"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to set board as active",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function setActive(Board $board): JsonResponse
    {
        try {
            $this->authorize('update', $board);

            $board->activate();

            $board->load([
                'columns.tasks.subtasks',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Board set as active.',
                'data' => [
                    'board' => new BoardResource($board),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set active board.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
