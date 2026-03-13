<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\PresenterCreate;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * @internal
 *
 * @property Container&MockObject $container
 */
final class PresenterCreateTest extends TestCase
{
    use PresenterCreate;

    protected Container&MockObject $container;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(Container::class);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function testCreatePresenter(): void
    {
        $presenter = $this->createStub(Presenter::class);

        $this->container
            ->expects($this->once())
            ->method('createInstance')
            ->with('MyPresenter')
            ->willReturn($presenter)
        ;

        $this->container
            ->expects($this->once())
            ->method('callInjects')
            ->with($presenter)
        ;

        // @phpstan-ignore-next-line
        $result = $this->createPresenter('MyPresenter');

        self::assertSame($result, $presenter);
    }
}
