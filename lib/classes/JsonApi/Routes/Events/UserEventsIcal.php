<?php

namespace JsonApi\Routes\Events;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserEventsIcal extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$observedUser = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);
        if ($user->id !== $observedUser->id) {
            // absichtlich keine AuthorizationFailedException
            // damit unsichtbare Nutzer nicht ermittelt werden können
            throw new RecordNotFoundException();
        }

        $export = new \CalendarExport(new \CalendarWriterICalendar());
        $export->exportFromDatabase($observedUser->id, 0, 2114377200, ['CalendarEvent', 'CourseEvent', 'CourseCancelledEvent']);
        if ($GLOBALS['_calendar_error']->getMaxStatus(\ErrorHandler::ERROR_CRITICAL)) {
            throw new InternalServerError();
        }

        $content = implode($export->getExport());

        return $response->withHeader('Content-Type', 'text/calendar')
            ->withHeader('Content-Disposition', 'attachment; '.encode_header_parameter('filename', 'studip.ics'))
            ->write($content);
    }
}
