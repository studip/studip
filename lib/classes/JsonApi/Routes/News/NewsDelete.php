<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Löscht eine Studip-Nachricht.
 */
class NewsDelete extends JsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$news = \StudipNews::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeleteNews($user = $this->getUser($request), $news)) {
            throw new AuthorizationFailedException();
        }

        if (!$this->deleteNews($news)) {
            throw new RecordNotFoundException();
        }

        return $this->getCodeResponse(204);
    }

    protected function deleteNews(\StudipNews $news)
    {
        return $news->delete();
    }
}
