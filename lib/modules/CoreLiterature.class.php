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
            $navigation = new Navigation(_('Teilnehmende'), "seminar_main.php?auswahl=".$course_id."&redirect_to=dispatch.php/course/members/index");
            $navigation->setImage(Icon::create('persons', 'inactive'));
            return $navigation;
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
