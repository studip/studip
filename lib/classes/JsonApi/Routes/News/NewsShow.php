<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/*
 * Get a single news by id
 */
class NewsShow extends JsonApiController
{
    protected $allowedIncludePaths = ['author', 'ranges'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$news = \StudipNews::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowNews($this->getUser($request), $news)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($news);
    }
}
