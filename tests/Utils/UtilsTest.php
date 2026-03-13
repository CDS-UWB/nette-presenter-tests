<?php

declare(strict_types=1);

namespace Tests\Utils;

use Cds\NettePresenterTests\Utils\Utils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase
{
    /**
     * Value building presenter name from class.
     *
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('dataBuildNameFromPresenter')]
    public function buildNameFromPresenter(string $class, string $expected): void
    {
        $result = Utils::buildNameFromPresenter($class);

        self::assertEquals($expected, $result);
    }

    /**
     * @return array<array{string, string}>
     */
    public static function dataBuildNameFromPresenter(): array
    {
        return [
            ['App\Presenters\Test1Presenter', 'Test1'],
            ['App\Modules\Mod2\Presenters\Test3Presenter', 'Mod2:Test3'],
            ['App\Test9Module\Presenters\Test6Presenter', 'Test9:Test6'],
            ['Foo\Test1Module\Test2Presenter', 'Test1:Test2'],
            ['Bar\Test2Module\Test3Presenter', 'Test2:Test3'],
        ];
    }
}
