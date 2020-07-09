<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */


namespace Studip\Activity;

class ActivityObserver
{
    /**
     * Register for Notifications the providers shall respond to
     *
     */
    public static function initialize()
    {
        \NotificationCenter::addObserver('Studip\Activity\MessageProvider', 'postActivity','MessageDidSend');

        // Notifications for ParticipantsProvider
        \NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','UserDidEnterCourse');
        \NotificationCenter::addObserver('\Studip\Activity\ParticipantsProvider', 'postActivity','UserDidLeaveCourse');

        //Notifications for DocumentsProvider
        \NotificationCenter::addObserver('\Studip\Activity\DocumentsProvider', 'postActivity','FileRefDidCreate');
        \NotificationCenter::addObserver('\Studip\Activity\DocumentsProvider', 'postActivity','FileRefDidUpdate');
        \NotificationCenter::addObserver('\Studip\Activity\DocumentsProvider', 'postActivity','FileRefDidDelete');

        //Notifications for NewsProvider
        \NotificationCenter::addObserver('\Studip\Activity\NewsProvider', 'postActivity','StudipNewsDidCreate');

        //Notifications for WikiProvider
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidCreate');
        \NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidDelete');
        //this is rather pointless and annoying
        //\NotificationCenter::addObserver('\Studip\Activity\WikiProvider', 'postActivity','WikiPageDidUpdate');

        //Notifications for ScheduleProvider (Course)
        \NotificationCenter::addObserver('\Studip\Activity\ScheduleProvider', 'postActivity','CourseDidChangeSchedule');
    }
}
