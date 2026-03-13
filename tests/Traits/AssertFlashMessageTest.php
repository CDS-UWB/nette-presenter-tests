<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\AssertFlashMessage;
use Nette\Application\UI\Presenter;
use Tests\TestCase;

/**
 * Test of flash message assertion.
 *
 * @internal
 */
final class AssertFlashMessageTest extends TestCase
{
    use AssertFlashMessage;

    private Presenter $presenter;

    public function setUp(): void
    {
        parent::setUp();

        $container = $this->configureContainer();

        $this->presenter = new class() extends Presenter {
        };

        $container->callInjects($this->presenter);
    }

    /**
     * Tests flash message assertion.
     */
    public function testAssertFlashMessage(): void
    {
        $this->presenter->flashMessage('Hello');

        $this->assertFlashMessage('Hello');
    }
}
