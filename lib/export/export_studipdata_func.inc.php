<?php
# Lifter002: TODO
# Lifter003: TEST (!)
# Lifter007: TODO
# Lifter010: TODO
/**
* Export-Subfile that exports data.
*
* This file contains functions to get data from the Stud.IP-db and write it into a file.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_studipdata_functions
* @package      Export
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_studipdata_func.inc.php
// exportfunctions for the Stud.IP database
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de>
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

require_once 'lib/statusgruppe.inc.php';

/**
* Writes the xml-stream into a file or to the screen.
*
* This function writes the xml-stream $object_data into a file or to the screen,
* depending on the content of $output_mode.
*
* @access   public
* @param        string  $object_data    xml-stream
* @param        string  $output_mode    switch for output target
*/
function output_data($object_data, $output_mode = "file", $flush = false)
{
    global $xml_file;
    static $fp;
    if (is_null($fp)) {
        $fp = fopen('php://temp', 'r+');
    }

    fwrite($fp, $object_data);

    if ($flush && is_resource($fp)) {
        rewind($fp);
        if (in_array($output_mode, words('file processor passthrough choose'))) {
            stream_copy_to_stream($fp, $xml_file);
        } elseif ($output_mode == "direct") {
            $out = fopen('php://output', 'w');
            stream_copy_to_stream($fp, $out);
            fclose($out);
        }
        fclose($fp);
    }
}

/**
* Exports data of the given range.
*
* This function calls the functions that export the data sepcified by the given $export_range.
* It calls the function output_data afterwards.
*
* @access   public
* @param        string  $range_id   Stud.IP-range_id for export
*/
function export_range($range_id)
{
    global $o_mode, $range_name,$ex_person_details,$persons, $ex_sem;

    // Ist die Range-ID eine Einrichtungs-ID?
    $query = "SELECT Name FROM Institute WHERE Institut_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$range_id]);
    $name = $statement->fetchColumn();
    if ($name) {
        $range_name = $name;
        output_data ( xml_header(), $o_mode);
        $output_startet = true;
        export_inst( $range_id );

    }

    // Ist die Range-ID eine Fakultaets-ID? Dann auch untergeordnete Institute exportieren!
    $query = "SELECT Name, Institut_id
              FROM Institute
              WHERE fakultaets_id = ? AND Institut_id != fakultaets_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$range_id]);
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Name'] != '') {
            // output_data ( xml_header(), $o_mode);
            export_inst($row['Institut_id']);
        }
    }

    // Ist die Range-ID eine Seminar-ID?
    $query = "SELECT Name, Seminar_id, Institut_id
              FROM seminare
              WHERE Seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$range_id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['Name'] != '') {
        $range_name = $row['Name'];
        if (!$output_startet) {
            output_data(xml_header(), $o_mode);
            $output_startet = true;
        }
        export_inst($row['Institut_id'], $row['Seminar_id']);
    }


    //  Ist die Range-ID ein Range-Tree-Item?
    if ($range_id != 'root') {
        $tree_object = new RangeTreeObject($range_id);
        $range_name = $tree_object->item_data["name"];

        // Tree-Item ist ein Institut:
        if ($tree_object->item_data['studip_object'] == 'inst') {
            if (!$output_startet) {
                output_data(xml_header(), $o_mode);
                $output_startet = true;
            }
            export_inst( $tree_object->item_data['studip_object_id'] );
        }

        // Tree-Item hat Institute als Kinder:
        $inst_array = $tree_object->GetInstKids();

        if (count($inst_array) > 0) {
            if (!$output_startet) {
                output_data(xml_header(), $o_mode);
                $output_startet = true;
            }
            while (list($key, $inst_ids) = each($inst_array)) {
                export_inst($inst_ids);
            }
        }
    }

    $query = "SELECT 1 FROM sem_tree WHERE sem_tree_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$range_id]);
    if ($statement->fetchColumn() || $range_id == 'root') {
        if (!$output_startet)  {
            output_data(xml_header(), $o_mode);
            $output_startet = true;
        }
        if (isset($ex_sem) && $semester = Semester::find($ex_sem)) {
            $args = ['sem_number' => [SemesterData::GetSemesterIndexById($ex_sem)]];
        } else {
            $args = [];
        }
        if ($range_id != 'root') {
            $the_tree = TreeAbstract::GetInstance('StudipSemTree', $args);
            $sem_ids = array_unique($the_tree->getSemIds($range_id, true));
        }
        if (is_array($sem_ids) || $range_id == 'root') {
            if (is_array($sem_ids)) {
                $query = "SELECT DISTINCT Institut_id
                          FROM seminare
                          WHERE Seminar_id IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$sem_ids]);
                $to_export = $statement->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $sem_ids = 'root';

                $query = "SELECT DISTINCT Institut_id
                          FROM seminare
                          INNER JOIN seminar_sem_tree USING (seminar_id)";
                if ($semester) {
                    $query .= " WHERE seminare.start_time <= :begin
                                  AND (:begin <= (seminare.start_time + seminare.duration_time) OR
                                       seminare.duration_time = -1)";
                    $statement = DBManager::get()->prepare($query);
                    $statement->bindValue(':begin', $semester->beginn);
                    $statement->execute();
                } else {
                    $statement = DBManager::get()->query($query);
                }

                $to_export = $statement->fetchAll(PDO::FETCH_COLUMN);
            }

            foreach($to_export as $inst) {
                export_inst($inst, $sem_ids);
            }
        }
    }

    if ($ex_person_details && is_array($persons)){
        export_persons(array_keys($persons));
    }
    output_data ( xml_footer(), $o_mode, $flush = true);
}


