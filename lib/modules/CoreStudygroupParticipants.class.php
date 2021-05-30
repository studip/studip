<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreStudygroupParticipants extends CorePlugin implements StudipModule
{
    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $navigation = new Navigation(_('Teilnehmende'), "dispatch.php/course/studygroup/members/{$course_id}");
        $navigation->setImage(Icon::create('persons', Icon::ROLE_INACTIVE));
        if ($last_visit && CourseMember::countBySQL("seminar_id = :course_id AND mkdate >= :last_visit", ['last_visit' => $last_visit, 'course_id' => $course_id]) > 0) {
            $navigation->setImage(Icon::create('persons+new', Icon::ROLE_ATTENTION));
        }
        return $navigation;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        $navigation = new Navigation(_('Teilnehmende'), "dispatch.php/course/studygroup/members/".$course_id);
        $navigation->setImage(Icon::create('persons', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('persons', Icon::ROLE_INFO));
        return ['members' => $navigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'category' => _('Lehr- und Lernorganisation'),
            'icon' => Icon::create('persons', Icon::ROLE_INFO),
            'displayname' => _('Teilnehmende'),
        ];
    }

    public function getInfoTemplate($course_id)
    {
        // TODO: Implement getInfoTemplate() method.
        return null;
    }

    public function isActivatableForContext(Range $context)
    {
        return false;
    }
}
