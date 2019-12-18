<?php

namespace JsonApi\Routes\Messages;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Liefert den Posteingang eines Nutzers zurück.
 */
class OutboxShow extends BoxController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        return $this->getBoxResponse($request, $args, 'snd');
    }
}
