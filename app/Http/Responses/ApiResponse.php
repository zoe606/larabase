<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class ApiResponse
{
    /**
     * Return a success response.
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        HttpStatus $status = HttpStatus::OK,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status->value);
    }

    /**
     * Return an error response.
     */
    public static function error(
        string $message,
        HttpStatus $status = HttpStatus::BAD_REQUEST,
        mixed $errors = null,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status->value);
    }

    /**
     * Return a paginated response.
     */
    public static function paginated(
        ResourceCollection $resource,
        string $message = 'Success',
    ): JsonResponse {
        $paginator = $resource->resource;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Return a created response (201).
     */
    public static function created(
        mixed $data = null,
        string $message = 'Created successfully',
    ): JsonResponse {
        return self::success($data, $message, HttpStatus::CREATED);
    }

    /**
     * Return a no content response (204).
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, HttpStatus::NO_CONTENT->value);
    }

    /**
     * Return a not found response (404).
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, HttpStatus::NOT_FOUND);
    }

    /**
     * Return an unauthorized response (401).
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, HttpStatus::UNAUTHORIZED);
    }

    /**
     * Return a forbidden response (403).
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, HttpStatus::FORBIDDEN);
    }

    /**
     * Return a validation error response (422).
     */
    public static function validationError(
        mixed $errors,
        string $message = 'Validation failed',
    ): JsonResponse {
        return self::error($message, HttpStatus::UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return a server error response (500).
     */
    public static function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return self::error($message, HttpStatus::INTERNAL_SERVER_ERROR);
    }

    /**
     * Return a too many requests response (429).
     */
    public static function tooManyRequests(string $message = 'Too many requests'): JsonResponse
    {
        return self::error($message, HttpStatus::TOO_MANY_REQUESTS);
    }

    /**
     * Return a conflict response (409).
     */
    public static function conflict(string $message = 'Resource conflict'): JsonResponse
    {
        return self::error($message, HttpStatus::CONFLICT);
    }
}
