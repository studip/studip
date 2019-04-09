<?php
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE - no forms

/*
dates.inc.php - basale Routinen zur Terminveraltung.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, André Noack <anoack@mcis.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require_once 'lib/calendar_functions.inc.php';
require_once 'lib/raumzeit/raumzeit_functions.inc.php'; // Helper-Funktionen

/*
 * getWeekday liefert einen String mit einem Tagesnamen.
 *
 * day_num  integer PHP-konformer Tag (0-6)
 * short    boolean Wenn gesetzt wird der Tag verkürzt zurückgegeben.
 */
function getWeekday($day_num, $short = TRUE) {
    switch ($day_num) {
        case 0:
            $day = $short ? _("So") : _("Sonntag");
            break;
        case 1:
            $day = $short ? _("Mo") : _("Montag");
            break;
        case 2:
            $day = $short ? _("Di") : _("Dienstag");
            break;
        case 3:
            $day = $short ? _("Mi") : _("Mittwoch");
            break;
        case 4:
            $day = $short ? _("Do") : _("Donnerstag");
            break;
        case 5:
            $day = $short ? _("Fr") : _("Freitag");
            break;
        case 6:
            $day = $short ? _("Sa") : _("Samstag");
            break;
    }

    // return i18n of day
    return $day;
}

/**
 * getMonthName returns the localized name of a certain month in
 * either the abbreviated form (default) or as the actual name.
 *
 * @param int  $month Month number
 * @param bool $short Display the abbreviated version or the actual
 *                    name of the month (defaults to abbreviated)
 * @return String Month name
 * @throws Exception when passed an invalid month number
 */
function getMonthName($month, $short = true) {
    $month = (int)$month;

    $months = [
         1 => [_('Januar'),    _('Jan.')],
         2 => [_('Februar'),   _('Feb.')],
         3 => [_('März'),      _('März')],
         4 => [_('April'),     _('Apr.')],
         5 => [_('Mai'),       _('Mai')],
         6 => [_('Juni'),      _('Juni')],
         7 => [_('Juli'),      _('Juli')],
         8 => [_('August'),    _('Aug.')],
         9 => [_('September'), _('Sep.')],
        10 => [_('Oktober'),   _('Okt.')],
        11 => [_('November'),  _('Nov.')],
        12 => [_('Dezember'),  _('Dez.')],
    ];
    if (!isset($months[$month])) {
        throw new Exception("Invalid month '{$month}'");
    }
    return $months[$month][(int)$short];
}

function leadingZero($num) {
    if ($num == '') return '00';
    if (mb_strlen($num) < 2) {
        return '0'.$num;
    } else {
        return $num;
    }
}

/* veranstaltung_beginn liefert den tatsächlichen ersten Termin einer Veranstaltung */
function veranstaltung_beginn($seminar_id = '', $return_mode = '') {
    if ($seminar_id == '') return 'dates.inc.php:veranstaltung_beginn - Fehlerhafter Aufruf!';
    $sem = new Seminar($seminar_id);
    return $sem->getFirstDate($return_mode);
}

/*
Die Funktion veranstaltung_beginn_from_metadata errechnet den ersten Seminartermin aus dem Turnus Daten.
Zurueckgegeben wird ausschließlich ein Timestamp
Diese Funktion arbeitet im 'ad hoc' Modus und erwartet die einzelnen Variabeln des Metadaten-Arrays als Uebergabe.
Konkrete Termine werde dabei NICHT mit beruecksichtigt!
*/
function veranstaltung_beginn_from_metadata($reg_irreg, $sem_begin, $start_woche, $start_termin,$turnus_data, $return_mode='int') {
    $ret_time = 0;
    if( $return_mode != 'int'){
        echo "<br>Fehler in dates.inc.php: veranstaltung_beginn_from_metadata() unterstuetzt nur den return mode 'int'.";
        die();
    }
    $semester = SemesterData::getSemesterDataByDate($sem_begin);
    $dow = date("w", $semester['vorles_beginn']);
    if ($dow <= 5)
        $corr = ($dow -1) * -1;
    elseif ($dow == 6)
        $corr = 2;
    elseif ($dow == 0)
        $corr = 1;
    else
    $corr = 0;

    if(is_array($turnus_data)){
        foreach ($turnus_data as $key => $val) {
            $start_time = mktime ((int)$val['start_stunde'], (int)$val['start_minute'], 0, date("n", $semester['vorles_beginn']), (date("j", $semester['vorles_beginn'])+$corr) + ($val['day'] -1) + ($start_woche * 7), date("Y", $semester['vorles_beginn']));
            if (($start_time < $ret_time) || ($ret_time == 0)) {
                $ret_time = $start_time;
            }
        }
    }

    return $ret_time;
}


