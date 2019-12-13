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
            $sql = "SELECT a.object_id, COUNT(module_id) as count,
                COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.module_type != 'crs'), module_id, NULL)) AS neue,
                MAX(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.module_type != 'crs'), chdate, 0)) AS last_modified
                FROM
                object_contentmodules a
                LEFT JOIN object_user_visits b ON (b.object_id = a.object_id AND b.user_id = :user_id AND b.type ='elearning_interface')
                WHERE a.object_id = :course_id  AND a.module_type != 'crs'
                GROUP BY a.object_id";

            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $course_id);
            $statement->bindValue(':threshold', $last_visit);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                $nav = new Navigation('elearning', "dispatch.php/course/elearning/show");
                if ((int) $result['neue']) {
                    $nav->setImage(
                        Icon::create(
                            'learnmodule+new',
                            'attention',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%1$d Lernmodul, %2$d neues',
                                        '%1$d Lernmodule, %2$d neue',
                                        $result['count']
                                    ),
                                    $result['count'],
                                    $result['neue']
                                )
                            ]
                        )
                    );
                } elseif ((int) $result['count']) {
                    $nav->setImage(
                        Icon::create(
                            'learnmodule',
                            'inactive',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%d Lernmodul',
                                        '%d Lernmodule',
                                        $result['count']
                                    ),
                                    $result['count']
                                )
                            ]
                        )
                    );
                }
                return $nav;
            }
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
