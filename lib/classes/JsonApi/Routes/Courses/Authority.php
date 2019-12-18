<?php

namespace JsonApi\Routes\Courses;

use Course;
use User;

class Authority
{
    const SCOPE_BASIC = 'basic';
    const SCOPE_EXTENDED = 'extended';

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canShowCourse(User $user, Course $course, $scope)
    {
        switch ($scope) {
        case self::SCOPE_BASIC:
            return
                // visible
                ((int) $course->visible) || $GLOBALS['perm']->have_perm(\Config::get()->SEM_VISIBILITY_PERM)
                // member
                || $GLOBALS['perm']->have_studip_perm('user', $course->id, $user->id);

        case self::SCOPE_EXTENDED:
            return $GLOBALS['perm']->have_studip_perm('user', $course->id, $user->id);
        }

        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canEditCourse(User $user, Course $course)
    {
        return $GLOBALS['perm']->have_studip_perm('dozent', $course->id, $user->id);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canIndexCourses(User $user)
    {
        return true;
    }

    public static function canIndexMemberships(User $user, Course $course)
    {
        return self::canShowCourse($user, $course, self::SCOPE_EXTENDED);
    }

    public static function canIndexMembershipsOfUser(User $observer, User $user)
    {
        return $observer->id === $user->id;
    }
}
