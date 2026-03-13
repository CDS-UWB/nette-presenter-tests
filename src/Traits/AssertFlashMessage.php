<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

/**
 * Trait for checking flash message dispatch.
 */
trait AssertFlashMessage
{
    /**
     * Verifies that a flash message was dispatched.
     *
     * @param non-empty-string $message
     * @param non-empty-string $type
     */
    protected function assertFlashMessage(string $message, string $type = 'info'): void
    {
        self::assertContainsEquals((object) [
            'message' => $message,
            'type' => $type,
        ], $this->presenter->getTemplate()->flashes);
    }
}
