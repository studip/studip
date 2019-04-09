<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreDocuments implements StudipModule
{

    function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $range_type = get_object_type($course_id, ['sem', 'inst']) == 'sem' ? 'course' : 'institute';
        $navigation = new Navigation(_('Dateibereich'), "seminar_main.php?auswahl=$course_id&redirect_to=dispatch.php/' . $range_type . '/files'");
        $navigation->setImage(Icon::create('files', 'inactive'));
        return $navigation;
    }

    function getTabNavigation($course_id)
    {
        $range_type = get_object_type($course_id, ['sem', 'inst']) == 'sem' ? 'course' : 'institute';
        $newFilesNavigation = new Navigation(_('Dateien'), 'dispatch.php/' . $range_type . '/files');
        $newFilesNavigation->setImage(Icon::create('files', 'info_alt'));
        $newFilesNavigation->setActiveImage(Icon::create('files', 'info'));
        return ['files' => $newFilesNavigation];
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata()
    {
        return [
            'summary'          => _('Austausch von Dateien'),
            'description'      => _('Im Dateibereich können Dateien sowohl von ' .
                'Lehrenden als auch von Studierenden hoch- bzw. ' .
                'heruntergeladen werden. Es können Ordner angelegt und ' .
                'individuell benannt werden (nur Lehrende). Die Dateien ' .
                'können somit strukturiert zur Verfügung gestellt werden. ' .
                'Multimediadateien wie Grafiken, Audio- und Videodateien ' .
                'können sofort angezeigt bzw. abgespielt werden. Über das ' .
                'PlugIn "Dateiordnerberechtigung" können Im Dateibereich ' .
                'bestimmte Rechte für Studierende, wie z.B. das Leserecht, ' .
                'festgelegt werden.'),
            'displayname'      => _('Dateien'),
            'category'         => _('Lehr- und Lernorganisation'),
            'keywords'         => _('Hoch- und Herunterladen von Dateien;
                            Anlegen von Ordnern und Unterordnern;
                            Verschieben einer Datei/eines Ordners per drag and drop innerhalb einer Veranstaltung;
                            Verschieben einer Datei/eines Ordners in eine andere Veranstaltung;
                            Kopieren einer Datei/eines Ordners in eine andere oder mehrere Veranstaltungen;
                            Verlinkung auf abgelegte Dateien möglich;
                            Erstellung Hausaufgabenordner durch Aktivierung der Funktion "Dateiordnerberechtigung"'),
            'descriptionshort' => _('Austausch von Dateien'),
            'descriptionlong'  => _('Dateien können sowohl von Lehrenden als auch von Studierenden hoch- bzw. ' .
                'heruntergeladen werden. Ordner können angelegt und individuell benannt werden ' .
                '(Standard: nur Lehrende), so dass Dateien strukuriert zur Verfügung gestellt ' .
                'werden können. Multimediadateien wie Grafiken, Audio- und Videodateien werden ' .
                'sofort angezeigt bzw. abspielbar dargestellt. Über das PlugIn "Dateiordnerberechtigungen" ' .
                'können Im Dateibereich bestimmte Rechte (r, w, x, f) für Studierende, wie z.B. das ' .
                'Leserecht (r), festgelegt werden.'),
            'icon'             => Icon::create('files', 'info'),
            'screenshots'      => [
                'path'     => 'plus/screenshots/Dateibereich_-_Dateiordnerberechtigung',
                'pictures' => [
                    0 => ['source' => 'Ordneransicht_mit_geoeffnetem_Ordner.jpg', 'title' => _('Ordneransicht mit geöffnetem Ordner')],
                    1 => ['source' => 'Ordneransicht_mit_Dateiinformationen.jpg', 'title' => _('Ordneransicht mit Dateiinformationen')],
                    2 => ['source' => 'Neuen_Ordner_erstellen.jpg', 'title' => _('Neuen Ordner erstellen')],
                    3 => ['source' => 'Ordner_zum_Hausaufgabenordner_umwandeln.jpg', 'title' => _('Ordner zum Hausaufgabenordner umwandeln')],
                    4 => ['source' => 'Ansicht_alle_Dateien.jpg', 'title' => _('Ansicht alle Dateien')]
                ]
            ]
        ];
    }
}
