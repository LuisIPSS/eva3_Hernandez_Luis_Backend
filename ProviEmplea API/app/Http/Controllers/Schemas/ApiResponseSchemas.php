<?php

namespace App\Http\Controllers\Schemas;

/**
 * Envoltorio estándar de respuestas exitosas de la API.
 *
 * @OA\Schema(
 *     schema="ApiSuccessResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object")
 * )
 */

/**
 * Envoltorio estándar de respuestas de error de la API.
 *
 * @OA\Schema(
 *     schema="ApiErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Los datos enviados no son válidos."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         nullable=true,
 *         example={"email": {"The email field is required."}}
 *     )
 * )
 */
class ApiResponseSchemas
{
}
