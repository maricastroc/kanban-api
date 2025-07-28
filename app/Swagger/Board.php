<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Boards",
 *     description="Board-related operations"
 * )
 */
class Board
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
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User doesn't have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Internal error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(): void {}

    /**
     * @OA\Get(
     *     path="/api/boards/{id}",
     *     summary="Get board details",
     *     description="Retrieves full details of a specific board including columns, tasks and subtasks",
     *     operationId="getBoardById",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="ID of the board", example=1, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Board data", @OA\JsonContent(@OA\Property(property="data", @OA\Property(property="board", ref="#/components/schemas/Board")))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User doesn't have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Board not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Board not found"))),
     *     @OA\Response(response=500, description="Internal error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(): void {}

    /**
     * @OA\Post(
     *     path="/api/boards",
     *     operationId="createBoard",
     *     summary="Create board with columns",
     *     description="Creates a new board with initial columns",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Board data with columns",
     *
     *         @OA\JsonContent(
     *             required={"name", "columns"},
     *
     *             @OA\Property(property="name", type="string", example="My Board"),
     *             @OA\Property(property="columns", type="array", @OA\Items(
     *                 @OA\Property(property="name", type="string", example="To Do"),
     *                 @OA\Property(property="order", type="integer", example=1)
     *             ))
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Board created", @OA\JsonContent(allOf={@OA\Schema(ref="#/components/schemas/SuccessResponse"), @OA\Schema(@OA\Property(property="data", @OA\Property(property="board", ref="#/components/schemas/Board")))})),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User does not have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, ref="#/components/schemas/ValidationErrorResponse"),
     *     @OA\Response(response=500, description="Failed to create", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(): void {}

    /**
     * @OA\Put(
     *     path="/api/boards/{id}",
     *     summary="Update an existing board",
     *     tags={"Boards"},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="Updated board"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="columns", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="To Do"),
     *                 @OA\Property(property="order", type="integer", example=1)
     *             ))
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Board updated successfully!"), @OA\Property(property="data", @OA\Property(property="board", ref="#/components/schemas/Board")))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User does not have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Board not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Not found"))),
     *     @OA\Response(response=422, ref="#/components/schemas/ValidationErrorResponse"),
     *     @OA\Response(response=500, description="Failed to update", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(): void {}

    /**
     * @OA\Delete(
     *     path="/api/boards/{id}",
     *     summary="Delete a board",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Deleted!"))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User does not have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Not found"))),
     *     @OA\Response(response=422, ref="#/components/schemas/ValidationErrorResponse"),
     *     @OA\Response(response=500, description="Failed to delete", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(): void {}

    /**
     * @OA\Get(
     *     path="/api/boards/active",
     *     summary="Get the active board",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Active board", @OA\JsonContent(@OA\Property(property="data", @OA\Property(property="board", ref="#/components/schemas/Board")))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User does not have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="No active boards found."))),
     *     @OA\Response(response=422, ref="#/components/schemas/ValidationErrorResponse"),
     *     @OA\Response(response=500, description="Failed to retrieve", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function active(): void {}

    /**
     * @OA\Patch(
     *     path="/api/boards/{id}/activate",
     *     summary="Set board as active",
     *     tags={"Boards"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Activated", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true), @OA\Property(property="message", type="string", example="Board set as active."), @OA\Property(property="data", @OA\Property(property="board", ref="#/components/schemas/Board")))),
     *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(@OA\Property(property="message", type="string", example="Unauthenticated"))),
     *     @OA\Response(response=403, description="Forbidden - User does not have permission", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Board not found"))),
     *     @OA\Response(response=422, ref="#/components/schemas/ValidationErrorResponse"),
     *     @OA\Response(response=500, description="Failed to set active", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function setActive(): void {}
}
