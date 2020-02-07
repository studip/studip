<?php

namespace JsonApi\Routes\Feedback;

use User;

class Authority
{
    public static function canShowFeedbackElement(User $user, \FeedbackElement $resource)
    {
        return \Feedback::hasRangeAccess($resource->range_id, $resource->range_type, $user->id);
    }

    public static function canIndexFeedbackEntries(User $user, \FeedbackElement $resource)
    {
        return self::canShowFeedbackElement($user, $resource);
    }

    public static function canSeeResultsOfFeedbackElement(User $user, \FeedbackElement $resource)
    {
        return self::canIndexFeedbackEntries($user, $resource) &&
            ($resource['results_visible'] || \Feedback::hasAdminPerm($resource['course_id'], $user->id));
    }

    public static function canIndexFeedbackElementsOfCourse(User $user, \Course $course)
    {
        return \Feedback::hasRangeAccess($course->id, \Course::class, $user->id);
    }

    public static function canIndexFeedbackElementsOfFileRef(User $user, \FileRef $fileRef)
    {
        return \Feedback::hasRangeAccess($fileRef->id, \FileRef::class, $user->id);
    }

    public static function canIndexFeedbackElementsOfFolder(User $user, \Folder $folder)
    {
        return \Feedback::hasRangeAccess($folder->id, \Folder::class, $user->id);
    }

    public static function canShowFeedbackEntry(User $user, \FeedbackEntry $resource)
    {
        $feedbackElement = $resource->feedback;

        return self::canShowFeedbackElement($user, $feedbackElement);
    }
}
