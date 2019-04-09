<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleTemplateDownload.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplateDownload
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplateDownload.class.php
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
require_once 'lib/statusgruppe.inc.php';


class ExternModuleTemplateDownload extends ExternModule {

    var $markers = [];
    var $args = ['seminar_id'];

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        $this->data_fields = ["icon", "filename", "description", "mkdate",
                             "filesize", "fullname"];
        $this->registered_elements = [
                'LinkInternTemplate', 'TemplateGeneric'
        ];

        $this->field_names =  [
                _("Icon"),
                _("Dateiname"),
                _("Beschreibung"),
                _("Datum"),
                _("Größe"),
                _("Upload durch")
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

        $this->elements['TemplateGeneric']->real_name = _("Template");
        $this->elements['LinkInternTemplate']->link_module_type = [2, 14];
        $this->elements['LinkInternTemplate']->real_name = _("Link zum Modul MitarbeiterInnendetails");

    }

    function toStringEdit ($open_elements = '', $post_vars = '',
            $faulty_values = '', $anker = '') {

        $this->updateGenericDatafields('TemplateGeneric', 'user');
        $this->elements['TemplateGeneric']->markers = $this->getMarkerDescription('TemplateGeneric');

        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }

    function getMarkerDescription ($element_name) {
        $markers['TemplateGeneric'][] = ['__GLOBAL__', _("Globale Variablen (gültig im gesamten Template).")];
        $markers['TemplateGeneric'][] = ['###FILES-COUNT###', ''];
        $markers['TemplateGeneric'][] = ['<!-- BEGIN DOWNLOAD -->', ''];
        $markers['TemplateGeneric'][] = ['<!-- BEGIN NO-FILES -->', ''];
        $markers['TemplateGeneric'][] = ['###NO-FILES-TEXT###', ''];
        $markers['TemplateGeneric'][] = ['<!-- END NO-FILES -->', ''];
        $markers['TemplateGeneric'][] = ['<!-- BEGIN FILES -->', ''];
        $markers['TemplateGeneric'][] = ['<!-- BEGIN FILE -->', ''];
        $markers['TemplateGeneric'][] = ['###FILE_NAME###', ''];
        $markers['TemplateGeneric'][] = ['###FILE_FILE-NAME###', ''];
        $markers['TemplateGeneric'][] = ['###FILE_SIZE###', ''];
        $markers['TemplateGeneric'][] = ['###FILE_NO###', ''];
        $markers['TemplateGeneric'][] = ['###FILE_DESCRIPTION###', ''];
        $markers['TemplateGeneric'][] = ['###FILE_UPLOAD-DATE###', ''];
        $markers['TemplateGeneric'][] = ['###FULLNAME###', ''];
        $markers['TemplateGeneric'][] = ['###LASTNAME###', ''];
        $markers['TemplateGeneric'][] = ['###FIRSTNAME###', ''];
        $markers['TemplateGeneric'][] = ['###TITLEFRONT###', ''];
        $markers['TemplateGeneric'][] = ['###TITLEREAR###', ''];
        $markers['TemplateGeneric'][] = ['###PERSONDETAIL-HREF###', ''];
        $markers['TemplateGeneric'][] = ['###USERNAME###', ''];
        $this->insertDatafieldMarkers('user', $markers, 'TemplateGeneric');
        $markers['TemplateGeneric'][] = ['###FILE_HREF###', ''];
        $markers['TemplateGeneric'][] = ['###FILE_ICON-HREF###', ''];
        $markers['TemplateGeneric'][] = ['<!-- BEGIN PERSONDETAIL-LINK -->'];
        $markers['TemplateGeneric'][] = ['###LINK_FULLNAME###', ''];
        $markers['TemplateGeneric'][] = ['###LINK_LASTNAME###', ''];
        $markers['TemplateGeneric'][] = ['###LINK_FIRSTNAME###', ''];
        $markers['TemplateGeneric'][] = ['###LINK_TITLEFRONT###', ''];
        $markers['TemplateGeneric'][] = ['###LINK_TITLEREAR###', ''];
        $markers['TemplateGeneric'][] = ['###LINK_PERSONDETAIL-HREF###', ''];
        $markers['TemplateGeneric'][] = ['<!-- END PERSONDETAIL-LINK -->'];
        $markers['TemplateGeneric'][] = ['<!-- END FILE -->'];
        $markers['TemplateGeneric'][] = ['<!-- END FILES -->', ''];
        $markers['TemplateGeneric'][] = ['<!-- END DOWNLOAD -->', ''];

        return $markers[$element_name];
    }

