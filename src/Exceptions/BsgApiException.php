<?php

declare(strict_types=1);

namespace Andriichuk\Bsg\Exceptions;

use RuntimeException;

final class BsgApiException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly string $errorDescription,
    ) {
        parent::__construct("BSG API error {$errorCode}: {$errorDescription}");
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response): self
    {
        return new self(
            errorCode: (string) ($response['error'] ?? 'unknown'),
            errorDescription: (string) ($response['errorDescription'] ?? $response['description'] ?? $response['message'] ?? 'Unknown error'),
        );
    }
}
