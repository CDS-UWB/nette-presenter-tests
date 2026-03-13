<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Utils;

enum FormSubmitMethod: string
{
    case Post = 'POST';
    case Get = 'GET';
}
