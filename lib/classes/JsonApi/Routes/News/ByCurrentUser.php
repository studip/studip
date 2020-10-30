<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/*
* Get all news of current user
*/
class ByCurrentUser extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];
    protected $allowedIncludePaths = ['author', 'ranges'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$observedUser = $this->getUser($request)) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canIndexNewsOfUser($observedUser, $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }
        $news = \StudipNews::GetNewsByAuthor($observedUser->user_id, true);

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($news, $offset, $limit),
            count($news)
        );
    }
}
