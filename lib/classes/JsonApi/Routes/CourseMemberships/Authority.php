<?php

namespace JsonApi\Routes\CourseMemberships;

use User;

class Authority
{
    public static function canIndexMembershipsOfUser(User $observer, User $user)
    {
        return $observer->id === $user->id;
    }

    public static function canShowMemberships(User $observer, \CourseMember $membership)
    {
        return $membership->user_id === $observer->id;
    }

    public static function canEditMemberships(User $observer, \CourseMember $membership)
    {
        return $membership->user_id === $observer->id;
    }
}
