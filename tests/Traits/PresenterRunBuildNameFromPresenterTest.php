<?php

declare(strict_types=1);

namespace Tests\Traits;

use Cds\NettePresenterTests\Traits\PresenterRun;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Verifies that buildNameFromPresenter can be overridden by a TestCase.
 *
 * @internal
 */
final class PresenterRunBuildNameFromPresenterTest extends TestCase
{
    use PresenterRun;

    private const CustomName = 'CustomPresenterName';

    protected Presenter&MockObject $presenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = $this->createMock(Presenter::class);
    }

    #[Test]
    public function testBuildNameFromPresenterOverrideUsedByRunPresenter(): void
    {
        $this->presenter
            ->expects($this->once())
            ->method('run')
            ->with(self::callback(static function (Request $request) {
                self::assertEquals(self::CustomName, $request->getPresenterName());

                return true;
            }))
        ;

        $this->runPresenter($this->presenter);
    }

    #[Test]
    public function testBuildNameFromPresenterOverrideUsedByRunPresenterForm(): void
    {
        $this->presenter
            ->expects($this->once())
            ->method('run')
            ->with(self::callback(static function (Request $request) {
                self::assertEquals(self::CustomName, $request->getPresenterName());

                return true;
            }))
        ;

        $form = new \Nette\Forms\Form();
        $form->addText('key1');

        $this->presenter
            ->method('offsetGet')
            ->willReturnMap([
                ['testForm', $form],
            ])
        ;

        $this->runPresenterForm($this->presenter, [], ['key1' => 'value1'], 'testForm');
    }

    protected function buildNameFromPresenter(string $presenterClass): string
    {
        return self::CustomName;
    }
}
