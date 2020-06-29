<?php
/**
* config.inc.php
*
* Configuration file for studip. In this file you can change the options of many
* Stud.IP Settings.
* Please note: To setup the system, you have to set the basic settings in the
* config_local.inc.php in the same directory first.
*
* @access       public
* @package      studip_core
* @modulegroup      library
* @module       config.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>,
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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
  $CALENDAR_MAX_EVENTS,
  $FLASHPLAYER_DEFAULT_CONFIG_MIN,
  $FLASHPLAYER_DEFAULT_CONFIG_MAX,
  $INST_ADMIN_DATAFIELDS_VIEW,
  $INST_MODULES,
  $INST_STATUS_GROUPS,
  $INST_TYPE,
  $LIT_LIST_FORMAT_TEMPLATE,
  $NAME_FORMAT_DESC,
  $PERS_TERMIN_KAT,
  $SCM_PRESET,
  $SEM_STATUS_GROUPS,
  $SMILE_SHORT,
  $SYMBOL_SHORT,
  $TERMIN_TYP,
  $TIME_PRESETS,
  $TITLE_FRONT_TEMPLATE,
  $TITLE_REAR_TEMPLATE,
  $UNI_CONTACT,
  $UNI_INFO,
  $UNI_LOGIN_ADD,
  $UNI_LOGOUT_ADD,
  $UNI_URL,
  $UPLOAD_TYPES,
  $SEM_TREE_TYPES,
  $NOT_HIDEABLE_FIELDS,
  $TEILNEHMER_IMPORT_DATAFIELDS,
  $DEFAULT_TITLE_FOR_STATUS;

/*basic settings for Stud.IP
----------------------------------------------------------------
you find here the indivdual settings for your installation.
 ! for the basic system settings, please edit the file config_local.inc.php in the same folder !*/


//Some more basic data
//Note: The the clean-name of your institution ($UNI_NAME_CLEAN) is stored in the config_local.inc.php
$UNI_URL = "http://www.studip.de";
$UNI_LOGIN_ADD='';
$UNI_LOGOUT_ADD=sprintf(_("Und hier geht's zur %sStud.IP Portalseite%s&nbsp;"), "<a href=\"http://www.studip.de\"><b>", "</b></a>");
$UNI_CONTACT = "<please insert your general contact mail-adress here>";
$UNI_INFO = "Stud.IP 4.0 - Studienbegleitender Internetsupport von Präsenzlehre";


/* $SEM_CLASS and $SEM_TYPE configuration moved to database
=> Admin/Global settings/Course categories
----------------------------------------------------------------*/

// define default names for status groups
$DEFAULT_TITLE_FOR_STATUS = [
    'dozent'   => [_('Lehrende'), _('Lehrende')],
    'deputy'   => [_('Vertretung'), _('Vertretungen')],
    'tutor'    => [_('Tutor/-in'), _('Tutor/-innen')],
    'autor'    => [_('Studierende'), _('Studierende')],
    'user'     => [_('Leser/-in'), _('Leser/-innen')],
    'accepted' => [_('Vorläufig akzeptierte Person'),
                        _('Vorläufig akzeptierte Personen')]];


/*
possible types of sem_tree ("Veranstaltungshierarchie") types
the "editable" flag could be used to prevent modifications, e.g. imported data
*/
$SEM_TREE_TYPES[0] = ["name" => "", "editable" => true]; //default type, must be present
//$SEM_TREE_TYPES[1] = array("name" => _("Studienmodul") , "editable" => true, "is_module" => true);


/* Set the allowed and prohibited file types for the types given above.
*  If nothing is configured for a specific type, the values of the "default" setting are taken.
*
*  "type"=>"deny" means: only the listed "file_types" are allowed
*  "type"=>"allow" means: all, but the listed "file_types" are allowed
*
*  "file_sizes" determines how much each user class can upload per file (multiple of 1 MB = 1048576 Bytes)
*/

$UPLOAD_TYPES=[    "default" =>
                [  "type"=>"allow",
                        "file_types" =>  ["exe"],
                        "file_sizes" =>  [ "root" => 240 * 1048576,
                                    "admin" => 240 * 1048576,
                                    "dozent" => 240 * 1048576,
                                    "tutor" => 240 * 1048576,
                                    "autor" => 240 * 1048576,
                                    "nobody" => 16 * 1048576
                                    ]
                ],
// rules for futher course-types can be added below (please adhere exactly to the structure given above)
        ];

