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
        $navigation = new Navigation(_('Ablaufplan'), URLHelper::getURL("seminar_main.php", ['auswahl' => $course_id, 'redirect_to' => "dispatch.php/course/dates"]));
        $navigation->setImage(Icon::create('schedule', 'inactive'));
        return $navigation;
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
