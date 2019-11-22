<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleDownload.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleDownload
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleDownload.class.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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
require_once 'lib/statusgruppe.inc.php';


class ExternModuleDownload extends ExternModule {

    var $field_names = [];
    var $data_fields = ["icon", "filename", "description", "mkdate",
                             "filesize", "fullname"];
    var $registered_elements = ["Body", "TableHeader", "TableHeadrow",
                                                                     "TableRow", "Link", "LinkIntern", "TableFooter"];
    var $args = ['seminar_id'];

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->field_names =
        [
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
        $this->elements["LinkIntern"]->link_module_type = 2;
        $this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
        $this->elements["Link"]->real_name = _("Link zum Dateidownload");
    }

    function printout ($args) {
        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);

        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->toString($args);

        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }

    function printoutPreview () {
        echo html_header($this->config);

        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        echo $this->toStringPreview();

        echo html_footer();
    }

    function toString ($args = NULL) {

        $error_message = "";

        // check for valid range_id
        if(!$this->checkRangeId($this->config->range_id)) {
            $error_message = $GLOBALS["EXTERN_ERROR_MESSAGE"];
        }
        // if $args['seminar_id'] is given, check for free access
        if ($args['seminar_id']) {
            $seminar_id = $args['seminar_id'];
            $query = "SELECT Lesezugriff FROM seminare s LEFT JOIN seminar_inst si ";
            $query .= "USING(seminar_id) WHERE s.seminar_id = ? ";
            $query .= "AND si.institut_id = ?";

            $parameters = [$seminar_id, $this->config->range_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $row = $statement->fetch(PDO::FETCH_ASSOC);

            if ($row === false && $row['Lesezugriff'] == 0)
                $error_message = $GLOBALS["EXTERN_ERROR_MESSAGE"];
        } else {
            $seminar_id = $this->config->range_id;
        }

        $sort = $this->config->getValue("Main", "sort");
        $query_order = [];
        foreach ($sort as $key => $position) {
            if ($position > 0) {
                $query_order[$position] = $this->data_fields[$key];
            }
        }
        if (count($query_order)) {
            ksort($query_order, SORT_NUMERIC);
            $query_order = implode(",", $query_order) . " DESC";
        } else {
            $query_order = '';
        }

        if (!$nameformat = $this->config->getValue("Main", "nameformat")) {
            $nameformat = "no_title_short";
        }

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
                $file_data['filename'] = $f->name;
                $file_data['filesize'] = $f->size;
                $downloadable_file_refs[] = $file_data;
            }
        }

        if (empty($downloadable_file_refs)) {
            $error_message = $this->config->getValue("Main", "nodatatext");
        }

        $out = $this->elements["TableHeadrow"]->toString();

        if ($error_message) {
            // use one column and set it visible to display error_message
            $this->config->setValue('Main', 'order', ['0']);
            $this->config->setValue('Main', 'visible', ['1']);
            $this->config->setValue('Main', 'width', ['100%']);
            $out = $this->elements['TableRow']->toString(['content' => ['' => $error_message]]);
        } else {

            $table_row_data["data_fields"] = $this->data_fields;
            $downloadable_file_refs = new SimpleCollection($downloadable_file_refs);
            $downloadable_file_refs->orderBy($query_order);
            foreach ($downloadable_file_refs as $downloadable_file_ref) {
                $icon = Icon::create(FileManager::getIconNameForMimeType($downloadable_file_ref->mime_type), 'clickable');

                $download_link = $downloadable_file_ref->download_url;

                // Aufbereiten der Daten
                $table_row_data["content"] = [
                    "icon"        => sprintf("<a href=\"%s\">%s</a>",
                                             $download_link,
                                             $icon->asImg()),

                    "filename"    => $this->elements["Link"]->toString(["content" =>
                                                        htmlReady($downloadable_file_ref->name), "link" => $download_link]),

                    "description" => htmlReady(mila_extern($downloadable_file_ref->description,
                                                     $this->config->getValue("Main", "lengthdesc"))),

                    "mkdate"      => strftime($this->config->getValue("Main", "dateformat"), $downloadable_file_ref->mkdate),

                    "filesize"    => $downloadable_file_ref->size > 1048576 ? round($downloadable_file_ref->size / 1048576, 1) . " MB"
                        : round($downloadable_file_ref->size / 1024, 1) . " kB",

                ];
                // if user is member of a group then link name to details page
                if (GetRoleNames(GetAllStatusgruppen($this->config->range_id, $downloadable_file_ref['user_id']))) {
                    $table_row_data['content']['fullname'] =
                            $this->elements['LinkIntern']->toString(['content' =>
                            htmlReady($downloadable_file_ref->fullname), 'module' => 'Persondetails',
                            'link_args' => 'username=' . $downloadable_file_ref->username]);
                } else {
                    $table_row_data['content']['fullname'] = htmlReady($downloadable_file_ref->fullname);
                }
                $out .= $this->elements["TableRow"]->toString($table_row_data);
            }
        }

        return $this->elements["TableHeader"]->toString(["content" => $out]);
    }

    function toStringPreview () {
        $time = time();
        // preview data
        $data[] = ["dokument_id" => 1, "description" => _("Das ist eine Text-Datei."),
            "filename" => "text_file.txt", "mkdate" => ($time - 100000), "chdate" => ($time - 50000),
            "filesize" => 26378, "Vorname" => "Julius", "Nachname" => "Rodman"];
        $data[] = ["dokument_id" => 2, "description" => _("Das ist eine Powerpoint-Datei."),
            "filename" => "powerpoint_file.ppt", "mkdate" => ($time - 200000), "chdate" => ($time - 150000),
            "filesize" => 263784, "Vorname" => "William", "Nachname" => "Wilson"];
        $data[] = ["dokument_id" => 3, "description" => _("Das ist eine ZIP-Datei."),
            "filename" => "zip_file.zip", "mkdate" => ($time - 300000), "chdate" => ($time - 250000),
            "filesize" => 63784, "Vorname" => "August", "Nachname" => "Bedloe"];
        $data[] = ["dokument_id" => 4, "description" => _("Das ist eine Excel-Datei."),
            "filename" => "excel_file.txt", "mkdate" => ($time - 400000), "chdate" => ($time - 350000),
            "filesize" => 23784, "Vorname" => "Ernst", "Nachname" => "Waldemar"];
        $data[] = ["dokument_id" => 5, "description" => _("Das ist eine Bild-Datei."),
            "filename" => "bild_jpeg_file.jpg", "mkdate" => ($time - 500000), "chdate" => ($time - 450000),
            "filesize" => 53784, "Vorname" => "Absalom", "Nachname" => "Hicks"];
        $data[] = ["dokument_id" => 6, "description" => _("Das ist ein Dokument im Microsoft Rich-Text-Format."),
            "filename" => "microsoft_rtf_file.rtf", "mkdate" => ($time - 600000), "chdate" => ($time - 550000),
            "filesize" => 563784, "Vorname" => "Dirk", "Nachname" => "Peters"];
        $data[] = ["dokument_id" => 7, "description" => _("Das ist ein Adobe PDF-Dokument."),
            "filename" => "adobe_pdf_file.pdf", "mkdate" => ($time - 700000), "chdate" => ($time - 650000),
            "filesize" => 13784, "Vorname" => "Augustus", "Nachname" => "Barnard"];
        $data[] = ["dokument_id" => 8, "description" => _("Und noch ein ZIP-Archiv."),
            "filename" => "gnu_zip_file.tar.gz", "mkdate" => ($time - 800000), "chdate" => ($time - 750000),
            "filesize" => 2684, "Vorname" => "Gordon", "Nachname" => "Pym"];
        $data[] = ["dokument_id" => 9, "description" => _("Eine weitere Text-Datei."),
            "filename" => "text2_file.txt", "mkdate" => ($time - 900000), "chdate" => ($time - 850000),
            "filesize" => 123784, "Vorname" => "Hans", "Nachname" => "Pfaal"];
        $data[] = ["dokument_id" => 10, "description" => _("Ein Bild im PNG-Format."),
            "filename" => "picture_png_file.png", "mkdate" => ($time - 1000000), "chdate" => ($time - 950000),
            "filesize" => 813784, "Vorname" => "John", "Nachname" => "Greely"];
        $data[] = ["dokument_id" => 11, "description" => _("Eine anderes Format."),
            "filename" => "good_music.mp3", "mkdate" => ($time - 1150000), "chdate" => ($time - 653900),
            "filesize" => 934651, "Vorname" => "Augustus", "Nachname" => "Barnard"];

        $table_row_data["data_fields"] = $this->data_fields;
        $out = $this->elements["TableHeadrow"]->toString();

        foreach ($data as $db) {

            preg_match("/^.+\.([a-z1-9_\-]+)$/i", $db["filename"], $file_suffix);

            // choose the icon for the given file format
            $icon = "";
            switch ($file_suffix[1]) {
                case "txt" :
                    if (!$picture_file = $this->config->getValue("Main", "icontxt"))
                        $icon = Icon::create("file-text", "clickable");
                    break;
                case "xls" :
                    if (!$picture_file = $this->config->getValue("Main", "iconxls"))
                        $icon = Icon::create("file-xls", "clickable");
                    break;
                case "ppt" :
                    if (!$picture_file = $this->config->getValue("Main", "iconppt"))
                        $icon = Icon::create("file-presentation", "clickable");
                    break;
                case "rtf" :
                    if (!$picture_file = $this->config->getValue("Main", "iconrtf"))
                        $icon = Icon::create("file-text", "clickable");
                    break;
                case "zip" :
                case "tgz" :
                case "gz" :
                    if (!$picture_file = $this->config->getValue("Main", "iconzip"))
                        $icon = Icon::create("file-archive", "clickable");
                    break;
                case "jpg" :
                case "png" :
                case "gif" :
                case "jpeg" :
                case "tif" :
                    if (!$picture_file = $this->config->getValue("Main", "iconpic"))
                        $icon = Icon::create("file-image", "clickable");
                    break;
                case "pdf" :
                    if (!$picture_file = $this->config->getValue("Main", "iconpdf"))
                        $icon = Icon::create("file-pdf", "clickable");
                    break;
                default :
                    if (!$picture_file = $this->config->getValue("Main", "icondefault"))
                        $icon = Icon::create("file-generic", "clickable");
            }

            if ($icon)
                $picture_file = $icon;

            // Aufbereiten der Daten
            $table_row_data["content"] = [
                "icon"        => $this->elements["Link"]->toString(
                    [
                        "content" => is_string($picture_file)
                                     ? Assets::img($picture_file)
                                     : $picture_file->asImg(),
                        "link"    => ""]),

                "filename"    => $this->elements["Link"]->toString(["content" =>
                                                    htmlReady($db["filename"]), "link" => ""]),

                "description" => htmlReady(mila_extern($db["description"],
                                                    $this->config->getValue("Main", "lengthdesc"))),

                "mkdate"      => strftime($this->config->getValue("Main", "dateformat"), $db["mkdate"]),

                "filesize"    => $db["filesize"] > 1048576 ? round($db["filesize"] / 1048576, 1) . " MB"
                                                    : round($db["filesize"] / 1024, 1) . " kB",

                "fullname"    => $this->elements["LinkIntern"]->toString(
                                                    ["content" => htmlReady($db["Vorname"]." ".$db["Nachname"])])

            ];
            $out .= $this->elements["TableRow"]->toString($table_row_data);
        }

        return $this->elements["TableHeader"]->toString(["content" => $out]);
    }


}

?>
