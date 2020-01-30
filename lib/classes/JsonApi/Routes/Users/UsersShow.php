<?php

namespace JsonApi\Routes\Users;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Schemas\User as UserSchema;

class UsersShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        UserSchema::REL_ACTIVITYSTREAM,
        UserSchema::REL_CONTACTS,
        UserSchema::REL_COURSES,
        UserSchema::REL_COURSE_MEMBERSHIPS,
        UserSchema::REL_EVENTS,
        UserSchema::REL_INSTITUTE_MEMBERSHIPS,
        UserSchema::REL_SCHEDULE,
    ];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (isset($args['id'])) {
            $observedUser = \User::find($args['id']);
        } else {
            $observedUser = $this->getUser($request);
        }
        if (!$observedUser) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowUser($this->getUser($request), $observedUser)) {
            // absichtlich keine AuthorizationFailedException
            // damit unsichtbare Nutzer nicht ermittelt werden können
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($observedUser);
    }
}
