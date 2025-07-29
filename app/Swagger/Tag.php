<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Tags",
 *     description="Tag-related operations"
 * )
 */
class Tag
{
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
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/Tag")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
    public function index(): void {}

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
     *         description="Tag created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag created successfully!"),
     *             @OA\Property(property="data", ref="#/components/schemas/Tag")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
    public function store(): void {}

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
     *             @OA\Property(property="data", ref="#/components/schemas/Tag")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *  @OA\Response(
     *     response=409,
     *     description="Conflict - Resource already in desired state",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ConflictError")
     * ),
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
     * )
     */
    public function attachToTask(): void {}

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
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *  @OA\Response(
     *     response=409,
     *     description="Conflict - Resource already in desired state",
     *
     *     @OA\JsonContent(ref="#/components/schemas/ConflictError")
     * ),
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
    public function detachFromTask(): void {}

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
     *             @OA\Property(property="data", ref="#/components/schemas/Tag")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
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
    public function update(): void {}

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
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden – User does not have permission",
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
    public function destroy(): void {}
}
