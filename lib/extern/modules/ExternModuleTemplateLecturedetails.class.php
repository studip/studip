<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleTemplateLecturedetails.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplateLecturedetails
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplateLecturedetails.class.php
//
// Copyright (C) 2007 Peter Thienel <thienel@data-quest.de>,
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


require_once 'lib/extern/views/extern_html_templates.inc.php';
require_once 'lib/user_visible.inc.php';
require_once 'lib/dates.inc.php';

class ExternModuleTemplateLecturedetails extends ExternModule {

    var $markers = [];
    var $args = ['seminar_id'];

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        $this->data_fields = ['subtitle', 'lecturer', 'art', 'status', 'description',
            'location', 'semester', 'time', 'number', 'teilnehmer', 'requirements',
            'lernorga', 'leistung', 'range_path', 'misc', 'ects'];
        $this->registered_elements = [
                'ReplaceTextSemType',
                'LinkInternPersondetails' => 'LinkInternTemplate',
                'TemplateLectureData' => 'TemplateGeneric',
                'TemplateNews' => 'TemplateGeneric',
                'TemplateStudipData' => 'TemplateGeneric'
        ];
        $this->field_names = 
        [
                _("Untertitel"),
                _("Lehrende"),
                _("Veranstaltungsart"),
                _("Veranstaltungstyp"),
                _("Beschreibung"),
                _("Ort"),
                _("Semester"),
                _("Zeiten"),
                _("Veranstaltungsnummer"),
                _("Teilnehmende"),
                _("Voraussetzungen"),
                _("Lernorganisation"),
                _("Leistungsnachweis"),
                _("Bereichseinordnung"),
                _("Sonstiges"),
                _("ECTS-Punkte")
        ];

        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    function setup () {
        // extend $data_fields if generic datafields are set
    //  $config_datafields = $this->config->getValue("Main", "genericdatafields");
    //  $this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);

        // setup module properties
    //  $this->elements["LinkIntern"]->link_module_type = 2;
    //  $this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");

        $this->elements['LinkInternPersondetails']->real_name = _("Verlinkung zum Modul MitarbeiterInnendetails");
        $this->elements['LinkInternPersondetails']->link_module_type = [2, 14];
        $this->elements['TemplateLectureData']->real_name = _("Haupttemplate");
        $this->elements['TemplateNews']->real_name = _("Template für News");
        $this->elements['TemplateStudipData']->real_name = _("Template für statistische Daten aus Stud.IP");

    }

    function toStringEdit ($open_elements = '', $post_vars = '',
            $faulty_values = '', $anker = '') {

        $this->updateGenericDatafields('TemplateLectureData', 'sem');
        $this->elements['TemplateLectureData']->markers = $this->getMarkerDescription('TemplateLectureData');
        $this->elements['TemplateNews']->markers = $this->getMarkerDescription('TemplateNews');
        $this->elements['TemplateStudipData']->markers = $this->getMarkerDescription('TemplateStudipData');

        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }

