<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternModuleLecturedetails.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleLecturedetails
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLecturedetails.class.php
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
require_once 'lib/extern/modules/views/ExternSemBrowse.class.php';
require_once 'lib/dates.inc.php';

class ExternModuleLecturedetails extends ExternModule {


    // private
    var $seminar_id;

    /**
    *
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
        $this->data_fields = ['subtitle', 'lecturer', 'art', 'status', 'description',
            'location', 'semester', 'time', 'number', 'teilnehmer', 'requirements',
            'lernorga', 'leistung', 'range_path', 'misc', 'ects'];
        $this->registered_elements = [
            'ReplaceTextSemType',
            'Body',
            'TableHeader',
            'SemName' => 'TableParagraphText',
            'Headline' => 'TableParagraphText',
            'Content' => 'TableParagraphText',
            'LinkInternSimple' => 'LinkIntern',
            'StudipInfo',
            'StudipLink'];
        $this->args = ['seminar_id'];
        $this->field_names = [
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
                _("ECTS-Punkte")];
        parent::__construct($range_id, $module_name, $config_id, $set_config, $global_id);
    }

    function setup () {
        // extend $data_fields if generic datafields are set
        $config_datafields = $this->config->getValue("Main", "genericdatafields");
        $this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);

        // setup module properties
        $this->elements["SemName"]->real_name = _("Name der Veranstaltung");
        $this->elements["Headline"]->real_name = _("Überschriften");
        $this->elements["Content"]->real_name = _("Absätze");
        $this->elements["LinkInternSimple"]->link_module_type = 2;
        $this->elements["LinkInternSimple"]->real_name = _("Link zum Modul Mitarbeiterdetails");
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

        if ($this->config->getValue("Main", "wholesite"))
            echo html_header($this->config);

        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);

        include "lib/extern/modules/views/lecturedetails_preview.inc.php";

        if ($this->config->getValue("Main", "wholesite"))
            echo html_footer();
    }

    function toString ($args) {
        $out = "";
        $this->seminar_id = $args["seminar_id"];
        $query = "SELECT * FROM seminare WHERE Seminar_id = ?";
        $parameters = [$this->seminar_id];
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $visible = $this->config->getValue("Main", "visible");
        $j = -1;
        if ($row !== false) {

            $data["name"] = htmlReady($row['Name']);

            if ($visible[++$j] && $row['Untertitel'])
                $data["subtitle"] = htmlReady($row['Untertitel']);

            if ($visible[++$j]) {
                if (!$name_sql = $this->config->getValue("Main", "nameformat"))
                    $name_sql = "full";
                $name_sql = $GLOBALS['_fullname_sql'][$name_sql];

                $query = "SELECT $name_sql AS name, username, position FROM seminar_user su LEFT JOIN
                        auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id)
                        WHERE su.Seminar_id = ? AND su.status='dozent' ORDER BY position, username";
                $parameters = [$this->seminar_id];
                $statement = DBManager::get()->prepare($query);
                $statement->execute($parameters);
                while ($res = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $data["lecturer"][] = $this->elements["LinkInternSimple"]->toString(
                            ["module" => "Persondetails",
                            "link_args" => "username=" . $res['username']
                            . "&seminar_id=" . $this->seminar_id,
                            "content" => $res['name'] ]);
                }
                if (is_array($data["lecturer"]))
                    $data["lecturer"] = implode(", ", $data["lecturer"]);
            }

            if ($visible[++$j] && $row['art'])
                $data["art"] = htmlReady($row['art']);

            if ($visible[++$j]) {
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
                        "class_" . $GLOBALS["SEM_TYPE"][$row['status']]['class']);
                if ($aliases_sem_type[$sem_types_position[$row['status']] - 1])
                    $data["status"] =  $aliases_sem_type[$sem_types_position[$row['status']] - 1];
                else
                    $data["status"] = htmlReady($GLOBALS["SEM_TYPE"][$row['status']]["name"]);
            }

            if ($visible[++$j] && $row['Beschreibung'])
                $data["description"] = formatLinks($row['Beschreibung']);

            if ($visible[++$j])
                $data["location"] = htmlReady(Seminar::getInstance($this->seminar_id)->getDatesTemplate('dates/seminar_export_location'));

            if ($visible[++$j])
                $data["semester"] = get_semester($this->seminar_id);

            if ($visible[++$j]) {
                $data["time"] = htmlReady(Seminar::getInstance($this->seminar_id)->getDatesExport());
                if ($first_app = vorbesprechung($this->seminar_id, 'export')) {
                    $data["time"] .= "<br>" . $this->config->getValue("Main", "aliaspredisc") . htmlReady($first_app);
                }
                if ($begin = Seminar::getInstance($this->seminar_id)->getFirstDate('export')) {
                    $data["time"] .= "<br>" . $this->config->getValue("Main", "aliasfirstmeeting") . htmlReady($begin);
                }
            }

            if ($visible[++$j] && $row['VeranstaltungsNummer'])
                $data["number"] = htmlReady($row['VeranstaltungsNummer']);

            if ($visible[++$j] && $row['teilnehmer'])
                $data["teilnehmer"] = htmlReady($row['teilnehmer']);

            if ($visible[++$j] && $row['vorrausetzungen'])
                $data["requirements"] = htmlReady($row['vorrausetzungen']);

            if ($visible[++$j] && $row['lernorga'])
                $data["lernorga"] = htmlReady($row['lernorga']);

            if ($visible[++$j] && $row['leistungsnachweis'])
                $data["leistung"] = htmlReady($row['leistungsnachweis']);

            if ($visible[++$j]) {
                $range_path_level = $this->config->getValue("Main", "rangepathlevel");
                $pathes = get_sem_tree_path($this->seminar_id, $range_path_level);
                if (is_array($pathes)) {
                    $data["range_path"] = htmlReady(implode("\n", array_values($pathes)),true,true);
                }
            }

            if ($visible[++$j] && $row['Sonstiges'])
                $data["misc"] = formatLinks($row['Sonstiges']);

            if ($visible[++$j] && $row['ects'])
                $data["ects"] = htmlReady($row['ects']);

            // generic data fields
            if ($generic_datafields = $this->config->getValue("Main", "genericdatafields")) {
                $localEntries = DataFieldEntry::getDataFieldEntries($this->seminar_id);
                foreach ($generic_datafields as $id) {
                    if ($visible[++$j] && isset($localEntries[$id]) && is_object($localEntries[$id])) {
                        $data[$id] = $localEntries[$id]->getDisplayValue();
                    }
                }
            }
            $out = $this->toStringMainTable($data, FALSE);
        }

        return $out;
    }

    function toStringPreview ($args) {
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

        $data_sem["name"] = _("Name der Veranstaltung");
        $data_sem["subtitle"] = _("Untertitel der Veranstaltung");
        switch ($this->config->getValue("Main", "nameformat")) {
            case "no_title_short" :
                $data_sem["lecturer"] = _("Meyer, P.");
                break;
            case "no_title" :
                $data_sem["lecturer"] = _("Peter Meyer");
                break;
            case "no_title_rev" :
                $data_sem["lecturer"] = _("Meyer Peter");
                break;
            case "full" :
                $data_sem["lecturer"] = _("Dr. Peter Meyer");
                break;
            case "full_rev" :
                $data_sem["lecturer"] = _("Meyer, Peter, Dr.");
                break;
            default :
                $data_sem["lecturer"] = _("Dr. Peter Meyer");
        }
        $data_sem["art"] = _("Testveranstaltung");
        $data_sem["semtype"] = 1;
        $data_sem["description"] = str_repeat(_("Beschreibung") . " ", 10);
        $data_sem["location"] = _("A 123, 1. Stock");
        $data_sem["semester"] = "WS 2003/2004";
        $data_sem["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
        $data_sem["number"] = "1234";
        $data_sem["teilnehmer"] = str_repeat(_("Teilnehmende") . " ", 6);
        $data_sem["requirements"] = str_repeat(_("Voraussetzungen") . " ", 6);
        $data_sem["lernorga"] = str_repeat(_("Lernorganisation") . " ", 6);
        $data_sem["leistung"] = str_repeat(_("Leistungsnachweis") . " ", 6);
        $data_sem["range_path"] = _("Fakultät &gt; Studiengang &gt; Bereich");
        $data_sem["misc"] = str_repeat(_("Sonstiges") . " ", 6);
        $data_sem["ects"] = "4";


        setlocale(LC_TIME, $this->config->getValue("Main", "timelocale"));
        $order = $this->config->getValue("Main", "order");
        $visible = $this->config->getValue("Main", "visible");
        $aliases = $this->config->getValue("Main", "aliases");
        $j = -1;

        $data["name"] = $data_sem["name"];

        if ($visible[++$j])
            $data["subtitle"] = $data_sem["subtitle"];

        if ($visible[++$j]) {
            $data["lecturer"][] = sprintf("<a href=\"\"%s>%s</a>",
                    $this->config->getAttributes("LinkInternSimple", "a"),
                    $data_sem["lecturer"]);
            if (is_array($data["lecturer"]))
                $data["lecturer"] = implode(", ", $data["lecturer"]);
        }

        if ($visible[++$j])
            $data["art"] = $data_sem["art"];

        if ($visible[++$j]) {
            $aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
                    "class_{$SEM_TYPE[$data_sem['semtype']]['class']}");
            if ($aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1])
                $data["status"] = $aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1];
            else {
                $data["status"] = htmlReady($SEM_TYPE[$data_sem['semtype']]["name"]
                        ." (". $SEM_CLASS[$SEM_TYPE[$data_sem['semtype']]["class"]]["name"].")");
            }
        }

        if ($visible[++$j])
            $data["description"] = $data_sem["description"];

        if ($visible[++$j])
            $data["location"] = $data_sem["location"];

        if ($visible[++$j])
            $data["semester"] = $data_sem["semester"];

        if ($visible[++$j])
            $data["time"] = $data_sem["time"];

        if ($visible[++$j])
            $data["number"] = $data_sem["number"];

        if ($visible[++$j])
            $data["teilnehmer"] = $data_sem["teilnehmer"];

        if ($visible[++$j])
            $data["requirements"] = $data_sem["requirements"];

        if ($visible[++$j])
            $data["lernorga"] = $data_sem["lernorga"];

        if ($visible[++$j])
            $data["leistung"] = $data_sem["leistung"];

        if ($visible[++$j]) {
            $pathes = [$data_sem["range_path"]];
            if (is_array($pathes)) {
                $pathes_values = array_values($pathes);
                if ($this->config->getValue("Main", "range") == "long")
                    $data["range_path"] = $pathes_values;
                else {
                    foreach ($pathes_values as $path)
                        $data["range_path"][] = array_pop(explode("&gt;", $path));
                }
                $data["range_path"] = array_filter($data["range_path"], "htmlReady");
                $data["range_path"] = implode("<br>", $data["range_path"]);
            }
        }

        if ($visible[++$j])
            $data["misc"] = $data_sem["misc"];

        if ($visible[++$j])
            $data["ects"] = $data_sem["ects"];

        return $this->toStringMainTable($data, TRUE);
    }


    // private
    function toStringMainTable ($data, $preview) {
        $order = $this->config->getValue("Main", "order");
        $visible = $this->config->getValue("Main", "visible");
        $aliases = $this->config->getValue("Main", "aliases");

        if ($this->config->getValue("Main", "studiplink")) {
            $out .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"";
            if ($studiplink_width = $this->config->getValue("TableHeader", "table_width"))
                $out .= " width=\"$studiplink_width\"";
            if ($studiplink_align = $this->config->getValue("TableHeader", "table_align"))
                $out .= " align=\"$studiplink_align\">\n";

            if ($preview)
                $studip_link = "";
            else {
                if ($this->config->getValue("Main", "studiplinktarget") != "signin") {
                    $studip_link = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'seminar_main.php?auswahl=';
                    $studip_link .= $this->seminar_id;
                    $studip_link .= "&again=1&redirect_to=dispatch.php/course/basicdata/view/".$this->seminar_id."&login=true&new_sem=TRUE";
                }
                else {
                    $studip_link = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/course/details/?again=1&sem_id=';
                    $studip_link .= $this->seminar_id;
                }
            }
            if ($this->config->getValue("Main", "studiplink") == "top") {
                $args = ["width" => "100%", "height" => "40", "link" => $studip_link];
                $out .= "<tr><td width=\"100%\">\n";
                $out .= $this->elements["StudipLink"]->toString($args);
                $out .= "</td></tr>";
            }
            $table_attr = $this->config->getAttributes("TableHeader", "table");
            $pattern = ["/width=\"[0-9%]+\"/", "/align=\"[a-z]+\"/"];
            $replace = ["width=\"100%\"", ""];
            $table_attr = preg_replace($pattern, $replace, $table_attr);
            $out .= "<tr><td width=\"100%\">\n<table$table_attr>";
        }
        else
            $out .= "<table" . $this->config->getAttributes("TableHeader", "table") . ">";

        $out .= $this->elements["SemName"]->toString(["content" => $data["name"]]);

        if ($this->config->getValue("Main", "headlinerow")) {
            foreach ($order as $position) {
                if ($visible[$position] && $data[$this->data_fields[$position]]) {
                    $out .= $this->elements["Headline"]->toString(
                            ["content" => $aliases[$position]]);
                    $out .= $this->elements["Content"]->toString(
                            ["content" => $data[$this->data_fields[$position]]]);
                }
            }
        }
        else {
            foreach ($order as $position) {
                if ($visible[$position] && $data[$this->data_fields[$position]]) {
                    $out .= $this->elements["Content"]->toString(["content" =>
                            $this->config->getTag("Headline", "font") . $aliases[$position] .
                            "</font>" . $data[$this->data_fields[$position]]]);
                }
            }
        }

        if ($this->config->getValue("Main", "studipinfo")) {
            $out .= $this->elements["Headline"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "headline")]);
            $out .= $this->toStringStudipInfo($preview);
        }

        $out .= "</table>\n";

        if ($this->config->getValue("Main", "studiplink")) {
            if ($this->config->getValue("Main", "studiplink") == "bottom") {
                $args = ["width" => "100%", "height" => "40", "link" => $studip_link];
                $out .= "</td></tr>\n<tr><td width=\"100%\">\n";
                $out .= $this->elements["StudipLink"]->toString($args);
            }
            $out .= "</td></tr></table>\n";
        }

        return $out;
    }

    // private
    function toStringStudipInfo ($preview) {
        if ($preview) {
            $studip_info = $this->elements["StudipInfo"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "homeinst") . "&nbsp;"]);
            $studip_info .= sprintf("<a href=\"\"%s>%s</a><br>\n",
                    $this->config->getAttributes("LinkInternSimple", "a"),
                    _("Heimatinstitut"));

            $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "involvedinst") . "&nbsp;"]);
            $studip_info .= str_repeat(_("Beteiligte Institute") . " ", 5) . "<br>\n";

            $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "countuser") . "&nbsp;"]);
            $studip_info .= "23<br>\n";

            $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "countpostings") . "&nbsp;"]);
            $studip_info .= "42<br>\n";

            $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "countdocuments") . "&nbsp;"]);
            $studip_info .= "7<br>\n";
        }
        else {
            $query="SELECT i.Institut_id, i.Name, i.url FROM seminare LEFT JOIN Institute i
                                    USING(institut_id) WHERE Seminar_id = ?";
            $parameters = [$this->seminar_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $res = $statement->fetch(PDO::FETCH_ASSOC);
            $own_inst = $res['Institut_id'];

            $studip_info = $this->elements["StudipInfo"]->toString(["content" =>
                    $this->config->getValue("StudipInfo", "homeinst") . "&nbsp;"]);

            if ($res['url']) {
                $link_inst = htmlReady($res['url']);
                if (!preg_match('{^https?://.+$}', $link_inst))
                    $link_inst = "http://$link_inst";
                $studip_info .= sprintf(
                    "<a href=\"%s\"%s target=\"_blank\" rel=\"noopener noreferrer\">%s</a>",
                    $link_inst,
                    $this->config->getAttributes("LinkInternSimple", "a"),
                    htmlReady($res['Name'])
                );
            }else
                $studip_info .= htmlReady($res['Name']);
            $studip_info .= "<br>\n";


            $query = "SELECT Name, url FROM seminar_inst LEFT JOIN Institute i USING(institut_id)
                                    WHERE seminar_id = ? AND i.institut_id!='$own_inst'";
            $parameters = [$this->seminar_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $involved_insts = NULL;
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $res) {
                if ($res['url']) {
                    $link_inst = htmlReady($res['url']);
                    if (!preg_match('{^https?://.+$}', $link_inst))
                        $link_inst = "http://$link_inst";
                        $involved_insts[] = sprintf(
                            "<a href=\"%s\"%s target=\"_blank\" rel=\"noopener noreferrer\">%s</a>",
                            $link_inst,
                            $this->config->getAttributes("LinkInternSimple", "a"),
                            htmlReady($res['Name'])
                        );
                }
                else
                    $involved_insts[] = $res['Name'];
            }

            if ($involved_insts) {
                $involved_insts = implode(", ", $involved_insts);
                $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                        $this->config->getValue("StudipInfo", "involvedinst") . "&nbsp;"]);
                $studip_info .= $involved_insts . "<br>\n";
            }

            $query = "SELECT count(*) as count_user FROM seminar_user WHERE Seminar_id = ?";
            $parameters = [$this->seminar_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $res = $statement->fetch(PDO::FETCH_ASSOC);

            if ($res['count_user']) {
                $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                            $this->config->getValue("StudipInfo", "countuser") . "&nbsp;"]);
                $studip_info .= $res['count_user'] . "<br>\n";
            }


            // get postings for all ForumModules
            foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
                $postings += $plugin->getNumberOfPostingsForSeminar($this->seminar_id);
            }

            if ($postings) {
                $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                            $this->config->getValue("StudipInfo", "countpostings") . "&nbsp;"]);
                $studip_info .= $postings . "<br>\n";
            }

            $query = "SELECT COUNT(*) AS count_documents
                  FROM folders
                  INNER JOIN file_refs ON folder_id = folders.id
                  WHERE range_id = ? AND range_type = 'course'
            AND folder_type IN ('RootFolder', 'StandardFolder')
                  GROUP BY range_id";
            $parameters = [$this->seminar_id];
            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $res = $statement->fetch(PDO::FETCH_ASSOC);

            if ($res['count_documents']) {
                $studip_info .= $this->elements["StudipInfo"]->toString(["content" =>
                            $this->config->getValue("StudipInfo", "countdocuments") . "&nbsp;"]);
                $studip_info .= $res['count_documents'] . "\n";
            }
        }

        return $this->elements["Content"]->toString(["content" => $studip_info]);
    }

}

?>
