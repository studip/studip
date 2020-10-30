<?php

namespace JsonApi\Routes\News;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;

/*
* Get a all news of a course
*/
class ByCourseIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];
    protected $allowedIncludePaths = ['author', 'ranges'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canIndexNewsOfCourse($this->getUser($request), $course)) {
            throw new AuthorizationFailedException();
        }

        $news = \StudipNews::GetNewsByRange($course->id, true, true);
        list($offset, $limit) = $this->getOffsetAndLimit();


        return $this->getPaginatedContentResponse(
            array_slice($news, $offset, $limit),
            count($news)
        );
    }
}
