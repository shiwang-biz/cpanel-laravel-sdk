<?php

namespace Shiwang\CpanelLaravelSdk\Exceptions;

use RuntimeException;

class WhmRequestException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $response  The full decoded WHM API response.
     */
    public function __construct(
        string $message,
        protected array $response = [],
        protected ?int $httpStatus = null,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function response(): array
    {
        return $this->response;
    }

    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }
}