/**
* Exports a Stud.IP-institute.
*
* This function gets the data of an institute and writes it into $data_object.
* It calls one of the functions export_sem, export_pers or export_teilis and then output_data.
*
* @access   public
* @param        string  $inst_id    Stud.IP-inst_id for export
* @param        string  $ex_sem_id  allows to choose if only a specific lecture is to be exported
*/
function export_inst($inst_id, $ex_sem_id = "all")
{
    global $ex_type, $o_mode, $xml_file, $xml_names_inst, $xml_groupnames_inst, $INST_TYPE;

    $query = "SELECT * FROM Institute WHERE Institut_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$inst_id]);
    $institute = $statement->fetch(PDO::FETCH_ASSOC);

    $data_object .= xml_open_tag($xml_groupnames_inst["object"], $institute['Institut_id']);
    while (list($key, $val) = each($xml_names_inst)) {
        if ($val == '') {
            $val = $key;
        }
        if ($key == 'type' && $INST_TYPE[$institute[$key]]['name'] != '') {
            $data_object .= xml_tag($val, $INST_TYPE[$institute[$key]]['name']);
        } elseif ($institute[$key] != '') {
            $data_object .= xml_tag($val, $institute[$key]);
        }
    }
    reset($xml_names_inst);

    $query = "SELECT Name, Institut_id, type
              FROM Institute
              WHERE Institut_id = ? AND fakultaets_id = Institut_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$institute['fakultaets_id']]);
    $faculty = $statement->fetch(PDO::FETCH_ASSOC);
    if ($faculty['Name'] != '') {
        $data_object .= xml_tag($xml_groupnames_inst["childobject"], $faculty['Name'], ['key' => $faculty['Institut_id']]);
    }

    // freie Datenfelder ausgeben
    $data_object .= export_datafields($inst_id, $xml_groupnames_inst["childgroup2"], $xml_groupnames_inst["childobject2"], 'inst', $faculty['type']);
    output_data( $data_object, $o_mode );
    $data_object = "";

    switch ($ex_type)
    {
    case "veranstaltung":
        export_sem($inst_id, $ex_sem_id);
        break;
    case "person":
        if ($ex_sem_id == "all")
            export_pers($inst_id);
        elseif ($GLOBALS['perm']->have_studip_perm('tutor', $ex_sem_id))
            export_teilis($inst_id, $ex_sem_id);
        else
            $data_object .= xml_tag("message", _("KEINE BERECHTIGUNG!"));
        break;
    default:
        echo "</td></tr>";
        echo '<tr><td>';
        echo MessageBox::error(_('Der gewählte Exportmodus wird nicht unterstützt.'));
        echo '</td></tr>';
        echo "</table></td></tr></table>";
        die();
    }

    $data_object .= xml_close_tag($xml_groupnames_inst["object"]);

    output_data($data_object, $o_mode);
    $data_object = "";
}

