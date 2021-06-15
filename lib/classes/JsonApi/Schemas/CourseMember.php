<?php

namespace JsonApi\Schemas;

use JsonApi\Routes\Courses\Authority as CourseAuthority;
use JsonApi\Routes\CourseMemberships\Authority as MembershipAuthority;
use Neomerx\JsonApi\Document\Link;

class CourseMember extends SchemaProvider
{
    const TYPE = 'course-memberships';
    const REL_COURSE = 'course';
    const REL_USER = 'user';

    protected $resourceType = self::TYPE;

    public function getId($membership)
    {
        return $membership->id;
    }

    public function getAttributes($membership)
    {
        $attributes = [
            'permission' => $membership->status,
            'position' => (int) $membership->position,
            'group' => (int) $membership->gruppe,
            'mkdate' => date('c', $membership->mkdate),
            'label' => $membership->label,
        ];
        // TODO: "bind_calendar": "1",

        if ($user = $this->getDiContainer()->get('studip-current-user')) {
            if (MembershipAuthority::canIndexMembershipsOfUser($user, $membership->user)) {
                # TODO: $attributes['notification'] = (int) $membership->notification;
                $attributes['visible'] = $membership->visible;
            }
            if (CourseAuthority::canEditCourse($user, $membership->course)) {
                $attributes['comment'] = $membership->comment;
                $attributes['visible'] = $membership->visible;
            }
        }

        return $attributes;
    }

    public function getRelationships($membership, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($isPrimary) {
            $relationships[self::REL_COURSE] = [
                self::LINKS => [
                    Link::RELATED => new Link('/courses/'.$membership->seminar_id),
                ],
                self::DATA => $membership->course,
            ];

            $relationships[self::REL_USER] = [
                self::LINKS => [
                    Link::RELATED => new Link('/users/'.$membership->user_id),
                ],
                self::DATA => $membership->user,
            ];
        }

        return $relationships;
    }
}
