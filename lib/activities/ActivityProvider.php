<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */


namespace Studip\Activity;

interface ActivityProvider
{
    /**
     * Fill in the url, route and any lengthy content for the passed activity
     *
     * @param Studip\Activity\Activity $activity
     */
    public function getActivityDetails($activity);

    /**
     * Human readable name for the current provider to be used in the activity-title
     */
    public static function getLexicalField();
}
