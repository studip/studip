<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreAdmin implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        $navigation = new Navigation(_('Verwaltung'), 'dispatch.php/course/management');
        $navigation->setImage(Icon::create('admin', 'inactive', ["title" => _('Verwaltung')]));
        return $navigation;
    }

    function getTabNavigation($course_id) {

        $sem_create_perm = in_array(get_config('SEM_CREATE_PERM'), array('root','admin','dozent')) ? get_config('SEM_CREATE_PERM') : 'dozent';

        if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            $navigation = new Navigation(_('Verwaltung'));
            $navigation->setImage(Icon::create('admin', 'info_alt'));
            $navigation->setActiveImage(Icon::create('admin', 'info'));

            $main = new Navigation(_('Verwaltung'), 'dispatch.php/course/management');
            $navigation->addSubNavigation('main', $main);

            if (!Context::isInstitute()) {
                $item = new Navigation(_('Grunddaten'), 'dispatch.php/course/basicdata/view/' . $course_id);
                $item->setImage(Icon::create('edit', 'clickable'));
                $item->setDescription(_('Bearbeiten der Grundeinstellungen dieser Veranstaltung.'));
                $navigation->addSubNavigation('details', $item);

                $item = new Navigation(_('Infobild'), 'dispatch.php/avatar/update/course/' . $course_id);
                $item->setImage(Icon::create('file-pic', 'clickable'));
                $item->setDescription(_('Infobild dieser Veranstaltung bearbeiten oder löschen.'));
                $navigation->addSubNavigation('avatar', $item);

                $item = new Navigation(_('Studienbereiche'), 'dispatch.php/course/study_areas/show/' . $course_id);
                $item->setImage(Icon::create('module', 'clickable'));
                $item->setDescription(_('Zuordnung dieser Veranstaltung zu Studienbereichen für die Darstellung im Verzeichnis aller Veranstaltungen.'));
                $navigation->addSubNavigation('study_areas', $item);

                $current_course = Course::find($course_id);
                if ($current_course && $current_course->getSemClass()->offsetGet('module')) {
                    $item = new Navigation(_('LV-Gruppen'), 'dispatch.php/course/lvgselector/index/' . $course_id, array('list' => 'TRUE'));
                    $item->setImage(Icon::create('learnmodule', 'clickable'));
                    $item->setDescription(_('Zuordnung der Veranstaltung zu Lehrveranstaltungsgruppen um die Einordnung innerhalb des Modulverzeichnisses festzulegen.'));
                    $navigation->addSubNavigation('lvgruppen', $item);
                }

                $item = new Navigation(_('Zeiten/Räume'), 'dispatch.php/course/timesrooms');
                $item->setImage(Icon::create('date', 'clickable'));
                $item->setDescription(_('Regelmäßige Veranstaltungszeiten, Einzeltermine und Ortsangaben ändern.'));
                $navigation->addSubNavigation('dates', $item);

                if (get_config('RESOURCES_ENABLE') && get_config('RESOURCES_ALLOW_ROOM_REQUESTS')) {
                    $item = new Navigation(_('Raumanfragen'), 'dispatch.php/course/room_requests/index/' . $course_id);
                    $item->setImage(Icon::create('resources', 'clickable'));
                    $item->setDescription(_('Raumanfragen zu Veranstaltungszeiten verwalten.'));
                    $navigation->addSubNavigation('room_requests', $item);
                }

                $item = new Navigation(_('Zugangsberechtigungen'), 'dispatch.php/course/admission');
                $item->setImage(Icon::create('lock-locked', 'clickable'));
                $item->setDescription(_('Zugangsbeschränkungen, Anmeldeverfahren oder einen Passwortschutz für diese Veranstaltung einrichten.'));
                $navigation->addSubNavigation('admission', $item);

                $item = new AutoNavigation(_('Zusatzangaben'), 'dispatch.php/admin/additional');
                $item->setImage(Icon::create('add', 'clickable'));
                $item->setDescription(_('Vorlagen zur Erhebung weiterer Angaben von Teilnehmenden auswählen.'));
                $navigation->addSubNavigation('additional_data', $item);

                if ($GLOBALS['perm']->have_perm($sem_create_perm)) {
                    if (!LockRules::check($course_id, 'seminar_copy')) {
                        $item = new Navigation(_('Veranstaltung kopieren'), 'dispatch.php/course/wizard/copy/'.$course_id);
                        $item->setImage(Icon::create('seminar+add', 'clickable'));
                        $main->addSubNavigation('copy', $item);
                    }

                    if (get_config('ALLOW_DOZENT_DELETE') || $GLOBALS['perm']->have_perm('admin')) {
                        $item = new Navigation(_('Veranstaltung löschen'), 'dispatch.php/course/archive/confirm');
                        $item->setImage(Icon::create('seminar+remove', 'clickable'));
                        $main->addSubNavigation('archive', $item);
                    }

                    if ((get_config('ALLOW_DOZENT_VISIBILITY') || $GLOBALS['perm']->have_perm('admin')) && !LockRules::Check($course_id, 'seminar_visibility')) {
                        $is_visible = Course::findCurrent()->visible;
                        $item = new Navigation(_('Sichtbarkeit ändern') . ' (' .  ($is_visible ? _('sichtbar') : _('unsichtbar')) . ')', 'dispatch.php/course/management/change_visibility');
                        $item->setImage(Icon::create('visibility-' . ($is_visible ? 'visible' : 'invisible'), 'clickable'));
                        $main->addSubNavigation('visibility', $item);
                    }
                    if ($GLOBALS['perm']->have_perm('admin')) {
                        $is_locked = Course::findCurrent()->lock_rule;
                        $item = new Navigation(_('Sperrebene ändern') . ' (' .  ($is_locked ? _('gesperrt') : _('nicht gesperrt')) . ')', 'dispatch.php/course/management/lock');
                        $item->setImage(Icon::create('lock-' . ($is_locked  ? 'locked' : 'unlocked'), 'clickable'), ['data-dialog'=> 'size=auto']);
                        $main->addSubNavigation('lock', $item);
                    }

                }

                // show entry for simulated participant view
                if (in_array($GLOBALS['perm']->get_studip_perm($course_id), words('tutor dozent'))) {
                    $item = new Navigation('Studierendenansicht simulieren', 'dispatch.php/course/change_view/set_changed_view');
                    $item->setDescription(_('Hier können Sie sich die Veranstaltung aus der Sicht von Studierenden sehen.'));
                    $item->setImage(Icon::create('visibility-invisible', 'clickable'));
                    $main->addSubNavigation('change_view', $item);
                }
            }  // endif modules only seminars

            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                if (get_config('VOTE_ENABLE')) {
                    $item = new Navigation(_('Fragebögen'), 'dispatch.php/questionnaire/courseoverview');
                    $item->setImage(Icon::create('vote', 'clickable'));
                    $item->setDescription(_('Erstellen und bearbeiten von Fragebögen.'));
                    $navigation->addSubNavigation('questionnaires', $item);

                    $item = new Navigation(_('Evaluationen'), 'admin_evaluation.php?view=eval_sem');
                    $item->setImage(Icon::create('evaluation', 'clickable'));
                    $item->setDescription(_('Richten Sie fragebogenbasierte Umfragen und Lehrevaluationen ein.'));
                    $navigation->addSubNavigation('evaluation', $item);
                }
            }

            /*
             * Is the current SemClass available for grouping other courses?
             * -> show child management
             */
            $course = Course::find($course_id);
            if ($course) {
                $c = $course->getSemClass();
                if ($c->isGroup()) {

                    $item = new Navigation(_('Unterveranstaltungen'), 'dispatch.php/course/grouping/children');
                    $item->setImage(Icon::create('group', 'info_alt'));
                    $item->setActiveImage(Icon::create('group', 'info'));
                    $item->setDescription(_('Ordnen Sie dieser Veranstaltung eine oder mehrere Unterveranstaltungen zu.'));
                    $navigation->addSubNavigation('children', $item);

                /*
                 * Check if any SemClasses with grouping functionality exist at all
                 * -> show parent assignment.
                 */
                } else if (count(SemClass::getGroupClasses()) > 0) {

                    $item = new Navigation(_('Zuordnung zu Hauptveranstaltung'), 'dispatch.php/course/grouping/parent');
                    $item->setImage(Icon::create('group', 'info_alt'));
                    $item->setActiveImage(Icon::create('group', 'info'));
                    $item->setDescription(_('Ordnen Sie diese Veranstaltung einer bestehenden ' .
                        'Hauptveranstaltung zu oder lösen Sie eine bestehende Zuordnung.'));
                    $navigation->addSubNavigation('parent', $item);

                }
            }

            return array('admin' => $navigation);
        } else {
            return array();
        }
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata()
    {
        return array();
    }
}