/**
* Exports lecture-data.
*
* This function gets the data of the lectures at an institute and writes it into $data_object.
* It calls output_data afterwards.
*
* @access   public
* @param        string  $inst_id    Stud.IP-inst_id for export
* @param        string  $ex_sem_id  allows to choose if only a specific lecture is to be exported
*/
function export_sem($inst_id, $ex_sem_id = 'all')
{
    global $range_id, $xml_file, $o_mode, $xml_names_lecture, $xml_groupnames_lecture, $object_counter, $SEM_TYPE, $SEM_CLASS, $filter, $ex_sem, $ex_sem_class,$ex_person_details,$persons;

    $ex_only_homeinst = Request::int('ex_only_homeinst', 0);

    // Prepare user count statement
    $query = "SELECT COUNT(user_id)
              FROM seminar_user
              WHERE seminar_id = ? AND status = 'autor'";
    $count_statement = DBManager::get()->prepare($query);

    // Prepare inner statement
    $query = "SELECT seminar_user.position,
                     auth_user_md5.user_id, auth_user_md5.username, auth_user_md5.Vorname, auth_user_md5.Nachname,
                     user_info.title_front, user_info.title_rear
              FROM seminar_user
              LEFT JOIN user_info USING (user_id)
              LEFT JOIN auth_user_md5 USING (user_id)
              WHERE seminar_user.status = 'dozent' AND seminar_user.Seminar_id = ?
              ORDER BY seminar_user.position";
    $inner_statement = DBManager::get()->prepare($query);

    // Prepare (build) and execute outmost query
    switch ($filter)
    {
        case "seminar":
            $order = " seminare.Name";
        break;
        case "status":
            $order = "seminare.status, seminare.Name";
            $group = "FIRSTGROUP";
            $group_tab_zelle = "status";
            $do_group = true;
        break;
        default:
            $order = "seminare.status, seminare.Name";
            $group = "FIRSTGROUP";
            $group_tab_zelle = "status";
            $do_group = true;
    }

    $parameters = [];

    if (isset($ex_sem) && $semester = Semester::find($ex_sem)) {
        $addquery = " AND seminare.start_time <= :begin AND (:begin <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
        $parameters[':begin'] = $semester->beginn;
    }

    if ($ex_sem_id != 'all'){
        if ($ex_sem_id == 'root') {
            $addquery .= " AND EXISTS (SELECT * FROM seminar_sem_tree WHERE seminar_sem_tree.seminar_id = seminare.Seminar_id) ";
        } else {
            if (!is_array($ex_sem_id)) $ex_sem_id = [$ex_sem_id];
            $ex_sem_id = array_flip($ex_sem_id);
        }
    }

    if (!$GLOBALS['perm']->have_perm('root') && !$GLOBALS['perm']->have_studip_perm('admin', $inst_id)) {
        $addquery .= " AND visible = 1 ";
    }

    if (count($ex_sem_class) > 0) {
        $allowed_sem_types = [];
        foreach(array_keys($ex_sem_class) as $semclassid) {
            $allowed_sem_types = array_merge($allowed_sem_types, array_keys(SeminarCategories::get($semclassid)->getTypes()));
        }
        $addquery .= " AND seminare.status IN (:status) ";
        $parameters[':status'] = $allowed_sem_types;
    } else {
        $addquery .= " AND seminare.status NOT IN (:status) ";
        $parameters[':status'] = studygroup_sem_types() ?: '';
    }

    if ($ex_only_homeinst) {
        $query = "SELECT seminare.*,Seminar_id as seminar_id, Institute.Name AS heimateinrichtung
                  FROM seminare
                  LEFT JOIN Institute USING (Institut_id)
                  WHERE Institut_id = :institute_id {$addquery}
                  ORDER BY " . $order;
        $parameters[':institute_id'] = $inst_id;
    } else {
        $query = "SELECT seminare.*,Seminar_id as seminar_id, Institute.Name AS heimateinrichtung
                  FROM seminar_inst
                  LEFT JOIN seminare USING (Seminar_id)
                  LEFT JOIN Institute ON seminare.Institut_id = Institute.Institut_id
                  WHERE seminar_inst.Institut_id = :institute_id {$addquery}
                  ORDER BY " . $order;
        $parameters[':institute_id'] = $inst_id;
    }
    $statement = DBManager::get()->prepare($query);
    $statement->execute($parameters);
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $data_object .= xml_open_tag( $xml_groupnames_lecture['group'] );

    foreach ($data as $row) {
        if (is_array($ex_sem_id) && !isset($ex_sem_id[$row['seminar_id']])) {
            continue;
        }
        $group_string = '';
        if ($do_group && $group != $row[$group_tab_zelle]) {
            if ($group != 'FIRSTGROUP') {
                $group_string .= xml_close_tag($xml_groupnames_lecture['subgroup1']);
            }
            if ($group_tab_zelle == 'status') {
                $group_string .= xml_open_tag($xml_groupnames_lecture['subgroup1'], $SEM_TYPE[$row[$group_tab_zelle]]['name']);
            } else {
                $group_string .= xml_open_tag($xml_groupnames_lecture['subgroup1'], $row[$group_tab_zelle]);
            }
            $group = $row[$group_tab_zelle];
            if ($do_subgroup && $subgroup == $row[$subgroup_tab_zelle]) {
                $subgroup = "NEXTGROUP";
            }
        }
        if ($do_subgroup && $subgroup != $row[$subgroup_tab_zelle]) {
            if ($subgroup != 'FIRSTGROUP') {
                $group_string = xml_close_tag($xml_groupnames_lecture['subgroup2']) . $group_string;
            }
            $group_string .= xml_open_tag($xml_groupnames_lecture['subgroup2'], $row[$subgroup_tab_zelle]);
            $subgroup = $row[$subgroup_tab_zelle];
        }
        $data_object .= $group_string;
        $object_counter += 1;
        $data_object .= xml_open_tag($xml_groupnames_lecture['object'], $row['seminar_id']);
        $sem_obj = new Seminar($row['seminar_id']);
        while ( list($key, $val) = each($xml_names_lecture)) {
            if (!$val) {
                $val = $key;
            }
            if ($key === 'status') {
                $data_object .= xml_tag($val, $SEM_TYPE[$row[$key]]['name']);
            } elseif ($key === 'ort') {
                $data_object .= xml_tag($val, $sem_obj->getDatesTemplate('dates/seminar_export_location'));
            } elseif ($key === 'bereich' && $SEM_CLASS[$SEM_TYPE[$row['status']]['class']]['bereiche']) {
                $data_object .= xml_open_tag($xml_groupnames_lecture['childgroup3']);
                $pathes = get_sem_tree_path($row['seminar_id']);
                if (is_array($pathes)) {
                    foreach ($pathes as $path_name) {
                        $data_object .= xml_tag($val, $path_name);
                    }
                } else {
                    $data_object .= xml_tag($val, 'n.a.');
                }
                $data_object .= xml_close_tag($xml_groupnames_lecture['childgroup3']);
            } elseif ($key === 'lvgruppe' && $SEM_CLASS[$SEM_TYPE[$row['status']]['class']]['module']) {
                $data_object .= xml_open_tag($xml_groupnames_lecture['childgroup3a']);
                ModuleManagementModelTreeItem::setObjectFilter('Modul', function ($modul) use ($sem_obj) {
                    // check for public status
                    if (!$GLOBALS['MVV_MODUL']['STATUS']['values'][$modul->stat]['public']) {
                        return false;
                    }
                    $modul_start = Semester::find($modul->start)->beginn ?: 0;
                    $modul_end = Semester::find($modul->end)->beginn ?: PHP_INT_MAX;
                    return $sem_obj->start_time <= $modul_end &&
                           ($modul_start <= $sem_obj->start_time + $sem_obj->duration_time || $sem_obj->duration_time == -1);
                });
                ModuleManagementModelTreeItem::setObjectFilter('StgteilVersion', function ($version) {
                    return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                });
                $trail_classes = ['Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion'];
                $mvv_object_paths = MvvCourse::get($sem_obj->id)->getTrails($trail_classes);
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
                    $data_object .= xml_tag($val, $mvv_path);
                }
                $data_object .= xml_close_tag($xml_groupnames_lecture['childgroup3a']);
            } elseif ($key === 'admission_turnout') {
                    $data_object .= xml_open_tag($val, $row['admission_type'] ? _('max.') : _('erw.'));
                    $data_object .= $row[$key];
                    $data_object .= xml_close_tag($val);
            } elseif ($key === 'teilnehmer_anzahl_aktuell') {
                $count_statement->execute([$row['seminar_id']]);
                $count = $count_statement->fetchColumn();
                $count_statement->closeCursor();

                $data_object .= xml_tag($val, $count);
            } elseif ($key === 'metadata_dates') {
                $data_object .= xml_open_tag( $xml_groupnames_lecture['childgroup1'] );
                $vorb = vorbesprechung($row['seminar_id'], 'export');
                if ($vorb != false) {
                    $data_object .= xml_tag($val[0], $vorb);
                }
                if (($first_date = SeminarDB::getFirstDate($row['seminar_id']))
                    && count($first_date))
                {
                    $really_first_date = new SingleDate($first_date[0]);
                    $data_object .= xml_tag($val[1], $really_first_date->getDatesExport());
                }
                $data_object .= xml_tag($val[2], $sem_obj->getDatesExport());
                $data_object .= xml_close_tag( $xml_groupnames_lecture["childgroup1"] );
            } elseif ($key === 'Institut_id') {
                $data_object .= xml_tag($val, $row['heimateinrichtung'] , ['key' => $row[$key]]);
            } elseif ($row[$key] !== '')
                $data_object .= xml_tag($val, $row[$key]);
        }

        $data_object .= "<" . $xml_groupnames_lecture['childgroup2'] . ">\n";

        $inner_statement->execute([$row['seminar_id']]);
        while ($inner = $inner_statement->fetch(PDO::FETCH_ASSOC)) {
            if ($ex_person_details) {
                $persons[$inner['user_id']] = true;
            }
            $content_string = $inner['Vorname'] . ' ' . $inner['Nachname'];
            if ($inner['title_front'] != '') {
                $content_string = $inner['title_front'] . ' ' . $content_string;
            }
            if ($inner['title_rear'] != '') {
                $content_string .= ', ' . $inner['title_rear'];
            }
            $data_object .= xml_tag($xml_groupnames_lecture['childobject2'], $content_string, ['key' => $inner['username']]);
        }

        $data_object .= xml_close_tag($xml_groupnames_lecture['childgroup2']);
    // freie Datenfelder ausgeben
        $data_object .= export_datafields($row['seminar_id'], $xml_groupnames_lecture['childgroup4'], $xml_groupnames_lecture['childobject4'], 'sem', $row['status']);
        $data_object .= xml_close_tag($xml_groupnames_lecture['object']);
        reset($xml_names_lecture);
        output_data($data_object, $o_mode);
        $data_object = '';
    }

    if ($do_subgroup && $subgroup != 'FIRSTGROUP') {
        $data_object .= xml_close_tag($xml_groupnames_lecture['subgroup2']);
    }
    if ($do_group && $group != 'FIRSTGROUP') {
        $data_object .= xml_close_tag($xml_groupnames_lecture['subgroup1']);
    }

    $data_object .= xml_close_tag($xml_groupnames_lecture['group']);
    output_data($data_object, $o_mode);
}


