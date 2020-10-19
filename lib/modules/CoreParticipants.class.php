<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreParticipants implements StudipModule
{
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $navigation = new Navigation(_('Teilnehmende'), 'dispatch.php/course/members');
        $navigation->setImage(Icon::create('persons', Icon::ROLE_INACTIVE));
        if ($last_visit && CourseMember::countBySQL("seminar_id = :course_id AND mkdate >= :last_visit", ['last_visit' => $last_visit, 'course_id' => $course_id]) > 0) {
            $navigation->setImage(Icon::create('persons+new', Icon::ROLE_ATTENTION));
        }
        return $navigation;
    }

    public function getTabNavigation($course_id)
    {
        #$navigation = new AutoNavigation(_('Teilnehmende'));
        $navigation = new Navigation(_('Teilnehmende'));
        $navigation->setImage(Icon::create('persons', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('persons', Icon::ROLE_INFO));

        $course = Course::find($course_id);

        // Only courses without children have a regular member list and statusgroups.
        if (!$course->getSemClass()->isGroup()) {
            $navigation->addSubNavigation('view', new Navigation(_('Teilnehmende'), 'dispatch.php/course/members'));
            $navigation->addSubNavigation('statusgroups', new Navigation(_('Gruppen'), 'dispatch.php/course/statusgroups'));
        } else {
            if (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                return null;
            }
            $navigation->addSubNavigation('children', new Navigation(_('Teilnehmende in Unterveranstaltungen'), 'dispatch.php/course/grouping/members'));
        }

        if ($course->aux_lock_rule) {
            $navigation->addSubNavigation('additional', new Navigation(_('Zusatzangaben'), 'dispatch.php/course/members/additional'));
        }

        return ['members' => $navigation];
    }

    /**
     * @see StudipModule::getMetadata()
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'description' => _('Die Teilnehmenden werden gruppiert nach ihrer '.
                'jeweiligen Funktion in einer Tabelle gelistet. Für Lehrende '.
                'werden sowohl das Anmeldedatum als auch der Studiengang mit '.
                'Semesterangabe dargestellt. Die Liste kann in verschiedene '.
                'Formate exportiert werden. Außerdem gibt es die '.
                'Möglichkeiten, eine Rundmail an alle zu schreiben (nur '.
                'Lehrende) bzw. einzelne Teilnehmende separat anzuschreiben.'),
            'displayname' => _('Teilnehmende'),
            'keywords' => _('Rundmail an einzelne, mehrere oder alle Teilnehmenden;
                            Gruppierung nach Lehrenden, Tutor/-innen und Studierenden (Autor/-innen);
                            Aufnahme neuer Studierender (Autor/-innen) und Tutor/-innen;
                            Import einer Teilnehmendenliste;
                            Export der Teilnehmendenliste;
                            Einrichten von Gruppen;
                            Anzeige Studiengang und Fachsemester'),
            'descriptionshort' => _('Liste aller Teilnehmenden einschließlich Nachrichtenfunktionen'),
            'descriptionlong' => _('Die Teilnehmenden werden gruppiert nach ihrer jeweiligen Rolle in '.
                                   'einer Tabelle gelistet. Für Lehrende werden sowohl das Anmeldedatum '.
                                   'als auch der Studiengang mit Semesterangabe der Studierenden dargestellt. '.
                                   'Die Liste kann in verschiedene Formate exportiert werden. Außerdem gibt '.
                                   'es die Möglichkeiten für Lehrende, allen eine Rundmail zukommen zu lassen '.
                                   'bzw. einzelne Teilnehmende separat anzuschreiben.'),
            'category' => _('Lehr- und Lernorganisation'),
            'icon' => Icon::create('persons', Icon::ROLE_INFO),
            'screenshots' => [
                'path' => 'plus/screenshots/TeilnehmerInnen',
                'pictures' => [
                    ['source' => 'Liste_aller_Teilnehmenden_einer_Veranstaltung.jpg', 'title' => _('Liste aller Teilnehmenden einer Veranstaltung')],
                    ['source' => 'Rundmail_an_alle_TeilnehmerInnen_einer_Veranstaltung.jpg', 'title' => _('Rundmail an alle Teilnehmdenden einer Veranstaltung')],
                ]
            ],
        ];
    }
}