/* Set the allowed and prohibited file types for mail attachments (if activated by ENABLE_MAIL_ATTACHMENTS).
*
*  "type"=>"deny" means: only the listed "file_types" are allowed
*  "type"=>"allow" means: all, but the listed "file_types" are allowed
*
*  "file_sizes" determines how much each user class can upload per file (multiple of 1 MB = 1048576 Bytes)
*/

$UPLOAD_TYPES["attachments"] =
                [  "type" => "allow",
                        "file_types" =>  ["exe"],
                        "file_sizes" =>  [ "root" => 7 * 1048576,
                                    "admin" => 7 * 1048576,
                                    "dozent" => 7 * 1048576,
                                    "tutor" => 7 * 1048576,
                                    "autor" => 7 * 1048576,
                                    "nobody" => 1.38 * 1048576
                                    ]
                ];

/* Set the allowed and prohibited file types for personal files (like in blubber-upload).
*
*  "type"=>"deny" means: only the listed "file_types" are allowed
*  "type"=>"allow" means: all, but the listed "file_types" are allowed
*
*  "file_sizes" determines how much each user class can upload per file (multiple of 1 MB = 1048576 Bytes)
*/

$UPLOAD_TYPES["personalfiles"] =
                [  "type" => "allow",
                        "file_types" =>  ["exe"],
                        "file_sizes" =>  [ "root" => 240* 1048576,
                                    "admin" => 240 * 1048576,
                                    "tutor" => 240 * 1048576,
                                    "dozent" =>240 * 1048576,
                                    "autor" => 240 * 1048576,
                                    "nobody" => 0
                                    ]
                ];


/*
* define presets for the status-groups in courses (refers to the key of the $SEM_CLASS array above)
* if none is given, the designations of $SEM_STATUS_GROUPS["default"] are used
*/
$SEM_STATUS_GROUPS["default"] =  ["Lehrende", "Tutor/-innen", "Studierende", "Leser/-innen", "sonstige"];    //the default. Don't delete this entry!
$SEM_STATUS_GROUPS["2"] =  ["Organisatoren", "Mitglieder", "Ausschußmitglieder", "sonstige"];
$SEM_STATUS_GROUPS["3"] =  ["Moderatoren des Forums","Mitglieder", "sonstige"];
// ...can be continued accordingly

/*
 * define additional fields that can be shown in participant list view.
 */

$TEILNEHMER_VIEW[0] = ["field" => "user_picture",
  "name" => _("Nutzerbilder"), "table" => "special", "export" => 0, "display"=> 1];
$TEILNEHMER_VIEW[1] = ["field" => "geschlecht",
  "name" => _("Geschlecht"), "table" => "datafields", "export" => 1, "display"=> 1];
$TEILNEHMER_VIEW[2] = ["field" => "preferred_language",
  "name" => _("Sprache"), "table" => "user_info", "export" => 1, "display"=> 1];


/*
* set allowed designations of institutes / divisions / administrative units
*/
$INST_TYPE[1]=["name"=>_("Einrichtung")];
$INST_TYPE[2]=["name"=>_("Zentrum")];
$INST_TYPE[3]=["name"=>_("Lehrstuhl")];
$INST_TYPE[4]=["name"=>_("Abteilung")];
$INST_TYPE[5]=["name"=>_("Fachbereich")];
$INST_TYPE[6]=["name"=>_("Seminar")];
$INST_TYPE[7]=["name"=>_("Fakultät")];
$INST_TYPE[8]=["name"=>_("Arbeitsgruppe")];
// ...can be continued accordingly


/*
* define presets for the status-groups in institutes / divisions / administrative units (refers to the key of the $INST_TYPE array above)
* if none is given, the designations of $INST_STATUS_GROUPS["default"] are used
*/

$INST_STATUS_GROUPS["default"] =  ["Direktor/-in", "Lehrende", "Lehrbeauftragte", "Zweitmitglied", "wiss. Hilfskraft","wiss. Mitarbeiter/-in",
                                    "stud. Hilfskraft", "Frauenbeauftragte", "Internetbeauftragte(r)", "Studierende", "techn. Mitarbeiter/-in", "Sekretariat / Verwaltung",
                                    "stud. Vertreter/-in"];