/*
 * The function shrink_dates expects an array of dates where the start_time and the end_time is noted
 * and creates a compressed version (spanning f.e. multiple dates).
 *
 * Returns an array, where each element is one condensed entry. (f.e. 10.6 - 14.6 8:00 - 12:00,)
 */
function shrink_dates($dates) {
    $ret = [];

    // First step: Clean out all duplicate dates (the dates are sorted)
    foreach ($dates as $key => $date) {
        if (isset($dates[$key + 1])) {
            if ($dates[$key + 1]['start_time'] == $date['start_time']
                    && $dates[$key + 1]['end_time'] == $date['end_time']) {
                unset($dates[$key]);
            }
        }
    }

    // Second step: Make sure the dates are still ordered by start- and end-time without any holes
    usort($dates, function($a, $b) {
        if ($a['start_time'] == $b['start_time']) {
            if ($a['end_time'] == $b['end_time']) return 0;
            return ($a['end_time'] > $b['end_time']) ? 1 : -1;
        }

        return ($a['start_time'] > $b['start_time']) ? 1 : -1;
    });

    // Third step: Check which dates are follow-ups
    for ($i=1; $i < sizeof($dates); $i++) {
        if (((date("G", $dates[$i-1]["start_time"])) == date("G", $dates[$i]["start_time"]))
                && ((date("i", $dates[$i-1]["start_time"])) == date("i", $dates[$i]["start_time"]))
                && ((date("G", $dates[$i-1]["end_time"])) == date("G", $dates[$i]["end_time"]))
                && ((date("i", $dates[$i-1]["end_time"])) == date("i", $dates[$i]["end_time"]))) {
            $dates[$i]["time_match"] = true;
        }

        if (((date ("z", $dates[$i]["start_time"])-1) == date ("z", $dates[$i-1]["start_time"]))
                || ((date ("z", $dates[$i]["start_time"]) == 0) && (date ("j", $dates[$i-1]["start_time"]) == 0))) {
            if ($dates[$i]["time_match"]) {
                $dates[$i]["conjuncted"] = true;
            }
        }
    }

    // Fourth step: aggregate the dates with follow-ups
    $return_string = '';
    // create text-output
    for ($i=0; $i < sizeof($dates); $i++) {
        if (!$dates[$i]["conjuncted"]) {
            $conjuncted = false;
        }

        if ((!$dates[$i]["conjuncted"]) || (!$dates[$i+1]["conjuncted"])) {
            $return_string .= ' ' . strftime('%A', $dates[$i]['start_time']) .'.';
            $return_string .= date (" d.m.y", $dates[$i]["start_time"]);
        }

        if ((!$conjuncted) && ($dates[$i+1]["conjuncted"])) {
            $return_string .= ' -';
            $conjuncted = true;
        } else if ((!$dates[$i+1]["conjuncted"]) && ($dates[$i+1]["time_match"])) {
            $return_string .= ',';
        }

        if (!$dates[$i+1]["time_match"]) {
            // check if the current date is for a whole day
            if ((($dates[$i]["end_time"] - $dates[$i]["start_time"]) / 60 / 60) > 23) {
                $return_string .= ' ('. _('ganztägig') . ')';
            } else {
                $return_string .= ' ' . date("H:i", $dates[$i]["start_time"]);
                if (date("H:i", $dates[$i]["start_time"]) != date("H:i", $dates[$i]["end_time"])) {
                    $return_string .= ' - ' . date("H:i", $dates[$i]["end_time"]);
                }
            }
        }

        if ($return_string != '' && !$dates[$i+1]['conjuncted'] && !$dates[$i+1]['time_match']) {
            $ret[] = $return_string;
            $return_string = '';
        }
    }

    return $ret;
}

