<?php

declare(strict_types=1);

namespace Tests;

use Cds\NettePresenterTests\Traits\Presenter;
use Nette\DI\Container;
use Tests\Presenters\TestPresenter;

/**
 * @internal
 */
final class TestPresenterTest extends TestCase
{
    use Presenter;

    private TestPresenter $presenter;

    private Container $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = $this->configureContainer();

        $this->presenter = $this->createPresenter(TestPresenter::class);
        $this->presenter->autoCanonicalize = false;
    }

    public function testActionDefault(): void
    {
        $response = $this->request('default');

        self::assertNonEmptyTextResponse($response);
    }

    public function testActionDetail(): void
    {
        $response = $this->request('detail', ['id' => 612]);

        self::assertNonEmptyTextResponse($response);
    }

    public function testActionRedirect(): void
    {
        $response = $this->request('redirect', ['id' => 42]);

        self::assertRedirectResponseTo('Test:detail id=42', $response);
    }
}
