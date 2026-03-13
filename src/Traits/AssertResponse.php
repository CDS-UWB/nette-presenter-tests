<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

use Nette\Application\Response;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Stringable;

/**
 * Trait for additional assertions for testing presenter responses.
 */
trait AssertResponse
{
    /**
     * Check if given Nette response is TextResponse.
     *
     * @phpstan-assert TextResponse $response
     *
     * @psalm-assert TextResponse $response
     */
    public static function assertTextResponse(Response $response, string $message = ''): void
    {
        self::assertInstanceOf(TextResponse::class, $response, $message);
    }

    /**
     * Check if given Nette response is non-empty TextResponse.
     *
     * @phpstan-assert TextResponse $response
     *
     * @psalm-assert TextResponse $response
     */
    public static function assertNonEmptyTextResponse(Response $response, string $message = ''): void
    {
        self::assertTextResponse($response, $message);

        // If response is template, it will force to render the template.
        /** @var scalar|Stringable $source */
        $source = $response->getSource();

        self::assertNotEmpty((string) $source);
    }

    /**
     * Check if given Nette response is redirect response with expected URL.
     *
     * @param string $url Nette presenter URL, e.g. 'Presenter:action arg=1'.
     *
     * @phpstan-assert RedirectResponse $response
     *
     * @psalm-assert RedirectResponse $response
     */
    public static function assertRedirectResponseTo(string $url, Response $response, string $message = ''): void
    {
        self::assertInstanceOf(RedirectResponse::class, $response, $message);
        self::assertEquals($url, $response->getUrl(), $message);
    }

    /**
     * Check if given Nette response is JSON response.
     *
     * @phpstan-assert JsonResponse $response
     *
     * @psalm-assert JsonResponse $response
     */
    public static function assertJsonResponse(Response $response, string $message = ''): void
    {
        self::assertInstanceOf(JsonResponse::class, $response, $message);
    }

    /**
     * Check if given Nette response is JSON response with expected payload.
     *
     * @phpstan-assert JsonResponse $response
     *
     * @psalm-assert JsonResponse $response
     *
     * @param mixed $payload the expected payload
     */
    public static function assertJsonResponseWith(mixed $payload, Response $response, string $message = ''): void
    {
        self::assertJsonResponse($response, $message);
        self::assertEquals($payload, $response->getPayload(), $message);
    }

    /**
     * Tests whether the response is forward.
     *
     * @phpstan-assert ForwardResponse $response
     *
     * @psalm-assert ForwardResponse $response
     */
    public static function assertForwardResponse(Response $response, string $message = ''): void
    {
        self::assertInstanceOf(ForwardResponse::class, $response, $message);
    }

    /**
     * Tests whether the response is forward.
     *
     * @param array<string, mixed> $params
     *
     * @phpstan-assert ForwardResponse $response
     *
     * @psalm-assert ForwardResponse $response
     */
    public static function assertForwardResponseTo(string $presenterName, array $params, Response $response, string $message = ''): void
    {
        self::assertForwardResponse($response, $message);
        self::assertSame($presenterName, $response->getRequest()->getPresenterName());
        self::assertSame($params, $response->getRequest()->getParameters());
    }
}