    function getContent ($args = NULL, $raw = FALSE) {
        $error_message = "";
        if (!$args) {
            $args = [];
        }
        $content = [];

        // check for valid range_id
        if(!$this->checkRangeId($this->config->range_id)) {
            $error_message = $GLOBALS['EXTERN_ERROR_MESSAGE'];
        }
        // if $args['seminar_id'] is given, check for free access
        if ($args['seminar_id']) {
            $seminar_id = $args['seminar_id'];
            $query = "SELECT Lesezugriff FROM seminare s LEFT JOIN seminar_inst si ";
            $query .= "USING(seminar_id) WHERE s.seminar_id = ? ";
            $query .= "AND si.institut_id = ?";
            $params = [$seminar_id, $this->config->range_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($params);
            $row = $statement->fetchColumn();
            if ($row !== false && $row == 0 ) {
                 $error_message = $GLOBALS['EXTERN_ERROR_MESSAGE'];
            }
        } else {
            $seminar_id = $this->config->range_id;
        }

        $sort = (array) $this->config->getValue('Main', 'sort');
        $query_order = '';
        foreach ($sort as $key => $position) {
            if ($position > 0) {
                $query_order[$position] = $this->data_fields[$key];
            }
        }
        if ($query_order) {
            ksort($query_order, SORT_NUMERIC);
            $query_order = implode(',', $query_order) . ' DESC';
        }

        if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
            $nameformat = 'no_title_short';
        }

        // generic data fields
        $generic_datafields = $this->config->getValue('TemplateGeneric', 'genericdatafields');

        $downloadable_file_refs = [];

        $top_folder = Folder::findTopFolder($seminar_id);
        $top_folder = $top_folder->getTypedFolder();

        $files = $folders = [];
        extract(FileManager::getFolderFilesRecursive($top_folder, 'nobody'));

        foreach ($files as $f) {
            if ($folders[$f->folder_id]->isFileDownloadable($f, 'nobody')) {
                $file_data = $f->toArray();
                $file_data['fullname'] = $f->owner->getFullname($nameformat);
                $file_data['username'] = $f->owner->username;
                $file_data['vorname'] = $f->owner->vorname;
                $file_data['nachname'] = $f->owner->nachname;
                $file_data['title_front'] = $f->owner->title_front;
                $file_data['title_rear'] = $f->owner->title_rear;

                $file_data['filename'] = $f->name;
                $file_data['filesize'] = $f->size;
                $downloadable_file_refs[] = $file_data;
            }
        }


        if (empty($downloadable_file_refs)) {
            $content['NO-FILES']['NO-FILES-TEXT'] = $this->config->getValue('Main', 'nodatatext');
        } else {
            $i = 0;
            $downloadable_file_refs = new SimpleCollection($downloadable_file_refs);
            $downloadable_file_refs->orderBy($query_order);
            foreach ($downloadable_file_refs as $downloadable_file_ref) {

                $content['FILES']['FILE'][$i]['FILE_ICON-HREF'] = Icon::create(
                    FileManager::getIconNameForMimeType($downloadable_file_ref->mime_type),
                    'clickable'
                    )->asImagePath(16);


                $content['FILES']['FILE'][$i]['FILE_NO'] = $i + 1;

                $download_link = $downloadable_file_ref->download_url;

                $content['FILES']['FILE'][$i]['FILE_HREF'] = $download_link;
                $content['FILES']['FILE'][$i]['FILE_NAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->name);
                $content['FILES']['FILE'][$i]['FILE_FILE-NAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->name);
                $content['FILES']['FILE'][$i]['FILE_DESCRIPTION'] = ExternModule::ExtHtmlReady(mila_extern($downloadable_file_ref->description,
                                                     $this->config->getValue("Main", "lengthdesc")));
                $content['FILES']['FILE'][$i]['FILE_UPLOAD-DATE'] = strftime($this->config->getValue("Main", "dateformat"), $downloadable_file_ref->mkdate);
                $content['FILES']['FILE'][$i]['FILE_SIZE'] = $downloadable_file_ref->filesize > 1048576 ? round($downloadable_file_ref->filesize / 1048576, 1) . " MB" : round($downloadable_file_ref->filesize / 1024, 1) . " kB";

                $content['FILES']['FILE'][$i]['USERNAME'] = $downloadable_file_ref->username;
                $content['FILES']['FILE'][$i]['FULLNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->fullname);
                $content['FILES']['FILE'][$i]['FIRSTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->vorname);
                $content['FILES']['FILE'][$i]['LASTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->nachname);
                $content['FILES']['FILE'][$i]['TITLEFRONT'] = ExternModule::ExtHtmlReady($downloadable_file_ref->title_front);
                $content['FILES']['FILE'][$i]['TITLEREAR'] = ExternModule::ExtHtmlReady($downloadable_file_ref->title_rear);
                $content['FILES']['FILE'][$i]['PERSONDETAIL-HREF'] = $this->elements['LinkInternTemplate']->createUrl(['link_args' => 'username=' . $downloadable_file_ref->username]);

                // if user is member of a group then link name to details page
                $link_persondetail = '';
                if (GetRoleNames(GetAllStatusgruppen($this->config->range_id, $downloadable_file_ref->user_id))) {
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_PERSONDETAIL-HREF'] = $this->elements['LinkInternTemplate']->createUrl(['link_args' => 'username=' . $downloadable_file_ref->username]);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_FULLNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->fullname);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_FIRSTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->vorname);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_LASTNAME'] = ExternModule::ExtHtmlReady($downloadable_file_ref->nachname);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_TITLEFRONT'] = ExternModule::ExtHtmlReady($downloadable_file_ref->title_front);
                    $content['FILES']['FILE'][$i]['PERSONDETAIL-LINK']['LINK_TITLEREAR'] = ExternModule::ExtHtmlReady($downloadable_file_ref->title_rear);
                }

                // generic data fields
                if (is_array($generic_datafields)) {
                    $localEntries = DataFieldEntry::getDataFieldEntries($downloadable_file_ref->owner->user_id, 'user');
                    $k = 1;
                    foreach ($generic_datafields as $datafield) {
                        if (isset($localEntries[$datafield]) && is_object($localEntries[$datafield])) {
                            $localEntry = $localEntries[$datafield]->getDisplayValue();
                            if ($localEntry) {
                                $content['FILES']['FILE'][$i]['DATAFIELD_' . $k] = $localEntry;
                            }
                        }
                        $k++;
                    }
                }

                $i++;
            //}while($row = $statement->fetch(PDO::FETCH_ASSOC));
            }
        }
        $content = ['DOWNLOAD' => $content];
        $content['__GLOBAL__']['FILES-COUNT'] = $i;

        return $content;
    }

    function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateGeneric']->toString(['content' => $this->getContent($args), 'subpart' => 'DOWNLOAD']);

    }

    function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->elements['TemplateGeneric']->toString(['content' => $this->getContent($args), 'subpart' => 'DOWNLOAD', 'hide_markers' => FALSE]);

    }

}

?>
