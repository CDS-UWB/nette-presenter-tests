<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Utils;

use Nette\StaticClass;

/**
 * Helper methods.
 */
class Utils
{
    use StaticClass;

    /**
     * Build presenter name from presenter class.
     *
     * It will test only some known patterns of presenter naming:
     *  - App\Presenters\Test1Presenter
     *  - App\Modules\Mod2\Presenters\Test3Presenter
     *  - App\Test9Module\Presenters\Test6Presenter
     *
     * @param class-string $class
     */
    public static function buildNameFromPresenter(string $class): string
    {
        $parts = [];

        $class = str_replace('Modules\\', 'Modules~', $class);

        foreach (explode('\\', $class) as $part) {
            if (preg_match('/[a-zA-Z\d]+Module/', $part)) {
                $parts[] = preg_replace('/Module$/', '', $part);
            } elseif (preg_match('/Modules~[a-zA-Z\d]+/', $part)) {
                $parts[] = preg_replace('/^Modules~/', '', $part);
            } elseif (preg_match('/[a-zA-Z\d]+Presenter/', $part)) {
                $parts[] = preg_replace('/Presenter$/', '', $part);
            }
        }

        return implode(':', $parts);
    }
}
