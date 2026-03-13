<?php

declare(strict_types=1);

namespace Tests\Presenters;

use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;

/**
 * Example presenter for testing.
 */
final class TestPresenter extends Presenter
{
    public function actionDefault(): void
    {
        // Nothing
    }

    public function actionDetail(int $id): void
    {
        // Nothing
    }

    /**
     * @throws AbortException
     */
    public function actionRedirect(int $id): never
    {
        $this->redirect('detail', $id);
    }
}
