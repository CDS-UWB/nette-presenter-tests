<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\AssertResponse;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

/**
 * @internal
 */
final class AssertResponseTest extends TestCase
{
    use AssertResponse;

    /**
     * Test if `assertTextResponse` accept `TextResponse`.
     */
    #[Test]
    public function testAssertTextResponse(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertTextResponse(new TextResponse('Hello'));
    }

    /**
     * Test if `assertTextResponse` reject other response.
     */
    #[Test]
    public function testAssertTextResponseOtherResponse(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertTextResponse(new RedirectResponse('page'));
    }

    /**
     * Test if `assertNonEmptyTextResponse` accept `TextResponse` with some content.
     */
    #[Test]
    public function testAssertNonEmptyTextResponse(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertNonEmptyTextResponse(new TextResponse('Hello'));
    }

    /**
     * Test if `assertNonEmptyTextResponse` reject other response.
     */
    #[Test]
    public function testAssertNonEmptyTextResponseOtherResponse(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertNonEmptyTextResponse(new RedirectResponse('page'));
    }

    /**
     * Test if `assertNonEmptyTextResponse` reject empty response.
     */
    #[Test]
    public function testAssertNonEmptyTextResponseEmpty(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertNonEmptyTextResponse(new TextResponse(''));
    }

    /**
     * Test if `assertRedirectResponseTo` accept `RedirectResponse`.
     */
    #[Test]
    public function testAssertRedirectResponseTo(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertRedirectResponseTo('page', new RedirectResponse('page'));
    }

    /**
     * Test if `assertRedirectResponseTo` reject other response.
     */
    #[Test]
    public function testAssertRedirectResponseToOther(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertRedirectResponseTo('page', new TextResponse('Hello'));
    }

    /**
     * Test if `assertRedirectResponseTo` reject `RedirectResponse` with different URL.
     */
    #[Test]
    public function testAssertRedirectResponseToDifferentUrl(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertRedirectResponseTo('page', new RedirectResponse('Hello'));
    }

    /**
     * Test if `assertJsonResponse` accept `JsonResponse`.
     */
    #[Test]
    public function testAssertJsonResponse(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertJsonResponse(new JsonResponse([]));
    }

    /**
     * Test if `assertJsonResponse` reject other response.
     */
    #[Test]
    public function testAssertJsonResponseOther(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertJsonResponse(new TextResponse('Hello'));
    }

    /**
     * Test if `assertJsonResponseWith` accept `JsonResponse` with specified payload.
     */
    #[Test]
    public function testAssertJsonResponseWith(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertJsonResponseWith([1, 2], new JsonResponse([1, 2]));
    }

    /**
     * Test if `assertJsonResponseWith` reject other response.
     */
    #[Test]
    public function testAssertJsonResponseWithOtherResponse(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertJsonResponseWith([], new TextResponse('Hello'));
    }

    /**
     * Test if `assertJsonResponseWith` reject `JsonResponse` with different payload.
     */
    #[Test]
    public function testAssertJsonResponseWithDifferentPayload(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertJsonResponseWith([], new JsonResponse([1, 2]));
    }

    /**
     * Tests response validation.
     */
    #[Test]
    public function testAssertForwardResponse(): void
    {
        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertForwardResponse(new ForwardResponse(new Request('page')));
    }

    /**
     * Tests response validation.
     */
    #[Test]
    public function testAssertForwardResponseFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertForwardResponse(new TextResponse('Hello'));
    }

    /**
     * Tests response validation.
     */
    #[Test]
    public function testAssertForwardResponseTo(): void
    {
        $response = new ForwardResponse(new Request('page', params: ['id' => 3]));

        // @phpstan-ignore staticMethod.alreadyNarrowedType
        self::assertForwardResponseTo('page', ['id' => 3], $response);
    }

    /**
     * Tests response validation.
     */
    #[Test]
    public function testAssertForwardResponseToFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        // @phpstan-ignore staticMethod.impossibleType
        self::assertForwardResponseTo('page', ['id' => 3], new TextResponse('Hello'));
    }
}
