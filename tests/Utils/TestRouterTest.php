<?php

declare(strict_types=1);

namespace Tests\Utils;

use Cds\NettePresenterTests\Utils\TestRouter;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(TestRouter::class)]
final class TestRouterTest extends TestCase
{
    private TestRouter $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->router = new TestRouter();
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function match(): void
    {
        self::assertNull($this->router->match($this->createStub(IRequest::class)));
    }

    /**
     * @param array<string, mixed> $args
     */
    #[Test]
    #[DataProvider('dataConstructUrl')]
    public function constructUrl(array $args, string $expected): void
    {
        $url = $this->router->constructUrl($args, new UrlScript('http://localhost'));

        self::assertEquals($expected, $url);
    }

    /**
     * @return array<int, array{array<string, mixed>, string}>
     */
    public static function dataConstructUrl(): array
    {
        return [
            [['presenter' => 'Test1'], 'Test1:default'],
            [['presenter' => 'Test2', 'action' => 'view'], 'Test2:view'],
            [['presenter' => 'Test3', 'action' => 'read', 'id' => 3], 'Test3:read id=3'],
            [['presenter' => 'Test3', 'action' => 'edit', 'id' => 3, 'mode' => 'html'], 'Test3:edit id=3&mode=html'],
        ];
    }
}
