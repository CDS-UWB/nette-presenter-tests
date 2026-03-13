<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Utils;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use Nette\Routing\Router;

/**
 * Router that generate routes in way, so they can be tested.
 *
 * The constructed URL is in same style as Nette presenter links, e.g. 'Presenter:action arg=1'.
 */
class TestRouter implements Router
{
    /**
     * @return array<string, mixed>|null
     */
    public function match(IRequest $httpRequest): ?array
    {
        return null;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function constructUrl(array $params, UrlScript $refUrl): ?string
    {
        $args = $params;
        unset($args['presenter'], $args['action'], $args[Presenter::FlashKey]);

        $suffix = http_build_query($args);
        if (!empty($suffix)) {
            $suffix = ' ' . $suffix;
        }

        return $params['presenter'] . ':' . ($params['action'] ?? 'default') . $suffix;
    }
}