/**
* Exports member-list for a Stud.IP-lecture.
*
* This function gets the data of the members of a lecture and writes it into $data_object.
* It calls output_data afterwards.
*
* @access   public
* @param        string  $inst_id    Stud.IP-inst_id for export
* @param        string  $ex_sem_id  allows to choose which lecture is to be exported
*/
function export_teilis($inst_id, $ex_sem_id = "no")
{
    global $range_id, $xml_file, $o_mode, $xml_names_person, $xml_groupnames_person, $xml_names_studiengaenge, $xml_groupnames_studiengaenge, $object_counter, $filter, $SEM_CLASS, $SEM_TYPE;

    if ($filter == 'status') {
        $query = "SELECT statusgruppe_id, name
                  FROM statusgruppen
                  WHERE range_id = ?
                  ORDER BY position ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$ex_sem_id]);
        $gruppe = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        $gruppe['no'] = _('keiner Funktion oder Gruppe zugeordnet');
    } else {

        if (!in_array($filter, words('awaiting claiming'))) {
            $gruppe = [
                'dozent'   => get_title_for_status('dozent', 2),
                'tutor'    => get_title_for_status('tutor', 2),
                'autor'    => get_title_for_status('autor', 2),
                'user'     => get_title_for_status('user', 2),
                'accepted' => get_title_for_status('accepted', 2)
            ];
        } else {
            $gruppe[$filter] = _('Anmeldeliste');
        }
    }

    $data_object .= xml_open_tag( $xml_groupnames_person['group'] );

    while (list($key1, $val1) = each($gruppe)) {
        $parameters = [];
        if ($filter == 'status') {
            // Gruppierung nach Statusgruppen / Funktionen
            if ($key1 == 'no') {
                $query = "SELECT ui.*, aum.*, su.*, FROM_UNIXTIME(su.mkdate) AS registration_date,
                                 GROUP_CONCAT(CONCAT_WS(',', sg.name, a.name, user_studiengang.semester) SEPARATOR '; ') AS nutzer_studiengaenge
                          FROM seminar_user AS su
                          LEFT JOIN auth_user_md5 AS aum USING (user_id)
                          LEFT JOIN user_info AS ui USING (user_id)
                          LEFT JOIN user_studiengang USING (user_id)
                          LEFT JOIN fach AS sg USING(fach_id)
                          LEFT JOIN abschluss AS a USING (abschluss_id)
                          WHERE seminar_id = :seminar_id
                          GROUP BY aum.user_id
                          ORDER BY Nachname, Vorname";
                $parameters[':seminar_id'] = $ex_sem_id;
            } else {
                $query = "SELECT DISTINCT ui.*, aum.*, su.*, FROM_UNIXTIME(su.mkdate) AS registration_date,
                                 GROUP_CONCAT(CONCAT_WS(',', sg.name, a.name, user_studiengang.semester) SEPARATOR '; ') AS nutzer_studiengaenge
                          FROM statusgruppe_user
                          LEFT JOIN seminar_user AS su USING (user_id)
                          LEFT JOIN auth_user_md5 AS aum USING (user_id)
                          LEFT JOIN user_info AS ui USING (user_id)
                          LEFT JOIN user_studiengang USING(user_id)
                          LEFT JOIN fach AS sg USING(fach_id)
                          LEFT JOIN abschluss AS a USING (abschluss_id)
                          WHERE statusgruppe_id = :statusgruppe_id AND seminar_id = :seminar_id
                          GROUP BY aum.user_id
                          ORDER BY Nachname, Vorname";
                $parameters[':seminar_id']      = $ex_sem_id;
                $parameters[':statusgruppe_id'] = $key1;
            }
        } // Gruppierung nach Status in der Veranstaltung / Einrichtung
          else if ($key1 == 'accepted') {
            $query = "SELECT ui.*, aum.*, asu.comment,
                             FROM_UNIXTIME(asu.mkdate) AS registration_date,
                             GROUP_CONCAT(CONCAT_WS(',', sg.name, a.name, user_studiengang.semester) SEPARATOR '; ') AS nutzer_studiengaenge
                      FROM admission_seminar_user AS asu
                      LEFT JOIN user_info AS ui USING (user_id)
                      LEFT JOIN auth_user_md5 AS aum USING (user_id)
                      LEFT JOIN user_studiengang USING (user_id)
                      LEFT JOIN fach AS sg ON (user_studiengang.fach_id = sg.fach_id)
                      LEFT JOIN abschluss AS a USING (abschluss_id)
                      WHERE seminar_id = :seminar_id AND asu.status = 'accepted'
                      GROUP BY aum.user_id
                      ORDER BY Nachname, Vorname";
            $parameters[':seminar_id'] = $ex_sem_id;
        } elseif ($key1 == 'awaiting') {
            $query = "SELECT ui.*, aum.*, asu.comment,
                             asu.position AS admission_position,
                             GROUP_CONCAT(CONCAT_WS(',', sg.name, a.name, user_studiengang.semester) SEPARATOR '; ') AS nutzer_studiengaenge
                        FROM admission_seminar_user AS asu
                        LEFT JOIN user_info AS ui USING(user_id)
                        LEFT JOIN auth_user_md5 AS aum USING(user_id)
                        LEFT JOIN user_studiengang USING(user_id)
                        LEFT JOIN fach AS sg ON (user_studiengang.fach_id = sg.fach_id)
                        LEFT JOIN abschluss AS a USING (abschluss_id)
                        WHERE asu.seminar_id = :seminar_id AND asu.status != 'accepted'
                        GROUP BY aum.user_id ORDER BY position";
            $parameters[':seminar_id'] = $ex_sem_id;
        } elseif ($key1 == 'claiming') {
            $cs = CourseSet::getSetForCourse($ex_sem_id);
            if (is_object($cs) && !$cs->hasAlgorithmRun()) {
                $parameters[':users'] = array_keys(AdmissionPriority::getPrioritiesByCourse($cs->getId(), $ex_sem_id));
            } else {
                $parameters[':users'] = [];
            }
            $query = "SELECT ui.*, aum.*, '' as comment,
                             0  AS admission_position,
                             GROUP_CONCAT(CONCAT_WS(',', sg.name, a.name, user_studiengang.semester) SEPARATOR '; ') AS nutzer_studiengaenge
                        FROM auth_user_md5 AS aum
                        INNER JOIN user_info AS ui USING(user_id)
                        LEFT JOIN user_studiengang USING(user_id)
                        LEFT JOIN fach AS sg ON (user_studiengang.fach_id = sg.fach_id)
                        LEFT JOIN abschluss AS a USING (abschluss_id)
                        WHERE aum.user_id IN (:users)
                        GROUP BY aum.user_id ORDER BY Nachname, Vorname";
        } else {
            $query = "SELECT ui.*, aum.*, su.*, FROM_UNIXTIME(su.mkdate) AS registration_date,
                             GROUP_CONCAT(CONCAT_WS(',', sg.name, a.name, us.semester) SEPARATOR '; ') AS nutzer_studiengaenge
                      FROM seminar_user AS su
                      LEFT JOIN auth_user_md5 AS aum USING ( user_id )
                      LEFT JOIN user_info AS ui USING ( user_id )
                      LEFT JOIN user_studiengang AS us USING(user_id)
                      LEFT JOIN fach AS sg USING (fach_id)
                      LEFT JOIN abschluss AS a USING (abschluss_id)
                      WHERE seminar_id = :seminar_id AND su.status = :status
                      GROUP BY aum.user_id
                      ORDER BY " . ($key1 === 'dozent' ? 'position, ' : '') . "Nachname, Vorname";
            $parameters[':seminar_id'] = $ex_sem_id;
            $parameters[':status']     = $key1;
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $course = Course::find($ex_sem_id);
        if ($course->aux) {
            $zusatzangaben = array_keys($course->aux->datafields);
        } else {
            $zusatzangaben = [];
        }
        $data_object_tmp = '';
        $object_counter_tmp = $object_counter;
        if (count($data) > 0) {
            $data_object_tmp .= xml_open_tag($xml_groupnames_person['subgroup1'], $val1);
            foreach ($data as $row) {
                // Nur Personen ausgeben, die entweder einer Gruppe angehoeren
                // oder zur Veranstaltung gehoeren und noch nicht ausgegeben wurden.
                if ($key1 != 'no' || $person_out[$row['user_id']] != true) {
                    $object_counter += 1;
                    $data_object_tmp .= xml_open_tag($xml_groupnames_person["object"], $row['username']);

                    reset($xml_names_person);
                    while (list($key, $val) = each($xml_names_person)) {
                        if ($val == '') {
                            $val = $key;
                        }
                        if ($row[$key] != '') {
                            $data_object_tmp .= xml_tag($val, $row[$key]);
                        }
                    }
                    // freie Datenfelder ausgeben
                    $data_object_tmp .= export_datafields($row['user_id'], $xml_groupnames_person['childgroup1'], $xml_groupnames_person['childobject1'], 'user');
                    $data_object_tmp .= export_datafields([$row['user_id'], $ex_sem_id] , 'zusatzangaben', 'zusatzangabe', 'usersemdata', null, $zusatzangaben);
                    $data_object_tmp .= xml_close_tag( $xml_groupnames_person['object'] );
                    $person_out[$row['user_id']] = true;
                }
            }
            $data_object_tmp .= xml_close_tag($xml_groupnames_person['subgroup1']);
            if ($object_counter_tmp != $object_counter) {
                $data_object .= $data_object_tmp;
            }
        }
    }

    $data_object .= xml_close_tag($xml_groupnames_person['group']);

    if (!in_array($filter, words('status awaiting accepted'))) {
        $query = "SELECT CONCAT_WS(',', fach.name, abschluss.name) AS name, COUNT(*) AS c
                  FROM seminar_user
                  INNER JOIN user_studiengang USING (user_id)
                  LEFT JOIN fach USING (fach_id)
                  LEFT JOIN abschluss USING (abschluss_id)
                  WHERE seminar_id = ?
                  GROUP BY name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$ex_sem_id]);
        $studiengang_count = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        if (count($studiengang_count) > 0) {
            $data_object .= xml_open_tag($xml_groupnames_studiengaenge["group"]);
            for ($i = 0; $i < count($studiengang_count); $i += 1) { // TODO: Is this really neccessary?
                while (list ($key, $val) = each ($studiengang_count))
                {
                    $data_object .= xml_open_tag($xml_groupnames_studiengaenge['object']);
                    $data_object .= xml_tag($xml_names_studiengaenge['name'], $key);
                    $data_object .= xml_tag($xml_names_studiengaenge['count'], $val);
                    $data_object .= xml_close_tag($xml_groupnames_studiengaenge['object']);
                }
            }
            $data_object .= xml_close_tag($xml_groupnames_studiengaenge['group']);
        }
    }

    output_data($data_object, $o_mode);
}

