<?php

namespace JsonApi\Routes\Schedule;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Models\ScheduleEntry;
use JsonApi\Routes\Courses\Authority as CourseAuthority;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Zeige einen selbst eingetragenen Eintrag eines Stundenplans.
 */
class SeminarCycleDatesShow extends JsonApiController
{
    protected $allowedIncludePaths = ['owner'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$entry = \SeminarCycleDate::find($args['id'])) {
            throw new RecordNotFoundException('Could not find cycle.');
        }

        if (!$course = \Course::find($entry->seminar_id)) {
            throw new RecordNotFoundException('Could not find course of cycle.');
        }

        $user = $this->getUser($request);
        if (!CourseAuthority::canShowCourse($user, $course, CourseAuthority::SCOPE_EXTENDED)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($entry);
    }
}
