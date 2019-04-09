<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreWiki implements StudipModule {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        if (get_config('WIKI_ENABLE')) {
            $navigation = new Navigation(_('Wiki'), "seminar_main.php?auswahl=".$course_id."&redirect_to=wiki.php");
            $navigation->setImage(Icon::create('wiki', 'inactive'));
            return $navigation;
        } else {
            return null;
        }
    }

    function getTabNavigation($course_id) {
        if (get_config('WIKI_ENABLE')) {
            $navigation = new Navigation(_('Wiki'));
            $navigation->setImage(Icon::create('wiki', 'info_alt'));
            $navigation->setActiveImage(Icon::create('wiki', 'info'));

            $navigation->addSubNavigation('show', new Navigation(_('Wiki-Startseite'), 'wiki.php?view=show'));
            $navigation->addSubNavigation('listnew', new Navigation(_('Neue Seiten'), 'wiki.php?view=listnew'));
            $navigation->addSubNavigation('listall', new Navigation(_('Alle Seiten'), 'wiki.php?view=listall'));
            return ['wiki' => $navigation];
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
            'summary' => _('Gemeinsames asynchrones Erstellen und Bearbeiten von Texten'),
            'description' => _('Im Wiki-Web oder kurz "Wiki" können '.
                'verschiedene Autor/-innen gemeinsam Texte, Konzepte und andere '.
                'schriftliche Arbeiten erstellen und gestalten, dies '.
                'allerdings nicht gleichzeitig. Texte können individuell '.
                'bearbeitet und die Änderungen gespeichert werden. Das '.
                'Besondere im Wiki ist, dass Studierende und Lehrende '.
                'annähernd die gleichen Rechte (schreiben, lesen, ändern, '.
                'löschen) haben, was sich nicht einschränken lässt. Das '.
                'System erstellt eine Versionshistorie, mit der Änderungen '.
                'nachvollziehbar werden. Einzelne Versionen können zudem '.
                'auch gelöscht werden (nur Lehrende). Ein Export als '.
                'pdf-Datei ist integriert.'),

            'displayname' => _('Wiki-Web'),
            'keywords' => _('Individuelle Bearbeitung von Texten;
                            Versionshistorie;
                            Druckansicht und PDF-Export;
                            Löschfunktion für die aktuellste Seiten-Version;
                            Keine gleichzeitige Bearbeitung desselben Textes möglich, nur nacheinander'),
            'descriptionshort' => _('Gemeinsames asynchrones Erstellen und Bearbeiten von Texten'),
            'descriptionlong' => _('Im Wiki-Web oder kurz "Wiki" können verschiedene Autor/-innen gemeinsam Texte, '.
                                    'Konzepte und andere schriftliche Arbeiten erstellen und gestalten. Dies '.
                                    'allerdings nicht gleichzeitig. Texte können individuell bearbeitet und '.
                                    'gespeichert werden. Das Besondere im Wiki ist, dass Studierende und Lehrende '.
                                    'annähernd die gleichen Rechte (schreiben, lesen, ändern, löschen) haben, was '.
                                    'gegenseitiges Vertrauen voraussetzt. Das System erstellt eine Versionshistorie, '.
                                    'mit der Änderungen nachvollziehbar werden. Einzelne Versionen können zudem auch '.
                                    'gelöscht werden (nur Lehrende). Eine Druckansicht und eine Exportmöglichkeit als '.
                                    'PDF-Datei ist integriert.'),
            'category' => _('Kommunikation und Zusammenarbeit'),
            'icon' => Icon::create('wiki', 'info'),
            'screenshots' => [
                'path' => 'plus/screenshots/Wiki-Web',
                'pictures' => [
                    0 => [ 'source' => 'Gemeinsam_erstellte_Texte.jpg', 'title' => 'Gemeinsam erstellte Texte']
                ]
            ]
        ];
    }
}
