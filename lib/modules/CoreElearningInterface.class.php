<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreElearningInterface implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        if (get_config('ELEARNING_INTERFACE_ENABLE')) {
            $navigation = new Navigation(_('Lernmodule'), "seminar_main.php?auswahl=".$course_id."&redirect_to=dispatch.php/course/elearning/show");
            $navigation->setImage(Icon::create('learnmodule', 'inactive'));

            return $navigation;
        } else {
            return null;
        }
    }

    function getTabNavigation($course_id) {
        if (get_config('ELEARNING_INTERFACE_ENABLE')) {
            $navigation = new Navigation(_('Lernmodule'));
            $navigation->setImage(Icon::create('learnmodule', 'info_alt'));
            $navigation->setActiveImage(Icon::create('learnmodule', 'info'));

            if (ObjectConnections::isObjectConnected($course_id)) {
                $elearning_nav = new Navigation(_('Lernmodule dieser Veranstaltung'), 'dispatch.php/course/elearning/show?seminar_id=' . $course_id);

                if (get_object_type($course_id, ['inst'])) {
                    $elearning_nav->setTitle(_('Lernmodule dieser Einrichtung'));
                }

                $navigation->addSubNavigation('show', $elearning_nav);
            }

            if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId())) {
                $navigation->addSubNavigation('edit', new Navigation(_('Lernmodule hinzufügen / entfernen'), 'dispatch.php/course/elearning/edit?seminar_id=' . $course_id));
            }

            return ['elearning' => $navigation];
        } else {
            return null;
        }
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata()
    {
        return [
            'summary' => _('Zugang zu extern erstellten Lernmodulen'),
            'description' => _('Über diese Schnittstelle ist es möglich, '.
                'Selbstlerneinheiten, die in externen Programmen erstellt '.
                'werden, in Stud.IP zur Verfügung zu stellen. Ein häufig '.
                'angebundenes System ist ILIAS. Besteht eine Anbindung zu '.
                'einem ILIAS-System, haben Lehrende die Möglichkeit, in '.
                'ILIAS Selbstlerneinheiten zu erstellen und in Stud.IP '.
                'bereit zu stellen.'),
            'displayname' => _('Lernmodulschnittstelle'),
            'category' => _('Inhalte und Aufgabenstellungen'),
            'keywords' => _('Einbindung z. B. von ILIAS-Lerneinheiten;
                            Zugang zu externen Lernplattformen;
                            Aufgaben- und Test-Erstellung'),
            'icon' => Icon::create('learnmodule', 'info'),
            'descriptionshort' => _('Zugang zu extern erstellten Lernmodulen'),
            'descriptionlong' => _('Über diese Schnittstelle ist es möglich, Selbstlerneinheiten, '.
                                    'die in externen Programmen erstellt werden, in Stud.IP zur Verfügung '.
                                    'zu stellen. Ein häufig angebundenes System ist ILIAS. Besteht eine '.
                                    'Anbindung zu einem ILIAS-System, haben Lehrende die Möglichkeit, in '.
                                    'ILIAS Selbstlerneinheiten zu erstellen und in Stud.IP bereit zu stellen.')
        ];
    }
}