/*
Die Funktion Vorbesprechung ueberpueft, ob es eine Vorbesprechung gibt und gibt in diesem
Falle den entsprechenden Timestamp zurueck. Ansonsten wird FALSE zurueckgegeben.
*/

function vorbesprechung ($seminar_id, $type = 'standard')
{
    $query = "SELECT termin_id
              FROM termine
              WHERE range_id = ? AND date_typ = '2'
              ORDER BY date";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id]);
    $termin_id = $statement->fetchColumn();

    if (!$termin_id) {
        return false;
    }

    $termin = new SingleDate($termin_id);
    $ret = $termin->toString();
    if ($termin->getResourceID()) {
        $ret .= ', '._("Ort:").' ';
        switch ($type) {
            case 'export':
                $resObj = ResourceObject::Factory($termin->getResourceID());
                $ret .= $resObj->getName();
                break;

            case 'standard':
            default:
                $resObj = ResourceObject::Factory($termin->getResourceID());
                $ret .= $resObj->getFormattedLink(TRUE, TRUE, TRUE);
                break;
        }
    }
    return $ret;
}

/*
Die Funktion get_sem_name gibt den Namen eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/

function get_sem_name ($time) {
    $semester = SemesterData::getSemesterDataByDate($time);
    return $semester["name"];
}

/*
Die Funktion get_sem_num gibt die Nummer eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/

function get_sem_num ($time) {
    $all_semester = SemesterData::getAllSemesterData();
    foreach ($all_semester as $key=>$val)
        if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
            return $key;

}

function get_sem_num_sem_browse () {
    $all_semester = SemesterData::getAllSemesterData();
    $time = time();
    $ret = false;
    foreach ($all_semester as $key=>$val){
        if ($ret && ($val["vorles_ende"] >= $time)){
            $ret = $key;
            break;
        }
        if ($time >= $val["vorles_ende"]){
            $ret = true;
        }
    }
    return $ret;
}

/*
Die Funktion get_semester gibt den oder die Semester einer speziellen Veranstaltung aus.
*/

function get_semester($seminar_id, $start_sem_only=FALSE)
{
    $query = "SELECT start_time, duration_time FROM seminare WHERE seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id]);
    $temp = $statement->fetch(PDO::FETCH_ASSOC);

    $return_string = get_sem_name($temp['start_time']);
    if (!$start_sem_only) {
        if ($temp['duration_time'] > 0) {
            $return_string .= ' - ' . get_sem_name($temp['start_time'] + $temp['duration_time']);
        }
        if ($temp['duration_time'] == -1) {
            $return_string .= ' ' . _('bis unbegrenzt');
        }
    }
    return $return_string;
}

