<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests;

use Cds\NettePresenterTests\Traits\Presenter;
use Nette\Application\UI\Presenter as NettePresenter;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @codeCoverageIgnore
 */
abstract class PresenterTestCase extends PHPUnitTestCase
{
    use Presenter;

    protected Container $container;

    protected NettePresenter $presenter;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = $this->configureContainer();
    }

    abstract protected function configureContainer(): Container;
}