    function getMarkerDescription ($element_name) {
        $markers['TemplateLectureData'][] = ['__GLOBAL__', _("Globale Variablen (gültig im gesamten Template).")];
        $markers['TemplateLectureData'][] = ['###STUDIP-EDIT-HREF###', ''];
        $markers['TemplateLectureData'][] = ['###STUDIP-REGISTER-HREF###', ''];

        $markers['TemplateLectureData'][] = ['<!-- BEGIN LECTUREDETAILS -->', ''];
        $markers['TemplateLectureData'][] = ['###TITLE###', ''];
        $markers['TemplateLectureData'][] = ['###SUBTITLE###', ''];
        $markers['TemplateLectureData'][] = ['###SEMESTER###', ''];
        $markers['TemplateLectureData'][] = ['###CYCLE###', ''];
        $markers['TemplateLectureData'][] = ['###ROOM###', ''];
        $markers['TemplateLectureData'][] = ['###NUMBER###', _("Die Veranstaltungsnummer")];

        $markers['TemplateLectureData'][] = ['<!-- BEGIN LECTURERS -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- BEGIN LECTURER -->', ''];
        $markers['TemplateLectureData'][] = ['###FULLNAME###', ''];
        $markers['TemplateLectureData'][] = ['###LASTNAME###', ''];
        $markers['TemplateLectureData'][] = ['###FIRSTNAME###', ''];
        $markers['TemplateLectureData'][] = ['###TITLEFRONT###', ''];
        $markers['TemplateLectureData'][] = ['###TITLEREAR###', ''];
        $markers['TemplateLectureData'][] = ['###PERSONDETAILS-HREF###', ''];
        $markers['TemplateLectureData'][] = ['###LECTURER-NO###', ''];
        $markers['TemplateLectureData'][] = ['###UNAME###', ''];
        $markers['TemplateLectureData'][] = ['<!-- END LECTURER -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- END LECTURERS -->', ''];

        $markers['TemplateLectureData'][] = ['<!-- BEGIN TUTORS -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- BEGIN TUTOR -->', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_FULLNAME###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_LASTNAME###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_FIRSTNAME###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_TITLEFRONT###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_TITLEREAR###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_PERSONDETAILS-HREF###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR-NO###', ''];
        $markers['TemplateLectureData'][] = ['###TUTOR_UNAME###', ''];
        $markers['TemplateLectureData'][] = ['<!-- END TUTOR -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- END TUTORS -->', ''];

        $markers['TemplateLectureData'][] = ['###PRELIM-DISCUSSION###', ''];
        $markers['TemplateLectureData'][] = ['###SEMTYPE-SUBSTITUTE###', ''];
        $markers['TemplateLectureData'][] = ['###SEMTYPE###', ''];
        $markers['TemplateLectureData'][] = ['###FORM###', _("Die Veranstaltungsart")];
        $markers['TemplateLectureData'][] = ['###PARTICIPANTS###', ''];
        $markers['TemplateLectureData'][] = ['###DESCRIPTION###', ''];
        $markers['TemplateLectureData'][] = ['###MISC###', _("Sonstiges")];
        $markers['TemplateLectureData'][] = ['###REQUIREMENTS###', ''];
        $markers['TemplateLectureData'][] = ['###ORGA###', _("Organisationsform")];
        $markers['TemplateLectureData'][] = ['###LEISTUNGSNACHWEIS###', _("Leistungsnachweis")];
        $markers['TemplateLectureData'][] = ['###FORM###', ''];
        $markers['TemplateLectureData'][] = ['###ECTS###', ''];
        $markers['TemplateLectureData'][] = ['###PRELIM-DISCUSSION###', ''];
        $markers['TemplateLectureData'][] = ['###FIRST-MEETING###', ''];

        $this->insertDatafieldMarkers('sem', $markers, 'TemplateLectureData');

        $markers['TemplateLectureData'][] = ['###NEWS###', _("Inhalt aus dem Template für News")];
        $markers['TemplateLectureData'][] = ['###STUDIP-DATA###', 'Inhalt aus dem Template für statistische Daten aus Stud.IP'];

        $markers['TemplateLectureData'][] = ['<!-- BEGIN RANGE-PATHES -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- BEGIN RANGE-PATH -->', ''];
        $markers['TemplateLectureData'][] = ['###PATH###', ''];
        $markers['TemplateLectureData'][] = ['<!-- END RANGE-PATH -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- END RANGE-PATHES -->', ''];

        $markers['TemplateLectureData'][] = ['<!-- BEGIN MODULES -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- BEGIN MODULE -->', ''];
        $markers['TemplateLectureData'][] = ['###PATH###', _('Modulzuordnungen der Veranstaltung')];
        $markers['TemplateLectureData'][] = ['<!-- END MODULE -->', ''];
        $markers['TemplateLectureData'][] = ['<!-- END MODULES -->', ''];

        $markers['TemplateLectureData'][] = ['<!-- END LECTUREDETAILS -->'];

        $markers['TemplateNews'][] = ['<!-- BEGIN NEWS -->', ''];
        $markers['TemplateNews'][] = ['<!-- BEGIN NO-NEWS -->', ''];
        $markers['TemplateNews'][] = ['###NEWS_NO-NEWS-TEXT###', ''];
        $markers['TemplateNews'][] = ['<!-- END NO-NEWS -->', ''];
        $markers['TemplateNews'][] = ['<!-- BEGIN ALL-NEWS -->', ''];
        $markers['TemplateNews'][] = ['<!-- BEGIN SINGLE-NEWS -->', ''];
        $markers['TemplateNews'][] = ['###NEWS_TOPIC###', ''];
        $markers['TemplateNews'][] = ['###NEWS_BODY###', ''];
        $markers['TemplateNews'][] = ['###NEWS_DATE###', ''];
        $markers['TemplateNews'][] = ['###NEWS_ADMIN-MESSAGE###', ''];
        $markers['TemplateNews'][] = ['###NEWS_NO###', ''];
        $markers['TemplateNews'][] = ['###FULLNAME###', _("Vollständiger Name des Autors.")];
        $markers['TemplateNews'][] = ['###LASTNAME###', _("Nachname des Autors.")];
        $markers['TemplateNews'][] = ['###FIRSTNAME###', _("Vorname des Autors.")];
        $markers['TemplateNews'][] = ['###TITLEFRONT###', _("Titel des Autors (vorangestellt).")];
        $markers['TemplateNews'][] = ['###TITLEREAR###', _("Titel des Autors (nachgestellt).")];
        $markers['TemplateNews'][] = ['###PERSONDETAIL-HREF###', ''];
        $markers['TemplateNews'][] = ['###USERNAME###', ''];
        $markers['TemplateNews'][] = ['<!-- END SINGLE-NEWS -->', ''];
        $markers['TemplateNews'][] = ['<!-- END ALL-NEWS -->', ''];
        $markers['TemplateNews'][] = ['<!-- END NEWS -->', ''];

        $markers['TemplateStudipData'][] = ['<!-- BEGIN STUDIP-DATA -->', ''];
        $markers['TemplateStudipData'][] = ['###HOME-INST-NAME###', ''];
        $markers['TemplateStudipData'][] = ['###HOME-INST-HREF###', ''];
        $markers['TemplateStudipData'][] = ['###COUNT-USER###', ''];
        $markers['TemplateStudipData'][] = ['###COUNT-POSTINGS###', ''];
        $markers['TemplateStudipData'][] = ['###COUNT-DOCUMENTS###', ''];

        $markers['TemplateStudipData'][] = ['<!-- BEGIN INVOLVED-INSTITUTES -->', ''];
        $markers['TemplateStudipData'][] = ['<!-- BEGIN INVOLVED-INSTITUTE -->', ''];
        $markers['TemplateStudipData'][] = ['###INVOLVED-INSTITUTE_HREF###', ''];
        $markers['TemplateStudipData'][] = ['###INVOLVED-INSTITUTE_NAME###', ''];
        $markers['TemplateStudipData'][] = ['<!-- END INVOLVED-INSTITUTE -->', ''];
        $markers['TemplateStudipData'][] = ['<!-- END INVOLVED-INSTITUTES -->', ''];

        $markers['TemplateStudipData'][] = ['<!-- END STUDIP-DATA -->', ''];

        return $markers[$element_name];
    }

    function getContent ($args = NULL, $raw = FALSE) {
        $this->seminar_id = $args["seminar_id"];
        $seminar = new Seminar($this->seminar_id);

        $visible = $this->config->getValue("Main", "visible");

        $j = -1;
        if ($seminar->visible == 1) {
            $content['LECTUREDETAILS']['TITLE'] = ExternModule::ExtHtmlReady($seminar->getName());
            if (trim($seminar->seminar_number)) {
                $content['LECTUREDETAILS']['NUMBER'] = ExternModule::ExtHtmlReady($seminar->seminar_number);
            }
            if (trim($seminar->subtitle)) {
                $content['LECTUREDETAILS']['SUBTITLE'] = ExternModule::ExtHtmlReady($seminar->subtitle);
            }
            if (trim($seminar->description)) {
                $content['LECTUREDETAILS']['DESCRIPTION'] = ExternModule::ExtHtmlReady($seminar->description, TRUE);
            }
            if (trim($seminar->misc)) {
                $content['LECTUREDETAILS']['MISC'] = ExternModule::ExtHtmlReady($seminar->misc, TRUE);
            }
            if (trim($seminar->participants)) {
                $content['LECTUREDETAILS']['PARTICIPANTS'] = ExternModule::ExtHtmlReady($seminar->participants);
            }
            if (trim($seminar->requirements)) {
                $content['LECTUREDETAILS']['REQUIREMENTS'] = ExternModule::ExtHtmlReady($seminar->requirements);
            }
            if (trim($seminar->orga)) {
                $content['LECTUREDETAILS']['ORGA'] = ExternModule::ExtHtmlReady($seminar->orga);
            }
            if (trim($seminar->leistungsnachweis)) {
                $content['LECTUREDETAILS']['LEISTUNGSNACHWEIS'] = ExternModule::ExtHtmlReady($seminar->leistungsnachweis);
            }
            if (trim($seminar->form)) {
                $content['LECTUREDETAILS']['FORM'] = ExternModule::ExtHtmlReady($seminar->form);
            }
            if (trim($seminar->ects)) {
                $content['LECTUREDETAILS']['ECTS'] = ExternModule::ExtHtmlReady($seminar->ects);
            }

            if (!$name_sql = $this->config->getValue("Main", "nameformat")) {
                $name_sql = "full";
            }

            $lecturers = array_keys($seminar->getMembers('dozent'));

            $l = 0;
            foreach ($lecturers as $lecturer) {
                $query = "SELECT {$GLOBALS['_fullname_sql'][$name_sql]} AS name, username, Vorname, Nachname, title_rear, title_front FROM auth_user_md5 aum LEFT JOIN user_info ui USING(user_id) WHERE aum.user_id = ?";
                $parameters = [$lecturer];
                $state = DBManager::get()->prepare($query);
                $state->execute($parameters);
                $rowlec = $state->fetch(PDO::FETCH_ASSOC);
                if ($rowlec !== false) {
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['PERSONDETAILS-HREF'] = $this->elements['LinkInternPersondetails']->createUrl(['link_args' => 'username=' . $rowlec['username']]);
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['FULLNAME'] = ExternModule::ExtHtmlReady($rowlec['name']);
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['FIRSTNAME'] = ExternModule::ExtHtmlReady($rowlec['Vorname']);
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['LASTNAME'] = ExternModule::ExtHtmlReady($rowlec['Nachname']);
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['TITLEFRONT'] = ExternModule::ExtHtmlReady($rowlec['title_front']);
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['TITLEREAR'] = ExternModule::ExtHtmlReady($rowlec['title_rear']);
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['UNAME'] = $rowlec['username'];
                    $content['LECTUREDETAILS']['LECTURERS']['LECTURER'][$l]['LECTURER-NO'] = $l + 1;
                    $l++;
                }
            }

            $tutors = array_keys($seminar->getMembers('tutor'));

            $l = 0;
            foreach ($tutors as $tutor) {
                $query = "SELECT {$GLOBALS['_fullname_sql'][$name_sql]} AS name, username, Vorname, Nachname, title_rear, title_front FROM auth_user_md5 aum LEFT JOIN user_info ui USING(user_id) WHERE aum.user_id = ?";
                $parameters = [$tutor];
                $state = DBManager::get()->prepare($query);
                $state->execute($parameters);
                $rowtut = $state->fetch(PDO::FETCH_ASSOC);
                if ($rowtut !== false) {
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_PERSONDETAILS-HREF'] = $this->elements['LinkInternPersondetails']->createUrl(['link_args' => 'username=' . $rowtut['username']]);
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_FULLNAME'] = ExternModule::ExtHtmlReady($rowtut['name']);
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_FIRSTNAME'] = ExternModule::ExtHtmlReady($rowtut['Vorname']);
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_LASTNAME'] = ExternModule::ExtHtmlReady($rowtut['Nachname']);
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_TITLEFRONT'] = ExternModule::ExtHtmlReady($rowtut['title_front']);
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_TITLEREAR'] = ExternModule::ExtHtmlReady($rowtut['title_rear']);
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR_UNAME'] = $rowtut['username'];
                    $content['LECTUREDETAILS']['TUTORS']['TUTOR'][$l]['TUTOR-NO'] = $l + 1;
                    $l++;
                }
            }

            // reorganize the $SEM_TYPE-array
            foreach ($GLOBALS["SEM_CLASS"] as $key_class => $class) {
                $i = 0;
                foreach ($GLOBALS["SEM_TYPE"] as $key_type => $type) {
                    if ($type["class"] == $key_class) {
                        $i++;
                        $sem_types_position[$key_type] = $i;
                    }
                }
            }
            $aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
                    "class_" . $GLOBALS["SEM_TYPE"][$seminar->status]['class']);
            if ($aliases_sem_type[$sem_types_position[$seminar->status] - 1]) {
                $content['LECTUREDETAILS']['SEMTYPE-SUBSTITUTE'] = $aliases_sem_type[$sem_types_position[$seminar->status] - 1];
            } else {
                $content['LECTUREDETAILS']['SEMTYPE-SUBSTITUTE'] = ExternModule::ExtHtmlReady($GLOBALS["SEM_TYPE"][$seminar->status]["name"]);
            }
            $content['LECTUREDETAILS']['SEMTYPE'] = ExternModule::ExtHtmlReady($GLOBALS["SEM_TYPE"][$seminar->status]["name"]);
            $room = trim(Seminar::getInstance($this->seminar_id)->getDatesTemplate('dates/seminar_export_location'));
            if ($room) {
                $content['LECTUREDETAILS']['ROOM'] = ExternModule::ExtHtmlReady($room);
            }
            $content['LECTUREDETAILS']['SEMESTER'] = get_semester($this->seminar_id);
            $content['LECTUREDETAILS']['CYCLE'] = ExternModule::ExtHtmlReady(Seminar::getInstance($this->seminar_id)->getDatesExport());
            if ($vorbesprechung = vorbesprechung($this->seminar_id, 'export')) {
                $content['LECTUREDETAILS']['PRELIM-DISCUSSION'] = ExternModule::ExtHtmlReady($vorbesprechung);
            }
            if ($veranstaltung_beginn = Seminar::getInstance($this->seminar_id)->getFirstDate('export')) {
                $content['LECTUREDETAILS']['FIRST-MEETING'] = ExternModule::ExtHtmlReady($veranstaltung_beginn);
            }

            $range_path_level = $this->config->getValue('Main', 'rangepathlevel');
            $pathes = get_sem_tree_path($this->seminar_id, $range_path_level);
            if (is_array($pathes)) {
                $i = 0;
                foreach ($pathes as $foo => $path) {
                    $content['LECTUREDETAILS']['RANGE-PATHES']['RANGE-PATH'][$i]['PATH'] = ExternModule::ExtHtmlReady($path);
                    $i++;
                }
            }

            if ($seminar->getSemClass()['module']) {
                ModuleManagementModelTreeItem::setObjectFilter('Modul', function ($modul) use ($seminar) {
                    // check for public status
                    if (!$GLOBALS['MVV_MODUL']['STATUS']['values'][$modul->stat]['public']) {
                        return false;
                    }
                    $modul_start = Semester::find($modul->start)->beginn ?: 0;
                    $modul_end = Semester::find($modul->end)->beginn ?: PHP_INT_MAX;
                    return $seminar->start_time <= $modul_end &&
                           ($modul_start <= $seminar->start_time + $seminar->duration_time || $seminar->duration_time == -1);
                });
                ModuleManagementModelTreeItem::setObjectFilter('StgteilVersion', function ($version) {
                    return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                });
                $trail_classes = ['Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion'];
                $mvv_object_paths = MvvCourse::get($this->seminar_id)->getTrails($trail_classes);
                $mvv_paths = [];

                foreach ($mvv_object_paths as $mvv_object_path) {
                    // show only complete paths
                    if (count($mvv_object_path) === 4) {
                        $mvv_object_names = [];
                        foreach ($mvv_object_path as $mvv_object) {
                            $mvv_object_names[] = $mvv_object->getDisplayName();
                        }
                        $mvv_paths[] = implode(' > ', $mvv_object_names);
                    }
                }

                foreach ($mvv_paths as $mvv_path) {
                    $content['LECTUREDETAILS']['MODULES']['MODULE'][] = ['PATH' => ExternModule::ExtHtmlReady($mvv_path)];
                }
            }

            $content['LECTUREDETAILS']['NEWS'] = $this->elements['TemplateNews']->toString(['content' => $this->getContentNews(), 'subpart' => 'NEWS']);
            $content['LECTUREDETAILS']['STUDIP-DATA'] = $this->getStudipData();

            // generic data fields
            if ($generic_datafields = $this->config->getValue('Main', 'genericdatafields')) {
                $localEntries = DataFieldEntry::getDataFieldEntries($this->seminar_id, 'sem');
                $k = 1;
                foreach ($generic_datafields as $datafield) {
                    if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
                        $localEntry = trim($localEntries[$datafield]->getDisplayValue());
                        if ($localEntry) {
                            $content['LECTUREDETAILS']["DATAFIELD_$k"] = $localEntry;
                        }
                    }
                    $k++;
                }
            }

            $content['__GLOBAL__']['STUDIP-EDIT-HREF'] = "{$GLOBALS['ABSOLUTE_URI_STUDIP']}seminar_main.php?auswahl={$this->seminar_id}&again=1&redirect_to=dispatch.php/course/basicdata/view/".$this->seminar_id."&login=true&new_sem=TRUE";
            $content['__GLOBAL__']['STUDIP-REGISTER-HREF'] = "{$GLOBALS['ABSOLUTE_URI_STUDIP']}dispatch.php/course/details/?again=1&sem_id={$this->seminar_id}";
        }