/**
* Exports member-list for a Stud.IP-institute.
*
* This function gets the data of the members of an institute and writes it into $data_object.
* The order of the members depends on the grouping-option $filter.
* It calls output_data afterwards.
*
* @access   public
* @param        string  $inst_id    Stud.IP-inst_id for export
* @param        string  $ex_sem_id  allows to choose which lecture is to be exported
*/
function export_pers($inst_id)
{
    global $range_id, $xml_file, $o_mode, $xml_names_person, $xml_groupnames_person, $object_counter, $filter;

    $group           = 'FIRSTGROUP';
    $group_tab_zelle = 'name';
    $do_group        = true;

    // fetch all statusgroups and their hierarchical structure
    $roles = GetRoleNames(GetAllStatusgruppen($inst_id), 0, '', true) ?: [];

    // traverse and join statusgroups with memberdates
    $rows = [];
    foreach ($roles as $group_id => $data) {
        $query = "SELECT statusgruppen.name, statusgruppen.statusgruppe_id, aum.user_id,
                         aum.Nachname, aum.Vorname, ui.inst_perms, ui.raum,
                         ui.sprechzeiten, ui.Telefon, ui.Fax, aum.Email,
                         aum.username, info.Home, info.geschlecht, info.title_front, info.title_rear
                  FROM statusgruppen
                  JOIN statusgruppe_user sgu USING(statusgruppe_id)
                  JOIN user_inst ui ON (ui.user_id = sgu.user_id AND ui.Institut_id = ? AND ui.inst_perms!='user')
                  JOIN auth_user_md5 aum ON (ui.user_id = aum.user_id)
                  LEFT JOIN user_info info ON (ui.user_id = info.user_id)
                  WHERE statusgruppe_id = ?
                  ORDER BY sgu.position";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$inst_id, $group_id]);
        $rows = array_merge($rows, $statement->fetchAll(PDO::FETCH_ASSOC));
    }
    // create xml-output
    $data_object = xml_open_tag($xml_groupnames_person['group']);
    foreach ($rows as $row) {
        $data_found = true;
        $group_string = '';
        if ($do_group && $group != $row[$group_tab_zelle]) {
            if ($group != 'FIRSTGROUP') {
                $group_string .= xml_close_tag($xml_groupnames_person['subgroup1']);
            }
            $group_string .= xml_open_tag($xml_groupnames_person['subgroup1'], $roles[$row['statusgruppe_id']]);
            $group = $row[$group_tab_zelle];
        }
        $data_object .= $group_string;
        $object_counter += 1;
        $data_object .= xml_open_tag($xml_groupnames_person["object"], $row['username']);
        while (list($key, $val) = each($xml_names_person)) {
            if ($val == '') {
                $val = $key;
            }
            if (mb_strtolower($key) == 'email') {
                $row[$key] = get_visible_email($row['user_id']);
            }
            if ($row[$key] != '') {
                $data_object .= xml_tag($val, $row[$key]);
            }
        }
    // freie Datenfelder ausgeben
        $data_object .= export_datafields($row['user_id'], $xml_groupnames_person['childgroup1'], $xml_groupnames_person['childobject1'], 'user');
        $data_object .= xml_close_tag( $xml_groupnames_person['object'] );
        reset($xml_names_person);
        output_data($data_object, $o_mode);
        $data_object = '';
    }

    if ($do_group && $data_found) {
        $data_object .= xml_close_tag($xml_groupnames_person['subgroup1']);
    }

    $data_object .= xml_close_tag( $xml_groupnames_person['group']);

    if ($data_found) {
        output_data($data_object, $o_mode);
    }
}

