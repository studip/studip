<?php
/*
 *  Copyright (c) 2012-2019  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * Class Blubber - the Blubber-plugin
 * This is only used to manage blubber within a course.
 */
class Blubber extends StudIPPlugin implements StandardPlugin
{

    /**
     * Returns a navigation for the tab displayed in the course.
     * @param string $course_id of the course
     * @return \Navigation
     */
    public function getTabNavigation($course_id) {
        $tab = new Navigation(_("Blubber"), PluginEngine::getLink($this, [], "messenger/course"));
        $tab->setImage(Icon::create('blubber', 'info_alt'));
        return ['blubber' => $tab];
    }

    /**
     * Returns a navigation-object with the grey/red icon for displaying in the
     * my_courses.php page.
     * @param string  $course_id
     * @param int $last_visit
     * @param string|null  $user_id
     * @return \Navigation
     */
    public function getIconNavigation($course_id, $last_visit, $user_id = null) {
        $icon = new Navigation(
            _("Blubber"),
            "plugins.php/blubber/messenger/course"
        );
        $icon->setImage(Icon::create("blubber", "inactive"));
        $comments = BlubberComment::findBySQL("INNER JOIN blubber_threads USING (thread_id) WHERE blubber_threads.context_type = 'course' AND blubber_threads.context_id = :course_id AND blubber_comments.mkdate >= :last_visit AND blubber_comments.user_id != :me AND blubber_threads.visible_in_stream = '1'", [
            'course_id' => $course_id,
            'last_visit' => $last_visit,
            'me' => $GLOBALS['user']->id
        ]);
        foreach ($comments as $comment) {
            if ($comment->thread->isVisibleInStream() AND $comment->thread->isReadable()) {
                $icon->setImage(Icon::create("blubber", "new"));
                $icon->setTitle(_("Es gibt neue Blubber"));
                $icon->setBadgeNumber(count($comments));
                break;
            }
        }
        $threads = BlubberThread::findBySQL("context_type = 'course' AND context_id = :course_id AND mkdate >= :last_visit AND user_id != :me AND visible_in_stream = '1'", [
            'course_id' => $course_id,
            'last_visit' => $last_visit,
            'me' => $GLOBALS['user']->id
        ]);
        foreach ($threads as $thread) {
            if ($thread->isVisibleInStream() AND $thread->isReadable()) {
                $icon->setImage(Icon::create("blubber", "attention"));
                $icon->setTitle(_("Es gibt neue Blubber"));
                break;
            }
        }
        return $icon;
    }

    /**
     * Returns no template, because this plugin doesn't want to insert an
     * info-template in the course-overview.
     * @param string $course_id
     * @return null
     */
    public function getInfoTemplate($course_id)  {
        return null;
    }
}
