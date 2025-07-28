<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Boards",
 *     description="Board-related operations"
 * )
 */
class Boards
{
    /**
     * @OA\Get(
     *     path="/api/boards",
     *     summary="List all user boards",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
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
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
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
    public function index(): void
    {
        //
    }

    /**
     *      @OA\Get(
     *           path="/api/boards/{id}",
     *           summary="Get board details",
     *           description="Retrieves full details of a specific board including columns, tasks and subtasks",
     *           operationId="getBoardById",
     *           tags={"Boards"},
     *           security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the board to retrieve",
     *         example=1,
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
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *      @OA\Response(
     *         response=404,
     *         description="Board not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Board not found")
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
    public function show(): void
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/boards",
     *     operationId="createBoard",
     *     summary="Create board with columns",
     *     description="Creates a new board with initial columns in a single atomic operation",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Board data with initial columns",
     *
     *         @OA\JsonContent(
     *             required={"name", "columns"},
     *
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="My Board",
     *                 description="Board name. Must be unique per user and contain between 3 and 50 characters."
     *             ),
     *             @OA\Property(
     *                 property="columns",
     *                 type="array",
     *                 description="List of initial columns that will be created along with the board.",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="name", type="string", description="Column name. Ex: 'To Do', 'In Progress', 'Done'", example="To Do"),
     *                     @OA\Property(property="order", type="integer", description="Defines the column's position on the board (lower values appear first).", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Board created successfully",
     *
     *         @OA\JsonContent(
     *             allOf={
     *
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *
     *                     @OA\Property(
     *                         property="data",
     *                         @OA\Property(
     *                             property="board",
     *                             ref="#/components/schemas/Board"
     *                         )
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/schemas/ValidationErrorResponse"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create a board",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(): void
    {
        //
    }

    /**
     *     @OA\Put(
     *        path="/api/boards/{id}",
     *        summary="Update an existing board with columns",
     *        tags={"Boards"},
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
     *                 description="List of columns that will be updated along with the board.",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", description="Column. Ex: 'To Do', 'In Progress', 'Done'", example="To Do"),
     *                     @OA\Property(property="order", type="integer", description="Column order on the board (smallest values come first).", example=1)
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
     *                 type="object",
     *                 @OA\Property(
     *                     property="board",
     *                     ref="#/components/schemas/Board"
     *                 )
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *      @OA\Response(
     *         response=404,
     *         description="Board not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Board] 5")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/schemas/ValidationErrorResponse"
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="Failed to update board",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(): void
    {
        //
    }

    /**
     *      @OA\Delete(
     *     path="/api/boards/{id}",
     *     summary="Delete a board",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
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
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *      @OA\Response(
     *         response=404,
     *         description="Board not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Board] 5")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/schemas/ValidationErrorResponse"
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="Failed deleting a board",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(): void
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/boards/active",
     *     summary="Get the currently active board",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Active board retrieved successfully",
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
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="No active board found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No active boards found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         ref="#/components/schemas/ValidationErrorResponse"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to get an active Board",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function active(): void
    {
        //
    }

    /**
     * @OA\Patch(
     *     path="/api/boards/{id}/activate",
     *     summary="Sets a board as active",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
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
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=403,
     *         description="Forbidden - User doesn't have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *      @OA\Response(
     *         response=404,
     *         description="Board not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Board] 5")
     *         )
     *     ),
     *
     *      @OA\Response(
     *         response=422,
     *         ref="#/components/schemas/ValidationErrorResponse"
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="Failed to set board as active",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function setActive(): void
    {
        //
    }
}