/*
Die function delete_date löscht einen Termin und verschiebt daran haegende
Ordner in den allgemeinen Ordner.
Der erste Parameter ist die termin_id des zu löschenden Termins.
Der zweite Parameter topic_id gibt an, ob auch die zu diesem Termin gehoerenden
Postings im Forensystem geloescht werden sollen.
0 bzw. FALSE : keine Topics loeschen
> 0 : rekursives loeschen von topic_id
Der dritte Parameter gibt analog an, ob auch die zu diesem Terminen gehoerenden
Folder im Ordnersystem geloescht werden sollen.
Der Rückgabewert der Funktion ist die Anzahl der insgesamt gelöschten Items.
-1 bedeutet einen Fehler beim Loeschen des Termins.
Ausgabe wird keine produziert.
Es erfolgt keine Überprüfung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_date($termin_id, $topic_delete = TRUE, $folder_move = TRUE, $sem_id=0) {

    if (Config::get()->RESOURCES_ENABLE) {
        include_once ("lib/resources/lib/VeranstaltungResourcesAssign.class.php");
    }

    //Deleting folders was removed since folders can't be assigned to
    //single dates, only to topics.

    ## Und den Termin selbst loeschen
    $query = "DELETE FROM termine WHERE termin_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$termin_id]);

    if ($statement->rowCount() && Config::get()->RESOURCES_ENABLE) {
        $insertAssign = new VeranstaltungResourcesAssign($sem_id);
        $insertAssign->killDateAssign($termin_id);
    }
}

/*
Die function delete_range_of_dates löscht Termine mit allen daran haengenden Items.
Der erste Parameter ist die range_id der zu löschenden Termine.
Es koennen also mit einem Aufruf alle Termine eines Seminares,
eines Institutes oder persoenliche Termine eines Benutzers aus der Datenbank entfernt werden.
Dokumente und Literatur an diesen Terminen werden auf jeden Fall gelöscht.
Der zweite Parameter topics gibt an, ob auch die zu diesen Terminen gehoerenden
Postings im Forensystem geloescht werden sollen.
0 bzw. FALSE : keine Topics loeschen
1 bzw. TURE : rekursives Loeschen der Postings
Der Rückgabewert der Funktion ist die Anzahl der gelöschten Termine.
Ausgabe wird keine produziert.
Es erfolgt keine Überprüfung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_range_of_dates($range_id, $topics = FALSE)
{
    $count = 0;

    ## Termine finden...
    $query = "SELECT termin_id FROM termine WHERE range_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$range_id]);

    while ($termin_id = $statement->fetchColumn()) {       // ...und nacheinander...
        delete_date($termin_id, $topics, true, $range_id);
        $count++;
    }

    return $count;
}


//Checkt, ob Ablaufplantermine zu gespeicherten Metadaten vorliegen
function isSchedule ($sem_id, $presence_dates_only = TRUE, $clearcache = FALSE)
{
    $query = "SELECT COUNT(*)
              FROM termine
              WHERE range_id = ? AND metadate_id != '' AND metadate_id IS NOT NULL";
    if ($presence_dates_only) {
        $query .= " AND date_typ IN " . getPresenceTypeClause();
    }

    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);

    return $statement->fetchColumn();
}


/**
* this functions checks, if a date corresponds with a metadate
*
* @param        string  termin_id
* @return       boolean TRUE, if the date corresponds to a metadate
*
*/
function isMetadateCorrespondingDate ($termin_id, $begin = '', $end = '', $seminar_id='')
{
    $termin = new SingleDate($termin_id);
    if ($termin->getMetaDateID()) {
        return $termin->getRangeId();
    }

    return false;
}


/**
* a small helper funktion to get the type query for "Sitzungstermine"
* (this dates are important to get the regularly, presence dates
* for a seminar
*
* @return   string  the SQL-clause to select only the "Sitzungstermine"
*
*/
function getPresenceTypeClause() {
    global $TERMIN_TYP;

    $i=0;
    $typ_clause = "(";
    foreach ($TERMIN_TYP as $key=>$val) {
        if ($val["sitzung"]) {
            if ($i)
                $typ_clause .= ", ";
            $typ_clause .= "'".$key."' ";
            $i++;
        }
    }
    $typ_clause .= ")";

    return $typ_clause;
}

function getPresenceTypes() {
    global $TERMIN_TYP;

    foreach ($TERMIN_TYP as $key=>$val) {
        if ($val["sitzung"]) {
            $types[] = $key;
        }
    }

    return $types;
}

/**
 * Return an array of room snippets, possibly linked
 *
 * @param array $rooms  an associative array of rooms
 * @param bool  $html   true if you want links, otherwise false
 *
 * @return array  an array of (formatted) room snippets
 */
function getFormattedRooms($rooms, $link = false)
{
    $room_list = [];

    if (is_array($rooms)) {
        foreach ($rooms as $room_id => $count) {
            if ($room_id && Config::get()->RESOURCES_ENABLE) {
                $resObj = ResourceObject::Factory($room_id);
                if ($link) {
                    $room_list[] = $resObj->getFormattedLink(TRUE, TRUE, TRUE,
                        'view_schedule', 'no_nav', false, htmlReady($resObj->getName()));
                } else {
                    $room_list[] = htmlReady($resObj->getName());
                }
            }
        }
    }

    return $room_list;
}

/**
 * Return an array of room snippets without any formatting
 *
 * @param array $rooms  an associative array of rooms
 *
 * @return array  an array of room snippets
 */
function getPlainRooms($rooms) {
    $room_list = [];

    if (is_array($rooms)) {
        foreach ($rooms as $room_id => $count) {
            if ($room_id) {
                $resObj =& ResourceObject::Factory($room_id);
                $room_list[] = $resObj->getName();
            }
        }
    }

    return $room_list;
}
