<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreStudygroupAdmin implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        $navigation = new Navigation(_('Verwaltung'), 'dispatch.php/course/studygroup/edit/'.$course_id);
        $navigation->setImage(Icon::create('admin', 'inactive', ["title" => _('Verwaltung')]));
        return $navigation;
    }

    function getTabNavigation($course_id) {

        if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            $navigation = new Navigation(_('Verwaltung'));
            $navigation->setImage(Icon::create('admin', 'info_alt'));
            $navigation->setActiveImage(Icon::create('admin', 'info'));

            $navigation->addSubNavigation('main', new Navigation(_('Verwaltung'), 'dispatch.php/course/studygroup/edit/'.$course_id));
            $navigation->addSubNavigation('avatar', new Navigation(_('Infobild'), 'dispatch.php/course/avatar/update/'.$course_id));

            if (!$GLOBALS['perm']->have_perm('admin')) {
                if (get_config('VOTE_ENABLE')) {
                    $item = new Navigation(_('Fragebögen'), 'dispatch.php/questionnaire/courseoverview');
                    $item->setDescription(_('Erstellen und bearbeiten von Fragebögen.'));
                    $navigation->addSubNavigation('questionnaires', $item);

                    $item = new Navigation(_('Evaluationen'), 'admin_evaluation.php?view=eval_sem');
                    $item->setDescription(_('Richten Sie fragebogenbasierte Umfragen und Lehrevaluationen ein.'));
                    $navigation->addSubNavigation('evaluation', $item);
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
