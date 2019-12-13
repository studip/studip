<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreLiterature implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        if (get_config('LITERATURE_ENABLE')) {
            $sql = "SELECT a.range_id, COUNT(list_id) as count,
                COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.user_id !=:user_id), list_id, NULL)) AS neue,
                MAX(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.user_id != :user_id), chdate, 0)) AS last_modified
                FROM
                lit_list a
                LEFT JOIN object_user_visits b ON (b.object_id = a.range_id AND b.user_id = :user_id AND b.type ='literature')
                WHERE a.range_id = :course_id  AND a. visibility = 1
                GROUP BY a.range_id";
            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $course_id);
            $statement->bindValue(':threshold', $last_visit);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                $nav = new Navigation('literature', "dispatch.php/course/literature");
                if ((int) $result['neue']) {
                    $nav->setImage(
                        Icon::create(
                            'literature+new',
                            'attention',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%1$d Literaturliste, %2$d neue',
                                        '%1$d Literaturlisten, %2$d neue',
                                        $result['count']
                                    ),
                                    $result['count'],
                                    $result['neue']
                                )
                            ]
                        )
                    );
                    $nav->setBadgeNumber($result['neue']);
                } elseif ((int)$result['count']) {
                    $nav->setImage(
                        Icon::create(
                            'literature',
                            'inactive',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%d Literaturliste',
                                        '%d Literaturlisten',
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
        if (get_config('LITERATURE_ENABLE')) {
            $object_type = get_object_type($course_id);
            $navigation = new Navigation(_('Literatur'));
            $navigation->setImage(Icon::create('literature', 'info_alt'));
            $navigation->setActiveImage(Icon::create('literature', 'info'));

            $navigation->addSubNavigation('view', new Navigation(_('Literatur'), "dispatch.php/course/literature?view=literatur_".$object_type));
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                $navigation->addSubNavigation('edit', new Navigation(_('Literatur bearbeiten'), 'dispatch.php/literature/edit_list?view=literatur_'.$object_type.'&new_'.$object_type.'=TRUE&_range_id='. $course_id));
                $navigation->addSubNavigation('search', new Navigation(_('Literatur suchen'), 'dispatch.php/literature/search?return_range=' . $course_id));
            }
            
            return ['literature' => $navigation];
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
            'summary' => _('Erstellung von Literaturlisten unter Verwendung von Katalogen'),
            'description' => _('Lehrende haben die Möglichkeit, '.
                'veranstaltungsspezifische Literaturlisten entweder zu '.
                'erstellen oder bestehende Listen aus anderen '.
                'Literaturverwaltungsprogrammen (z. B. Citavi und Endnote) '.
                'hochzuladen. Diese Listen können in Lehrveranstaltungen '.
                'kopiert und sichtbar geschaltet werden. Je nach Anbindung '.
                'kann im tatsächlichen Buchbestand der Hochschule '.
                'recherchiert werden.'),
            'displayname' => _('Literatur'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('Individuell zusammengestellte Literaturlisten;
                            Anbindung an Literaturverwaltungsprogramme (z. B. OPAC);
                            Einfache Suche nach Literatur'),
            'descriptionshort' => _('Erstellung von Literaturlisten unter Verwendung von Katalogen'),
            'descriptionlong' => _('Lehrende haben die Möglichkeit, veranstaltungsspezifische Literaturlisten '.
                                    'entweder zu erstellen oder bestehende Listen aus anderen Literaturverwaltungsprogrammen '.
                                    '(z. B. Citavi und Endnote) hochzuladen. Diese Listen können in Lehrveranstaltungen '.
                                    'kopiert und sichtbar geschaltet werden. Je nach Anbindung kann im tatsächlichen '.
                                    'Buchbestand der Hochschule recherchiert werden.'),
            'icon' => Icon::create('literature', 'info'),
            'screenshots' => [
                'path' => 'plus/screenshots/Literatur',
                'pictures' => [
                    0 => ['source' => 'Literatur_suchen.jpg', 'title' => _('Literatur suchen')],
                    1 => ['source' => 'Literatur_in_Literaturliste_einfuegen.jpg', 'title' => _('Literatur in Literaturliste einfügen')],
                    2 => [ 'source' => 'Literaturliste_in_der_Veranstaltung_anzeigen.jpg', 'title' => _('Literaturliste in der Veranstaltung anzeigen')]
                ]
            ]                        
        ];
     }
}
