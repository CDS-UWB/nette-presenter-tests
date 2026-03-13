<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

use Nette\Application\UI\Presenter;
use Nette\DI\Container;

/**
 * Trait for creating presenter from class-name.
 *
 * @property Container $container
 */
trait PresenterCreate
{
    /**
     * Create a presenter instance.
     *
     * @template T of Presenter
     *
     * @param class-string<T> $class presenter class name
     *
     * @return T&Presenter
     */
    protected function createPresenter(string $class): Presenter
    {
        /** @var T&Presenter $instance */
        $instance = $this->container->createInstance($class);

        $this->container->callInjects($instance);

        return $instance;
    }
}