        return $content;
    }

    private function getContentNews ()
    {
        $local_fullname_sql = $GLOBALS['_fullname_sql'];
        if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
            $nameformat = 'no_title';
        }
        if ($nameformat == 'last') {
            $local_fullname_sql['last'] = ' Nachname ';
        }
        $dateform = $this->config->getValue('Main', 'dateformat');

        $news = StudipNews::GetNewsByRange($this->seminar_id, TRUE);
        if (!count($news)) {
            $content['NEWS']['NO-NEWS']['NEWS_NO-NEWS-TEXT'] = $this->config->getValue('Main', 'nodatatext');
        } else {
            $i = 0;
            foreach ($news as $news_id => $news_detail) {
                list($news_content, $admin_msg) = explode("<admin_msg>", $news_detail['body']);
                if ($admin_msg) {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_ADMIN-MESSAGE'] = preg_replace('# \(.*?\)#', '', $admin_msg);
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_BODY'] = ExternModule::ExtFormatReady($news_content);
                } else {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_BODY'] = ExternModule::ExtFormatReady($news_detail['body']);
                }
                $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_DATE'] = strftime($dateform, $news_detail['date']);
                $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_TOPIC'] = ExternModule::ExtHtmlReady($news_detail['topic']);
                $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_NO'] = $i + 1;

                $query = "SELECT Nachname, Vorname, title_front, title_rear,
                                 {$local_fullname_sql[$nameformat]} AS fullname, username,
                                 aum.user_id
                          FROM auth_user_md5 AS aum
                          LEFT JOIN user_info AS ui USING (user_id)
                          WHERE aum.user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$news_detail['user_id']]);
                $temp = $statement->fetch(PDO::FETCH_ASSOC);
                if ($temp) {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['FULLNAME'] = ExternModule::ExtHtmlReady($temp['fullname']);
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['FIRSTNAME'] = ExternModule::ExtHtmlReady($temp['Vorname']);
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['LASTNAME'] = ExternModule::ExtHtmlReady($temp['Nachname']);
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['TITLEFRONT'] = ExternModule::ExtHtmlReady($temp['title_front']);
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['TITLEREAR'] = ExternModule::ExtHtmlReady($temp['title_rear']);
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['USERNAME'] = $temp['username'];
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-HREF'] = $this->elements['LinkInternPersondetails']->createUrl(['link_args' => 'username=' . $temp['username']]);
                }
                $i++;
            }
        }
        return $content;
    }

    function getStudipData () {
        $query = "SELECT i.Institut_id, i.Name, i.url FROM seminare LEFT JOIN Institute i USING(institut_id) WHERE Seminar_id = ?";
        $parameters = [$this->seminar_id];
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $own_inst = $row['Institut_id'];
        $content['STUDIP-DATA']['HOME-INST-NAME'] = ExternModule::ExtHtmlReady($row['Name']);

        if ($row['url']) {
            $link_inst = htmlReady($row['url']);
            if (!preg_match('{^https?://.+$}', $link_inst)) {
                $link_inst = "http://$link_inst";
            }
            $content['STUDIP-DATA']['HOME-INST-HREF'] = $link_inst;
        }

        $query = "SELECT Name, url FROM seminar_inst LEFT JOIN Institute i USING(institut_id) WHERE seminar_id='{$this->seminar_id}' AND i.institut_id!='$own_inst'";
        $involved_insts = NULL;
        $i = 0;
        $statement = DBManager::get()->prepare($query);
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($row['url']) {
                $link_inst = htmlReady($row['url']);
                if (!preg_match('{^https?://.+$}', $link_inst)) {
                    $link_inst = "http://$link_inst";
                }
                $content['STUDIP-DATA']['INVOLVED-INSTITUES']['INVOLVED-INSTITUTE'][$i]['INVOLVED-INSTITUTE_HREF'] = $link_inst;
            }
            $content['STUDIP-DATA']['INVOLVED-INSTITUTES']['INVOLVED-INSTITUTE'][$i]['INVOLVED-INSTITUTE_NAME'] = ExternModule::ExtHtmlReady($row['Name']);
            $i++;
        }

        $query = "SELECT count(*) as count_user FROM seminar_user WHERE Seminar_id = ?";
        $parameters = [$this->seminar_id];
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row['count_user']) {
            $content['STUDIP-DATA']['COUNT-USER'] = $row['count_user'];
        } else {
            $content['STUDIP-DATA']['COUNT-USER'] = '0';
        }

        $count = 0;
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $count += $plugin->getNumberOfPostingsForSeminar($this->seminar_id);
        }
        $content['STUDIP-DATA']['COUNT-POSTINGS'] = $count;

        $query = "SELECT COUNT(*) AS count_documents
                  FROM folders
                  INNER JOIN file_refs ON folder_id = folders.id
                  WHERE range_id = ? AND range_type = 'course'
            AND folder_type IN ('RootFolder', 'StandardFolder')
                  GROUP BY range_id";
        $parameters = [$this->seminar_id];
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row['count_documents']) {
            $content['STUDIP-DATA']['COUNT-DOCUMENTS'] = $row['count_documents'];
        } else {
            $content['STUDIP-DATA']['COUNT-DOCUMENTS'] = '0';
        }

        return $this->elements['TemplateStudipData']->toString(['content' => $content, 'subpart' => 'STUDIP-DATA']);
    }

    function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateLectureData']->toString(['content' => $this->getContent($args), 'subpart' => 'LECTUREDETAILS']);

    }

    function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateLectureData']->toString(['content' => $this->getContent([]), 'subpart' => 'LECTUREDETAILS', 'hide_markers' => FALSE]);

    }

    function addContentStudipInfo (&$content) {

    }
}

?>
