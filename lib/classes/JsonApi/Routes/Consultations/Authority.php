<?php

namespace JsonApi\Routes\Consultations;

use ConsultationBlock;
use ConsultationBooking;
use ConsultationSlot;

class Authority
{
    // TODO
    public static function canShowBlubberThread(User $user, BlubberThread $resource)
    {
        return self::userIsAuthor($user) && $resource->isReadable($user->id);
    }

    public static function canCreatePrivateBlubberThread(User $user)
    {
        return self::userIsAuthor($user);
    }

    public static function canCreateComment(User $user, BlubberThread $resource)
    {
        return self::userIsAuthor($user) && $resource->isCommentable($user->id);
    }

    public static function canDeleteComment(User $user, BlubberComment $resource)
    {
        return self::canEditComment($user, $resource);
    }

    public static function canEditComment(User $user, BlubberComment $resource)
    {
        return self::userIsAuthor($user) && $resource->isWritable($user->id);
    }

    public static function canIndexComments(User $user, BlubberThread $resource = null)
    {
        return isset($resource)
            ? self::canShowBlubberThread($user, $resource)
            : self::userIsAuthor($user);
    }

    public static function canShowComment(User $user, BlubberComment $resource)
    {
        return self::canShowBlubberThread($user, $resource->thread);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private static function userIsAuthor(User $user)
    {
        return $GLOBALS['perm']->have_perm('autor', $user->id);
    }
}
