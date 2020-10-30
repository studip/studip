<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\JsonApiController;

/*
* Get all global news
*/
class GlobalNewsShow extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];
    protected $allowedIncludePaths = ['author', 'ranges'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$news = \StudipNews::getNewsByRange('studip', true, true)) {
            $news = [];
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($news, $offset, $limit),
            count($news)
        );
    }
}
