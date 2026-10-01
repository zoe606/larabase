<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\HttpStatus;
use Exception;
use Illuminate\Http\JsonResponse;

abstract class DomainException extends Exception
{
    /**
     * @param  array<string, mixed>|null  $errors
     */
    public function __construct(
        string $message,
        public readonly HttpStatus $status = HttpStatus::BAD_REQUEST,
        public readonly ?array $errors = null,
    ) {
        parent::__construct($message, $status->value);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->status->value);
    }

    public function report(): bool
    {
        // Return false to let Laravel's default logging handle it
        // Return true to prevent logging (for expected exceptions)
        return $this->status->isClientError();
    }
}
