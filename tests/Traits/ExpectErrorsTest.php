<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\ExpectErrors;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @note It's not possible to test if exception is not properly captured
 *  because the PHPUnit throws an exception for that result after test.
 *
 * @internal
 */
final class ExpectErrorsTest extends TestCase
{
    use ExpectErrors;

    /**
     * Test if `expectErrorBase` catches `BadRequestException`.
     *
     * @throws BadRequestException
     */
    #[Test]
    public function testExpectErrorBase(): void
    {
        $this->expectErrorBase(400);

        throw new BadRequestException('Test', 400);
    }

    /**
     * Test if `expectErrorBase` catches `BadRequestException` (with message).
     *
     * @throws BadRequestException
     */
    #[Test]
    public function testExpectErrorBaseMessage(): void
    {
        $this->expectErrorBase(444, 'Test message');

        throw new BadRequestException('Test message', 444);
    }

    /**
     * Test if `expectErrorNotFound` catches `BadRequestException`.
     *
     * @throws BadRequestException
     */
    #[Test]
    public function testExpectErrorNotFound(): void
    {
        $this->expectErrorNotFound();

        throw new BadRequestException('Test', 404);
    }

    /**
     * Test if `expectErrorNotFound` catches `BadRequestException` (with message).
     *
     * @throws BadRequestException
     */
    #[Test]
    public function testExpectErrorNotFoundMessage(): void
    {
        $this->expectErrorNotFound('Test message');

        throw new BadRequestException('Test message', 404);
    }

    /**
     * Test if `expectErrorBadRequest` catches `BadRequestException`.
     *
     * @throws BadRequestException
     */
    public function testExpectErrorBadRequest(): void
    {
        $this->expectErrorBadRequest();

        throw new BadRequestException('Test', 400);
    }

    /**
     * Test if `expectErrorBadRequest` catches `BadRequestException` (with message).
     *
     * @throws BadRequestException
     */
    public function testExpectErrorBadRequestMessage(): void
    {
        $this->expectErrorBadRequest('Testovací zpráva');

        throw new BadRequestException('Testovací zpráva', 400);
    }

    /**
     * Test if `expectErrorConflict` catches `BadRequestException`.
     *
     * @throws BadRequestException
     */
    public function testExpectErrorConflict(): void
    {
        $this->expectErrorConflict();

        throw new BadRequestException('Test', 409);
    }

    /**
     * Test if `expectErrorConflict` catches `BadRequestException` (with message).
     *
     * @throws BadRequestException
     */
    public function testExpectErrorConflictMessage(): void
    {
        $this->expectErrorConflict('Testovací zpráva');

        throw new BadRequestException('Testovací zpráva', 409);
    }

    /**
     * Test if `expectForbidden` catches `ForbiddenRequestException`.
     *
     * @throws ForbiddenRequestException
     */
    #[Test]
    public function testExpectForbidden(): void
    {
        $this->expectForbidden();

        throw new ForbiddenRequestException('Test');
    }

    /**
     * Test if `expectForbidden` catches `ForbiddenRequestException` (with message).
     *
     * @throws ForbiddenRequestException
     */
    #[Test]
    public function testExpectForbiddenMessage(): void
    {
        $this->expectForbidden('Test message');

        throw new ForbiddenRequestException('Test message');
    }
}
