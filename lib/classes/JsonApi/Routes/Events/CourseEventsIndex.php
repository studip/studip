<?php

namespace JsonApi\Routes\Events;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\JsonApiController;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courses\Authority as CourseAuthority;

class CourseEventsIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['owner'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$course = \Course::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);

        if (!CourseAuthority::canShowCourse($user, $course, CourseAuthority::SCOPE_EXTENDED)) {
            throw new AuthorizationFailedException();
        }

        $dates = $course->dates->map(function ($courseDate) {
            return new \CourseEvent($courseDate->id);
        });
        $exDates = $course->ex_dates->map(function ($courseDate) {
            return new \CourseCancelledEvent($courseDate->id);
        });

        $allDates = array_merge($dates, $exDates);
        usort($allDates, function ($date1, $date2) {
            return intval($date1->date) <=> intval($date2->date);
        });
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(array_slice($allDates, $offset, $limit), count($allDates));
    }
}