// ...can be continued accordingly


//define the used modules for instiutes
$INST_MODULES["default"] = [
            "forum"=>TRUE,              //forum, this module is stud_ip core; always available
            "documents"=>TRUE,          //documents, this module is stud_ip core; always available
            "personal"=>TRUE,           //personal, this module is stud_ip core; always available
            "literature"=>FALSE,         //literature, this module is stud_ip core; always available
            "scm"=>FALSE,               //simple content module, this modul is stud_ip core; always available
            "wiki"=>FALSE,              //wikiwiki-web, this module is stud_ip core; always available
            ];
//you can add more specific presets for the different types


// Set presets for course appointment types
/*
* The first array element is used for the designation of regular meetings and will be labeled
* accordingly in the schedule.
* The second array element is always used to denominate preliminary discussions.
* Both names can be chosen freely.
* The appointment types marked as "sitzung"=>1 are shown in the course creation assistent and
* while editing dates as templates for the description of regular turnus meetings.
*/

$TERMIN_TYP[1]=["name"=>_("Sitzung"), "sitzung"=>1, "color"=>"#682c8b"];
$TERMIN_TYP[2]=["name"=>_("Vorbesprechung"), "sitzung"=>0, "color"=>"#b02e7c"];
$TERMIN_TYP[3]=["name"=>_("Klausur"), "sitzung"=>0, "color"=>"#129c94"];
$TERMIN_TYP[4]=["name"=>_("Exkursion"), "sitzung"=>0, "color"=>"#f26e00"];
$TERMIN_TYP[5]=["name"=>_("anderer Termin"), "sitzung"=>0, "color"=>"#008512"];
$TERMIN_TYP[6]=["name"=>_("Sondersitzung"), "sitzung"=>0, "color"=>"#a85d45"];
$TERMIN_TYP[7]=["name"=>_("Vorlesung"), "sitzung"=>1, "color"=>"#ca9eaf"];
// more types can be added here


// Configure the categories for the personal calendar
$PERS_TERMIN_KAT[1]=["name"=>_("Sonstiges"), "color"=>"#008512"];
$PERS_TERMIN_KAT[2]=["name"=>_("Sitzung"), "color"=>"#682c8b"];
$PERS_TERMIN_KAT[3]=["name"=>_("Vorbesprechung"), "color"=>"#b02e7c"];
$PERS_TERMIN_KAT[4]=["name"=>_("Klausur"), "color"=>"#129c94"];
$PERS_TERMIN_KAT[5]=["name"=>_("Exkursion"), "color"=>"#f26e00"];
$PERS_TERMIN_KAT[6]=["name"=>_("Sondersitzung"), "color"=>"#a85d45"];
$PERS_TERMIN_KAT[7]=["name"=>_("Prüfung"), "color"=>"#6ead10"];
$PERS_TERMIN_KAT[8]=["name"=>_("Telefonat"), "color"=>"#d60000"];
$PERS_TERMIN_KAT[9]=["name"=>_("Besprechung"), "color"=>"#ffbd33"];
$PERS_TERMIN_KAT[10]=["name"=>_("Verabredung"), "color"=>"#66b570"];
$PERS_TERMIN_KAT[11]=["name"=>_("Geburtstag"), "color"=>"#a480b9"];
$PERS_TERMIN_KAT[12]=["name"=>_("Familie"), "color"=>"#d082b0"];
$PERS_TERMIN_KAT[13]=["name"=>_("Urlaub"), "color"=>"#70c3bf"];
$PERS_TERMIN_KAT[14]=["name"=>_("Reise"), "color"=>"#f7a866"];
$PERS_TERMIN_KAT[15]=["name"=>_("Vorlesung"), "color"=>"#ca9eaf"];
// more categories can be added here

//standard times for date-begin and date-end
$TIME_PRESETS =  [ //starthour, startminute, endhour, endminute
         ['07','45','09','15'], // 07:45 - 09:15
         ['09','30','11','00'], // 09:30 - 11:00
         ['11','15','12','45'], // 11:15 - 12:45
         ['13','30','15','00'], // 13:30 - 15:00
         ['15','15','16','45'], // 15:15 - 16:45
         ['17','00','18','30'], // 17:00 - 18:30
         ['18','45','20','15']  // 18:45 - 20:15
        ];
