<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreScm implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        if (get_config('SCM_ENABLE')) {
            $sql = "SELECT tab_name,  ouv.object_id,
                  COUNT(IF(content !='',1,0)) as count,
                  COUNT(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND scm.user_id !=:user_id), IF(content !='',1,0), NULL)) AS neue,
                  MAX(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND scm.user_id !=:user_id), chdate, 0)) AS last_modified
                FROM
                  scm
                LEFT JOIN
                  object_user_visits ouv ON(ouv.object_id = scm.range_id AND ouv.user_id = :user_id and ouv . type = 'scm')
                WHERE
                  scm.range_id = :course_id
                GROUP BY
                  scm.range_id";

            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $course_id);
            $statement->bindValue(':threshold', $last_visit);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);


            if (!empty($result)) {
                $nav = new Navigation('scm', 'dispatch.php/course/scm');

                if ($result['count']) {
                    if ($result['neue']) {
                        $image = Icon::create('infopage+new', 'attention');
                        $nav->setBadgeNumber($result['neue']);
                        if ($result['count'] == 1) {
                            $title = $result['tab_name'] . _(' (geändert)');
                        } else {
                            $title = sprintf(
                                _('%1$d Einträge insgesamt, %2$d neue'),
                                $result['count'],
                                $result['neue']
                            );
                        }
                    } else {
                        $image = Icon::create('infopage', 'inactive');
                        if ($result['count'] == 1) {
                            $title = $result['tab_name'];
                        } else {
                            $title = sprintf(
                                ngettext(
                                    '%d Eintrag',
                                    '%d Einträge',
                                    $result['count']
                                ),
                                $result['count']
                            );
                        }
                    }
                    $nav->setImage($image, ['title' => $title]);
                }
                return $nav;
            }
        } else {
            return null;
        }
    }
    
    function getTabNavigation($course_id) {
        if (get_config('SCM_ENABLE')) {
            $temp = StudipScmEntry::findByRange_id($course_id, 'ORDER BY position ASC');
            $scms = SimpleORMapCollection::createFromArray($temp);

            $link = 'dispatch.php/course/scm';

            $navigation = new Navigation($scms->first()->tab_name ?: _('Informationen'), $link);
            $navigation->setImage(Icon::create('infopage', 'info_alt'));
            $navigation->setActiveImage(Icon::create('infopage', 'info'));

            foreach ($scms as $scm) {
                $scm_link = $link .'/'. $scm->id;
                $nav = new Navigation($scm['tab_name'], $scm_link);
                $navigation->addSubNavigation($scm->id, $nav);
            }

            return ['scm' => $navigation];
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
            'summary' => _('Die Lehrenden bestimmen, wie Titel und Inhalt dieser Seite aussehen.'),
            'description' => _('Die Freie Informationsseite ist eine Seite, '.
                'die sich die Lehrenden nach ihren speziellen Anforderungen '.
                'einrichten können. So kann z.B. der Titel im Kartenreiter '.
                'selbst definiert werden. Ferner können beliebig viele '.
                'Einträge im Untermenü vorgenommen werden. Für jeden Eintrag '.
                'öffnet sich eine Seite mit einem Text-Editor, in den '.
                'beliebiger Text eingegeben und formatiert werden kann. Oft '.
                'wird die Seite für die Angabe von Literatur genutzt als '.
                'Alternative zum Plugin Literatur. Sie kann aber auch für '.
                'andere beliebige Zusatzinformationen (Links, Protokolle '.
                'etc.) verwendet werden.'),
            'displayname' => _('Informationen'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('Raum für eigene Informationen;
                            Name des Reiters frei definierbar;
                            Beliebig erweiterbar durch zusätzliche "neue Einträge"'),
            'descriptionshort' => _('Freie Gestaltung von Reiternamen und Inhalten durch Lehrende.'),
            'descriptionlong' => _('Diese Seite kann von Lehrenden nach ihren speziellen Anforderungen eingerichtet werden. '.
                                    'So ist z.B. der Titel im Reiter frei definierbar. Ferner können beliebig viele neue '.
                                    'Eintragsseiten eingefügt werden. Für jeden Eintrag öffnet sich eine Seite mit einem '.
                                    'Text-Editor, in den beliebiger Text eingefüft, eingegeben und formatiert werden kann. '.
                                    'Oft wird die Seite für die Angabe von Literatur genutzt als Alternative zur Funktion '.
                                    'Literatur. Sie kann aber auch für andere beliebige Zusatzinformationen (Links, Protokolle '.
                                    'etc.) verwendet werden.'),
            'icon' => Icon::create('infopage', 'info'),
            'screenshots' => [
                'path' => 'plus/screenshots/Freie_Informationsseite',
                'pictures' => [
                    0 => ['source' => 'Zwei_Eintraege_mit_Inhalten_zur_Verfuegung_stellen.jpg', 'title' => _('Zwei Einträge mit Inhalten zur Verfügung stellen')],
                    1 => [ 'source' => 'Neue_Informationsseite_anlegen.jpg', 'title' => _('Neue Informationsseite anlegen')]
                ]
            ]       
        ];
    }
}
