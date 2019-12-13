<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreSchedule implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        $count = 0;
        $neue  = 0;
        // check for extern dates
        $sql       = "SELECT  COUNT(term.termin_id) as count,
                  MAX(IF ((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id != :user_id), term.chdate, 0)) AS last_modified,
                  COUNT(IF((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id !=:user_id), term.termin_id, NULL)) AS neue
                FROM
                  ex_termine term
                LEFT JOIN
                  object_user_visits ouv ON(ouv . object_id = term . range_id AND ouv . user_id = :user_id AND ouv . type = 'schedule')
                WHERE term . range_id = :course_id
                GROUP BY term.range_id";
        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', $last_visit);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            $count = $result['count'];
            $neue  = $result['neue'];
        }


        // check for normal dates
        $sql = "SELECT  COUNT(term.termin_id) as count,
                  COUNT(term.termin_id) as count, COUNT(IF((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id !=:user_id), term.termin_id, NULL)) AS neue,
                  MAX(IF ((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id != :user_id), term . chdate, 0)) AS last_modified
                FROM
                  termine term
                LEFT JOIN
                  object_user_visits ouv ON(ouv . object_id = term . range_id AND ouv . user_id = :user_id AND ouv . type = 'schedule')
                WHERE term . range_id = :course_id
                GROUP BY term.range_id";

        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':threshold', $last_visit);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            $count += $result['count'];
            $neue += $result['neue'];
        }

        if ($neue || (int)$count > 0) {
            $nav = new Navigation('schedule', "dispatch.php/course/dates");
            if ($neue) {
                $nav->setImage(
                    Icon::create(
                        'schedule+new',
                        'attention',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%1$d Termin, %2$d neuer',
                                    '%1$d Termine, %2$d neue',
                                    $count
                                ),
                                $count,
                                $neue
                            )
                        ]
                    )
                );
                $nav->setBadgeNumber($neue);
            } elseif ($count) {
                $nav->setImage(
                    Icon::create(
                        'schedule',
                        'inactive',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%d Termin',
                                    '%d Termine',
                                    $count
                                ),
                                $count
                            )
                        ]
                    )
                );
            }
            return $nav;
        }
    }
    
    function getTabNavigation($course_id) {
        // cmd und open_close_id mit durchziehen, damit geöffnete Termine geöffnet bleiben
        $req = Request::getInstance();
        $openItem = '';
        if (isset($req['cmd']) && isset($req['open_close_id'])) {
            $openItem = '&cmd='.$req['cmd'].'&open_close_id='.$req['open_close_id'];
        }
        
        $navigation = new Navigation(_('Ablaufplan'));
        $navigation->setImage(Icon::create('schedule', 'info_alt'));
        $navigation->setActiveImage(Icon::create('schedule', 'info'));

        $navigation->addSubNavigation('dates', new Navigation(_('Termine'), "dispatch.php/course/dates"));
        $navigation->addSubNavigation('topics', new Navigation(_('Themen'), "dispatch.php/course/topics"));

        return ['schedule' => $navigation];
    }

    /** 
     * @see StudipModule::getMetadata()
     */ 
    function getMetadata()
    {
        return [
            'summary' => _('Anzeige aller Termine der Veranstaltung'),
            'description' => _('Der Ablaufplan listet alle Präsenz-, '.
                'E-Learning-, Klausur-, Exkursions- und sonstige '.
                'Veranstaltungstermine auf. Zur besseren Orientierung und zur '.
                'inhaltlichen Einstimmung der Studierenden können Lehrende den '.
                'Terminen Themen hinzufügen, die z. B. eine Kurzbeschreibung '.
                'der Inhalte darstellen.'),
            'displayname' => _('Ablaufplan'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('Inhaltliche und räumliche Orientierung für Studierende;
                            Beschreibung der Inhalte einzelner Termine;
                            Raumangabe;
                            Themenzuordnung zu Terminen;
                            Terminzuordnung zu Themen'),
            'descriptionshort' => _('Anzeige aller Termine der Veranstaltung, ggf. mit Themenansicht'),
            'descriptionlong' => _('Der Ablaufplan listet alle Präsenz-, E-Learning-, Klausur-, Exkursions- ' .
                                    'und sonstige Veranstaltungstermine auf. Zur besseren Orientierung und zur ' .
                                    'inhaltlichen Einstimmung der Studierenden können Lehrende den Terminen ' .
                                    'Themen hinzufügen, die z. B. eine Kurzbeschreibung der Inhalte darstellen.'),          
            'icon' => Icon::create('schedule', 'info'),
            'screenshots' => [
                'path' => 'plus/screenshots/Ablaufplan',
                'pictures' => [
                    0 => ['source' => 'Termine_mit_Themen.jpg', 'title' => _('Termine mit Themen')],
                    1 => [ 'source' => 'Thema_bearbeiten_und_einem_Termin_zuordnen.jpg', 'title' => _('Thema bearbeiten und einem Termin zuordnen')]
                ]
            ]
        ];
    }
}
