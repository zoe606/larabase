<?php

declare(strict_types=1);

namespace App\Enums;

enum HttpStatus: int
{
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE_ENTITY = 422;
    case TOO_MANY_REQUESTS = 429;
    case INTERNAL_SERVER_ERROR = 500;
    case SERVICE_UNAVAILABLE = 503;

    public function message(): string
    {
        return match ($this) {
            self::OK => 'Success',
            self::CREATED => 'Created successfully',
            self::NO_CONTENT => 'No content',
            self::BAD_REQUEST => 'Bad request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Resource not found',
            self::METHOD_NOT_ALLOWED => 'Method not allowed',
            self::CONFLICT => 'Resource conflict',
            self::UNPROCESSABLE_ENTITY => 'Validation failed',
            self::TOO_MANY_REQUESTS => 'Too many requests',
            self::INTERNAL_SERVER_ERROR => 'Internal server error',
            self::SERVICE_UNAVAILABLE => 'Service unavailable',
        };
    }

    public function isSuccess(): bool
    {
        return match ($this) {
            self::OK, self::CREATED, self::NO_CONTENT => true,
            default => false,
        };
    }

    public function isClientError(): bool
    {
        return match ($this) {
            self::BAD_REQUEST, self::UNAUTHORIZED, self::FORBIDDEN, self::NOT_FOUND,
            self::METHOD_NOT_ALLOWED, self::CONFLICT, self::UNPROCESSABLE_ENTITY,
            self::TOO_MANY_REQUESTS => true,
            default => false,
        };
    }

    public function isServerError(): bool
    {
        return match ($this) {
            self::INTERNAL_SERVER_ERROR, self::SERVICE_UNAVAILABLE => true,
            default => false,
        };
    }
}
