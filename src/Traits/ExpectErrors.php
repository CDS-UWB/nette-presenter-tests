<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Http\IResponse;

/**
 * Trait for error expectations for presenters testing.
 */
trait ExpectErrors
{
    /**
     * Set expectation for raising BadRequestException.
     */
    public function expectErrorBase(int $code, null|string $message = null): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionCode($code);

        if ($message !== null) {
            $this->expectExceptionMessage($message);
        }
    }

    /**
     * Set expectation for calling `error` method.
     */
    public function expectErrorNotFound(null|string $message = null): void
    {
        $this->expectErrorBase(IResponse::S404_NotFound, $message);
    }

    /**
     * Set expectation for calling `error` method with code 400.
     */
    public function expectErrorBadRequest(null|string $message = null): void
    {
        $this->expectErrorBase(IResponse::S400_BadRequest, $message);
    }

    /**
     * Set expectation for calling `error` method with code 409.
     */
    public function expectErrorConflict(null|string $message = null): void
    {
        $this->expectErrorBase(IResponse::S409_Conflict, $message);
    }

    /**
     * Set expectation for raising ForbiddenRequestException.
     */
    public function expectForbidden(null|string $message = null): void
    {
        $this->expectException(ForbiddenRequestException::class);

        if ($message !== null) {
            $this->expectExceptionMessage($message);
        }
    }
}
