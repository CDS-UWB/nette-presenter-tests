<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Traits;

/**
 * Union trait for presenter testing.
 */
trait Presenter
{
    use AssertResponse;
    use ExpectErrors;
    use PresenterRequest;
    use PresenterCreate;
}