//$TIME_PRESETS = false;


//preset for academic titles -  add further titles to the array, if necessary
$TITLE_FRONT_TEMPLATE = ["","Prof.","Prof. Dr.","Dr.","PD Dr.","Dr. des.","Dr. med.","Dr. rer. nat.","Dr. forest.",
                            "Dr. sc. agr.","Dipl.-Biol.","Dipl.-Chem.","Dipl.-Ing.","Dipl.-Sozw.","Dipl.-Geogr.",
                            "Dipl.-Geol.","Dipl.-Geophys.","Dipl.-Ing. agr.","Dipl.-Kfm.","Dipl.-Math.","Dipl.-Phys.",
                            "Dipl.-Psych.","M. Sc","B. Sc"];
$TITLE_REAR_TEMPLATE = ["","M.A.","B.A.","M.S.","MBA","Ph.D.","Dipl.-Biol.","Dipl.-Chem.","Dipl.-Ing.","Dipl.-Sozw.","Dipl.-Geogr.",
                            "Dipl.-Geol.","Dipl.-Geophys.","Dipl.-Ing. agr.","Dipl.-Kfm.","Dipl.-Math.","Dipl.-Phys.",
                            "Dipl.-Psych.","M. Sc","B. Sc"];

// name templates for the list of currently active users ("who is online")

$NAME_FORMAT_DESC['full'] = _("Titel1 Vorname Nachname Titel2");
$NAME_FORMAT_DESC['full_rev'] = _("Nachname, Vorname, Titel1, Titel2");
$NAME_FORMAT_DESC['no_title'] = _("Vorname Nachname");
$NAME_FORMAT_DESC['no_title_rev'] = _("Nachname, Vorname");
$NAME_FORMAT_DESC['no_title_short'] = _("Nachname, V.");
$NAME_FORMAT_DESC['no_title_motto'] = _("Vorname Nachname, Motto");


//preset names for scm (simple content module)
$SCM_PRESET[1] = ["name"=>_("Informationen")];     //the first entry is the default label for scms, it'll be used if the user give no information for another label
$SCM_PRESET[2] = ["name"=>_("Literatur")];
$SCM_PRESET[3] = ["name"=>_("Links")];
$SCM_PRESET[4] = ["name"=>_("Verschiedenes")];
//you can add more presets here

//preset template for formatting of literature list entries
$LIT_LIST_FORMAT_TEMPLATE = "**{dc_creator}** |({dc_contributor})||\n"
                        . "{dc_title}||\n"
                        . "{dc_identifier}||\n"
                        . "%%{published}%%||\n"
                        . "{note}||\n"
                        . "[{lit_plugin_display_name}]{external_link}|\n";

//Shortcuts for smileys
$SMILE_SHORT = [ //diese Kuerzel fuegen das angegebene Smiley ein (Dateiname + ".gif")
    ":)"=>"smile" ,
    ":-)"=>"asmile" ,
    ":#:"=>"zwinker" ,
    ":("=>"frown" ,
    ":o"=>"redface" ,
    ":D"=>"biggrin",
    ";-)"=>"wink"];

//Shortcuts for symbols
$SYMBOL_SHORT = [
    "=)"    => "&rArr;" ,
    "(="    => "&lArr;" ,
    "(c)"   => "&copy;" ,
    "(r)"   => "&reg;" ,
    " tm "  => "&trade;"
];


/*configuration for additional modules
----------------------------------------------------------------
this options are only needed, if you are using the addional Stud.IP modules (please see in config_local.inc.php
which modules are activated). It's a good idea to leave this settings untouched...*/

