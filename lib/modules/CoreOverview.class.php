<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreOverview implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        return null;
    }

    function getTabNavigation($course_id) {
        $object_type = get_object_type($course_id, ['sem', 'inst']);
        if ($object_type === 'sem') {
            $course = Course::find($course_id);
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$course->status]['class']] ?: SemClass::getDefaultSemClass();
        } else {
            $institute = Institute::find($course_id);
            $sem_class = SemClass::getDefaultInstituteClass($institute->type);
        }

        $navigation = new Navigation(_('Übersicht'));
        $navigation->setImage(Icon::create('seminar', 'info_alt'));
        $navigation->setActiveImage(Icon::create('seminar', 'info'));
        if ($object_type !== 'sem') {
            $navigation->addSubNavigation('info', new Navigation(_('Kurzinfo'), 'dispatch.php/institute/overview'));
            $navigation->addSubNavigation('courses', new Navigation(_('Veranstaltungen'), 'show_bereich.php?level=s&id='.$course_id));
            $navigation->addSubNavigation('schedule', new Navigation(_('Veranstaltungs-Stundenplan'), 'dispatch.php/calendar/instschedule?cid='.$course_id));

            if ($GLOBALS['perm']->have_studip_perm('admin', $course_id)) {
                $navigation->addSubNavigation('admin', new Navigation(_('Administration der Einrichtung'), 'dispatch.php/institute/basicdata/index?new_inst=TRUE'));
            }
        } else {
            $navigation->addSubNavigation('info', new Navigation(_('Kurzinfo'), 'dispatch.php/course/overview'));
            if (!$sem_class['studygroup_mode']) {
                $navigation->addSubNavigation('details', new Navigation(_('Details'), 'dispatch.php/course/details/'));
            }
        }
        return ['main' => $navigation];
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata() {
         return [];
     }
}
