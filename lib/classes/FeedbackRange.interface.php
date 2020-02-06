<?php

/**
 * Interface FeedbackRange
 *
 * The FeedbackRange defines methods for range objects
 * that can reveive feedback.
 *
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 */

interface FeedbackRange
{
    /**
     * Returns a human-friendly representation of the FeedbackRange object instance's name.
     *
     * @return string A human-friendly name for the FeedbackRange object instance.
     */
    public function getRangeName();

    /**
     * Returns the icon object that shall be used with the FeedbackRange object instance.
     *
     * @param string $role role of icon
     * @return Icon icon for the FeedbackRange object instance.
     */
    public function getRangeIcon($role);

    /**
     * Returns the URL of FeedbackRange view, where the object instance is visible
     * together with the related feedback element(s).
     * @return string Path that is usable with the url_for and link_for methods.
     */
    public function getRangeUrl();

    /**
     * Returns the course id of FeedbackRange object instance
     * @return string course_id
     */
    public function getRangeCourseId();

    /**
     * Returns the accessebility of FeedbackRange object instance for current active user
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     * @return bool range object accessebility
     */
    public function isRangeAccessible(string $user_id = null): bool;
}
