<?php

namespace JsonApi\Routes\CourseMemberships;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\CourseMember as CourseMemberSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Edits membership in a course.
 */
class CourseMembershipsUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $membership = self::findMembership($args['id']);

        $json = $this->validate($request, $membership);

        if (!Authority::canEditMemberships($this->getUser($request), $membership)) {
            throw new AuthorizationFailedException();
        }

        if (!$membership = $this->updateMembershipFromJSON($membership, $json)) {
            throw new InternalServerError('Could not update news.');
        }

        return $this->getContentResponse($membership);
    }

    private function findMembership($id)
    {
        if (!preg_match('/^([^_]+)_(.+)$/', $id, $matches)) {
            throw new BadRequestException();
        }

        if (!$membership = \CourseMember::find([$matches[1], $matches[2]])) {
            throw new RecordNotFoundException();
        }

        return $membership;
    }

    protected function updateMembershipFromJSON($membership, array $json)
    {
        $getField = function ($key, $default = null) use ($json) {
            return self::arrayGet($json, 'data.attributes.'.$key, $default);
        };

        if (self::arrayHas($json, 'data.attributes.group')) {
            $membership->gruppe = (int) $getField('group');
        }
        if (self::arrayHas($json, 'data.attributes.visible')) {
            $membership->visible = $getField('visible');
        }

        $membership->store();

        return $membership;
    }

    protected function validateResourceDocument($json, \CourseMember $membership)
    {
        if (CourseMemberSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Missing or wrong type.';
        }

        if (self::arrayHas($json, 'data.attributes.group')) {
            $group = self::arrayGet($json, 'data.attributes.group');
            if (!is_int($group)) {
                return 'Attribute `group` has to be an integer.';
            }
            if ($group < 0 || $group > 9) {
                return 'Attribute `group` has to be an integer between 0 and 9.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.visible')) {
            $visible = self::arrayGet($json, 'data.attributes.visible');

            if (!in_array($visible, ['yes', 'no'])) {
                return 'Attribute `visible` must be either `yes` or `no`';
            }

            if ('no' === $visible && in_array($membership->status, ['tutor', 'dozent'])) {
                return 'Users of status `tutor` or `dozent` must remain visible.';
            }
        }
    }
}