// Literature-Import Plugins
$LIT_IMPORT_PLUGINS[1] = ['name' => 'EndNote', 'visual_name' => 'EndNote ab Version 7 / Reference Manager 11', 'description' => _("Exportieren Sie Ihre Literaturliste aus EndNote / Reference Manager als XML-Datei.")];
$LIT_IMPORT_PLUGINS[2] = ['name' => 'GenericXML', 'visual_name' => _("Einfaches XML nach fester Vorgabe"),
        'description' => _("Die XML-Datei muss folgende Struktur besitzen:").'[code]
        <?xml version="1.0" encoding="UTF-8"?>
        <xml>
        <eintrag>
            <titel></titel>
            <autor></autor>
            <beschreibung></beschreibung>
            <herausgeber></herausgeber>
            <ort></ort>
            <isbn></isbn>
            <jahr></jahr>
        </eintrag>
        </xml>[/code]'.
        _("Jeder Abschnitt darf mehrfach vorkommen oder kann weggelassen werden, mindestens ein Titel pro Eintrag muss vorhanden sein.")];
$LIT_IMPORT_PLUGINS[3] = ['name' => 'CSV', 'visual_name' => _("CSV mit Semikolon als Trennzeichen"), 'description' => _("Exportieren Sie Ihre Literaturliste in eine mit Trennzeichen getrennte Datei (CSV). Wichtig hierbei ist die Verwendung des Semikolons als Trennzeichen. Folgende Formatierung wird dabei in jeder Zeile erwartet:").'[pre]'._("Titel;Verfasser oder Urheber;Verleger;Herausgeber;Thema und Stichworte;ISBN").'[/pre]'];
$LIT_IMPORT_PLUGINS[4] = ['name' => 'StudipLitList', 'visual_name' => _("Literaturliste im Stud.IP Format"), 'description' => _("Benutzen Sie die Export-Funktion innerhalb von Stud.IP, um eine Literaturliste im Stud.IP Format zu exportieren.")];
$LIT_IMPORT_PLUGINS[5] = ['name' => 'Citavi', 'visual_name' => _("Literaturliste im Stud.IP (Citavi) Format"), 'description' => _("Benutzen Sie die Export-Funktion von Citavi, um eine Literaturliste im Stud.IP Format zu exportieren.")];

// cofiguration for flash player
$FLASHPLAYER_DEFAULT_CONFIG_MIN = "&amp;showstop=1&amp;showvolume=1&amp;bgcolor=A6B6C6&amp;bgcolor1=A6B6C6&amp;bgcolor2=7387AC&amp;playercolor=7387AC&amp;buttoncolor=254580&amp;buttonovercolor=E9EFFD&amp;slidercolor1=CAD7E1&amp;slidercolor2=A6B6C6&amp;sliderovercolor=E9EFFD&amp;loadingcolor=E9B21A&amp;buffer=5&amp;buffercolor=white&amp;buffershowbg=0&amp;playeralpha=90&amp;playertimeout=500&amp;shortcut=1&amp;phpstream=0&amp;onclick=playpause&amp;showloading=always";
$FLASHPLAYER_DEFAULT_CONFIG_MAX = "&amp;showstop=1&amp;showvolume=1&amp;bgcolor=A6B6C6&amp;bgcolor1=A6B6C6&amp;bgcolor2=7387AC&amp;playercolor=7387AC&amp;buttoncolor=254580&amp;buttonovercolor=E9EFFD&amp;slidercolor1=CAD7E1&amp;slidercolor2=A6B6C6&amp;sliderovercolor=E9EFFD&amp;loadingcolor=E9B21A&amp;buffer=5&amp;buffercolor=white&amp;buffershowbg=0&amp;playeralpha=90&amp;playertimeout=500&amp;shortcut=1&amp;showtime=1&amp;showfullscreen=1&amp;showplayer=always&amp;phpstream=0&amp;onclick=playpause&amp;showloading=always";

//Here you have to add the datafield_ids as elements. They will be shown in the standard / extended view on inst_admin.php
$INST_ADMIN_DATAFIELDS_VIEW = [
    'default' => [],
    'extended' => []
];
/*
 * Fields that may not be hidden by users in their privacy settings.
 * Can be configured per permission level.
 * @see lib/models/User.class.php in function getHomepageElements for
 * available fields.
 * Entries look like "'field_name' => true".
 */
$NOT_HIDEABLE_FIELDS = [
    'user' => [],
    'autor' => [],
    'tutor' => [],
    'dozent' => [],
    'admin' => [],
    'root' => []
];
//Add ids of datafields to use for import on teilnehmer.php
$TEILNEHMER_IMPORT_DATAFIELDS = [];
