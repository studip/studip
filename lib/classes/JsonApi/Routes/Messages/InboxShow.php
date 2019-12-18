<?php

namespace JsonApi\Routes\Messages;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Liefert den Posteingang eines Nutzers zurück.
 */
class InboxShow extends BoxController
{
    protected $allowedFilteringParameters = ['unread'];

    public function __invoke(Request $request, Response $response, $args)
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters();
        $onlyUnread = (bool) $filtering['unread'];

        return $this->getBoxResponse($request, $args, 'rec', $onlyUnread);
    }
}
