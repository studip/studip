<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* extern_config.inc.php
* 
* extern modules configuration file
* 
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       extern_config
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_config.inc.php
// extern modules configuration file
// Copyright (C) 2003 Peter Thienel <thienel@data-quest.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

global
    $EXTERN_MODULE_TYPES,
    $EXTERN_MAX_CONFIGURATIONS,
    $EXTERN_ERROR_MESSAGE,
    $EXTERN_CONFIG_STORAGE_CONTAINER,
    $EXTERN_ENABLE_ERROR_LOGGING,
    $EXTERN_LOG_FILE;


$EXTERN_MODULE_TYPES[0] = array("module" => "Global", "name" => _("globale Konfiguration"), "level" => 1,
                                                    "description" => _("Das Modul &quot;globale Konfiguration&quot; enthält Einstellungen, die für alle Module gelten, solange sie nicht in den jeweiligen Modulen überschrieben werden."), 'order' => 1, 'view' => array('inst','fak','studip'));

$EXTERN_MODULE_TYPES[1] = array("module" => "Persons", "name" => _("Mitarbeiter"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Mitarbeiter&quot; gibt ein Mitarbeiterverzeichnis einer Einrichtung aus."), 'order' => 20, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[2] = array("module" => "Persondetails", "name" => _("Mitarbeiterdetails"), "level" => 2,
                                                    "description" => _("Das Modul &quot;Mitarbeiterdetails&quot; gibt die Daten eines Mitarbeiters einer Einrichtung aus."), 'order' => 30, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[3] = array("module" => "Lectures", "name" => _("Veranstaltungen"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung aus."), 'order' => 40, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[4] = array("module" => "Lecturedetails", "name" => _("Veranstaltungsdetails"), "level" => 2,
                                                    "description" => _("Das Modul &quot;Veranstaltungsdetails&quot; gibt alle allgemeinen Daten einer Veranstaltung aus."), 'order' => 50, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[5] = array("module" => "News", "name" => _("News"), "level" => 1,
                                                    "description" => _("Das Modul &quot;News&quot; gibt alle News einer Einrichtung aus."), 'order' => 60, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[6] = array("module" => "Download", "name" => _("Download"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Download&quot; stellt alle Dateien aus dem Dateibereich einer Einrichtung zum Download zur Verfügung."), 'order' => 70, 'view' => array('inst','fak'));
/*
$EXTERN_MODULE_TYPES[8] = array("module" => "Semlecturetree", "name" => _("Bereichsbaum Veranstaltungen"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung aus."));

$EXTERN_MODULE_TYPES[9] = array("module" => "Rangelecturetree", "name" => _("Einrichtungsbaum Veranstaltungen"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung aus."));
*/
$EXTERN_MODULE_TYPES[7] = array("module" => "Newsticker", "name" => _("Newsticker"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Newsticker&quot; gibt alle News einer Einrichtung in einem Ticker aus."), 'order' => 75, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[8] = array("module" => "Lecturestable", "name" => _("Veranstaltungen (Tabelle)"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung als Tabelle aus."), 'order' => 45, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[9] = array("module" => "TemplatePersons", "name" => _("Mitarbeiter (templatebasiert)"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Mitarbeiter&quot; gibt ein Mitarbeiterverzeichnis einer Einrichtung aus."), 'order' => 22, 'view' => array('inst','fak', 'studip'));
                                                    
$EXTERN_MODULE_TYPES[10] = array("module" => "TemplateDownload", "name" => _("Download (templatebasiert)"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Download&quot; stellt alle Dateien aus dem Dateibereich einer Einrichtung zum Download zur Verfügung."), 'order' => 72, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[11] = array("module" => "TemplateNews", "name" => _("News (templatebasiert)"), "level" => 1,
                                                    "description" => _("Das Modul &quot;News&quot; gibt alle News einer Einrichtung aus."), 'order' => 62, 'view' => array('inst','fak'));

$EXTERN_MODULE_TYPES[12] = array("module" => "TemplateLectures", "name" => _("Veranstaltungen (templatebasiert)"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Veranstaltungen&quot; gibt alle Veranstaltungen einer Einrichtung aus."), 'order' => 42, 'view' => array('inst','fak'));
                                                    
$EXTERN_MODULE_TYPES[13] = array("module" => "TemplateLecturedetails", "name" => _("Veranstaltungsdetails (templatebasiert)"), "level" => 2,
                                                    "description" => _("Das Modul &quot;Veranstaltungsdetails&quot; gibt alle allgemeinen Daten einer Veranstaltung aus."), 'order' => 52, 'view' => array('inst','fak','studip'));
                                                    
$EXTERN_MODULE_TYPES[14] = array("module" => "TemplatePersondetails", "name" => _("Mitarbeiterdetails (templatebasiert)"), "level" => 2,
                                                    "description" => _("Das Modul &quot;Mitarbeiterdetails&quot; gibt die Daten eines Mitarbeiters einer Einrichtung aus."), 'order' => 32, 'view' => array('inst','fak','studip'));

$EXTERN_MODULE_TYPES[15] = array("module" => "TemplateSemBrowse", "name" => _("Veranstaltungsbrowser (templatebasiert)"), "level" => 1,
                                                    "description" => _("Das Modul &quot;Veranstaltungsbrowser&quot; ermöglicht das Suchen nach Veranstaltungen im Einrichtungs- und Vorlesungsverzeichnis."), 'order' => 47, 'view' => array('studip'));
                                                    
$EXTERN_MODULE_TYPES[16] = array('module' => 'TemplatePersBrowse', 'name' => _("Personenbrowser (templatebasiert)"), 'level' => 1, 'description' => _("Das Modul &quot;Personenbrowser&quot; ermöglicht die Anzeige eines systemweiten Personalverzeichnisses."), 'order' => 55, 'view' => array('studip'));

// Allowed number of configurations
$EXTERN_MAX_CONFIGURATIONS = 32;

// print this message instead of data if an error occurs
$EXTERN_ERROR_MESSAGE = "<b>Ein Fehler ist aufgetreten. Die Daten können nicht angezeigt werden. Bitte wenden Sie sich an den Webmaster.</b>";

// change this to match your class name, if you have extended the class ExternConfig to store configurations in a different manner
$EXTERN_CONFIG_STORAGE_CONTAINER = 'DB';

// Not yet implemented!
$EXTERN_ENABLE_ERROR_LOGGING = FALSE;

// Not yet implemented!
$EXTERN_LOG_FILE = "";

// don't edit below this line
//==============================================================================

// path generation for SRI-interface (external pages)
if (preg_match('#^(http://|https://)?(.+?)(/)?$#', $GLOBALS['EXTERN_SERVER_NAME'], $matches)) {
    if ($matches[1]) {
        $GLOBALS['EXTERN_SERVER_NAME']  = $matches[1];
    } else {
        $GLOBALS['EXTERN_SERVER_NAME']  = 'http://';
    }
    $GLOBALS['EXTERN_SERVER_NAME']  .= $matches[2] . '/';
} else {
    $GLOBALS['EXTERN_SERVER_NAME'] = $GLOBALS['ABSOLUTE_URI_STUDIP'];
} 

?>
