<?php

namespace JsonApi\Routes\News;

use User;

class Authority
{
    public static function canShowNews(User $observer, \StudipNews $news)
    {
        return $news->havePermission('view', '', $observer->id);
    }

    public static function canEditNews(User $observer, \StudipNews $news)
    {
        return $news->havePermission('edit', '', $observer->id);
    }

    public static function canCreateUserNews(User $observer, User $user)
    {
        return \StudipNews::haveRangePermission('edit', $user->id, $observer->id);
    }

    public static function canCreateCourseNews(User $observer, \Course $course)
    {
        return \StudipNews::haveRangePermission('edit', $course->id, $observer->id);
    }

    public static function canCreateStudipNews(User $observer)
    {
        return \StudipNews::haveRangePermission('edit', 'studip', $observer->id);
    }

    public static function canIndexNewsOfCourse(User $observer, \Course $course)
    {
        return \StudipNews::haveRangePermission('view', $course->id, $observer->id);
    }

    public static function canIndexNewsOfUser(User $observer, User $observedUser)
    {
        return \StudipNews::haveRangePermission('view', $observedUser->id, $observer->id);
    }

    public static function canShowNewsRange(User $observer, \StudipNews $news, $rangeId)
    {
        return $news->havePermission('view', $rangeId, $observer->id);
    }

    public static function canEditNewsRange(User $observer, \StudipNews $news, $rangeId)
    {
        return $news->havePermission('edit', $rangeId, $observer->id);
    }

    public static function canDeleteNews(User $observer, \StudipNews $news)
    {
        return $news->havePermission('delete', '', $observer->id);
    }

    public static function canDeleteComment(User $observer, \StudipComment $comment)
    {
        //TODO: Are admins allowed to delete comments? If yes: are there different kind of admingroups?
        return $observer->id === $comment->user_id || $GLOBALS['perm']->have_perm('root', $observer->id);
    }

    public static function canCreateComment(User $observedUser, \StudipNews $news)
    {
        return
            $news->havePermission('view', '', $observedUser->id)
            && ($news->allow_comments);
    }
}
