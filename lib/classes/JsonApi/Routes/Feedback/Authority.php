<?php

namespace JsonApi\Routes\Feedback;

use User;

class Authority
{
    public static function canShowFeedbackElement(User $user, \FeedbackElements $resource)
    {
        return \Feedback::hasRangeAccess($resource->range_id, $resource->range_type, $user->id);
    }

    public static function canIndexFeedbackEntries(User $user, \FeedbackElements $resource)
    {
        return self::canShowFeedbackElement($user, $resource);
    }

    public static function canSeeResultsOfFeedbackElement(User $user, \FeedbackElements $resource)
    {
        return self::canIndexFeedbackEntries($user, $resource) &&
            ($resource['results_visible'] || \Feedback::hasAdminPerm($resource['course_id'], $user->id));
    }

    public static function canIndexFeedbackElementsOfCourse(User $user, \Course $course)
    {
        return \Feedback::hasRangeAccess($course->id, \Course::class, $user->id);
    }

    public static function canShowFeedbackEntry(User $user, \FeedbackEntries $resource)
    {
        $feedbackElement = $resource->feedback;

        return self::canShowFeedbackElement($user, $feedbackElement);
    }
}
