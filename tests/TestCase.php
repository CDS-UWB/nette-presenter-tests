<?php

declare(strict_types=1);

namespace Tests;

use Cds\NettePresenterTests\Utils\TestRouter;
use Nette\Bootstrap\Configurator;
use Nette\Caching\Storages\MemoryStorage;
use Nette\DI\Container;

/**
 * @internal
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function configureContainer(): Container
    {
        $tempDirectory = __DIR__ . '/temp';

        $configurator = new Configurator();
        $configurator->setDebugMode(true);
        $configurator->setTempDirectory($tempDirectory);

        $configurator->addStaticParameters([
            'tempDir' => $tempDirectory,
        ]);

        $configurator->addConfig([
            'services' => [
                'cache.storage' => MemoryStorage::class,
                'router' => TestRouter::class,
            ],
        ]);

        return $configurator->createContainer();
    }
}
