<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;

class StatusGroupsOfCourses extends JsonApiController
{
    protected $allowedIncludePaths = [
        'range'
    ];

    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowCourse($this->getUser($request), $course, Authority::SCOPE_BASIC)) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $statusGroups = \Statusgruppen::findBySeminar_id($course->id);
        $total = count($statusGroups);

        return $this->getPaginatedContentResponse(
            array_slice($statusGroups, $offset, $limit),
            $total
        );
    }
}