/**
* Exports list of persons.
*
*
* @access   public
* @param        array   $persons    Stud.IP-user_ids for export
*/
function export_persons($persons)
{
    global $xml_names_person, $xml_groupnames_person, $object_counter, $o_mode, $ex_person_details;

    if (!is_array($persons) or count($persons) == 0) {
        return;
    }

    $query = "SELECT *
              FROM auth_user_md5
              LEFT JOIN user_info USING (user_id)
              WHERE user_id IN (?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$persons]);
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $object_counter += 1;

        $data_object = xml_open_tag($xml_groupnames_person['object'], $row['username']);
        if ($ex_person_details) {
            $data_object .= xml_tag('id', $row['user_id']);
        }
        while (list($key, $val) = each($xml_names_person)) {
            if ($val == '') {
                $val = $key;
            }
            if ($row[$key] != '') {
                $data_object .= xml_tag($val, $row[$key]);
            }
        }
        // freie Datenfelder ausgeben
        $data_object .= export_datafields($row['user_id'], $xml_groupnames_person['childgroup1'], $xml_groupnames_person['childobject1'], 'user');
        $data_object .= xml_close_tag($xml_groupnames_person['object']);
        reset($xml_names_person);
        output_data($data_object, $o_mode);
        $data_object = '';
    }
}

/**
* helper function to export custom datafields
*
* only visible datafields are exported (depending on user perms)
* @access   public
* @param    string  $range_id   id for object to export
* @param    string  $childgroup_tag name of outer tag
* @param    string  $childobject_tag    name of inner tags
*/
function export_datafields($range_id, $childgroup_tag, $childobject_tag, $object_type = null, $object_class_hint = null, $only_these = null){
    $ret = '';
    $d_fields = false;
    $localEntries = DataFieldEntry::getDataFieldEntries($range_id, $object_type, $object_class_hint);
    if(is_array($localEntries)){
        foreach ($localEntries as $entry){
            if (is_array($only_these)) {
                if (!in_array($entry->getId(), $only_these)) {
                    continue;
                }
            }
            if ($entry->isVisible(null, false) && ($entry->getDisplayValue() || $only_these)) {
                if (!$d_fields) $ret .= xml_open_tag( $childgroup_tag );
                $ret .= xml_open_tag($childobject_tag , $entry->getName());
                $ret .= xml_escape($entry->getDisplayValue(false));
                $ret .= xml_close_tag($childobject_tag);
                $d_fields = true;
            }
        }
    }
    if ($d_fields) $ret .= xml_close_tag( $childgroup_tag );
    return $ret;
}
