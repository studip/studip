<?php

namespace JsonApi\Routes\News;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a news with course-range.
 */
class CourseNewsCreate extends AbstractNewsCreate
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canCreateCourseNews($user, $course)) {
            throw new AuthorizationFailedException();
        }
        if (!$news = $this->createNewsFromJSON($user, $course, $json)) {
            throw new InternalServerError('Could not create news.');
        }

        return $this->getCreatedResponse($news);
    }
}
