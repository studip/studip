<?
# Lifter002: DONE - not applicable
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE - not applicable

/**
* list_assign.inc.php
*
* library, contains functions to create the AssinEvents
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @package      resources
* @modulegroup  resources_modules
* @module       list_assign.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// list_assign.inc.php
// Library der Funktionen zur Erstellung von AssignEvents
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once 'lib/dates.inc.php';

function list_restore_assign(&$assEvtLst, $resource_id, $begin, $end, $user_id = '', $range_id = '', $filter = FALSE, $day_of_week = false)
{

    $year = date("Y", $begin);
    $month = date("n", $begin);
    if ($day_of_week) {
        $day_of_week = (++$day_of_week == 8 ? 1 : (int)$day_of_week);
    }

    //create the query
    $parameters = [];

    $query = "SELECT assign_id
              FROM resources_assign ";
    if ($range_id) {
        $query .= " LEFT JOIN resources_user_resources USING (resource_id)";
    }
    $query .= " WHERE";
    if ($resource_id) {
        $query .= " resource_id = :resource_id AND";
        $parameters[':resource_id'] = $resource_id;
    }
    if ($user_id) {
        $query .= " assign_user_id = :user_id AND";
        $parameters[':user_id'] = $user_id;
    }
    if ($range_id) {
        $query .= " user_id = :range_id AND";
        $parameters[':range_id'] = $range_id;
    }
    $query .= " (begin BETWEEN :begin AND :end OR (begin <= :end AND (repeat_end > :begin OR end > :begin)))";
    $parameters[':begin'] = $begin;
    $parameters[':end']   = $end;

    if ($day_of_week) {
        // For better readability, we'll use DOW() as an alias for the bulky
        // DAYOFWEEK(FROM_UNIXTIME()) and replace it afterwards
        $query .= " AND (DOW(begin) = :day_of_week OR
                         (repeat_interval > 0 AND repeat_day_of_week = 0) OR
                         (repeat_interval = 0 AND repeat_end > end AND
                          IF(DOW(begin) > DOW(repeat_end),
                             IF(:day_of_week < DOW(begin), :day_of_week + 7, :day_of_week) BETWEEN DOW(begin) AND DOW(repeat_end) + 7,
                             :day_of_week BETWEEN DOW(begin) AND DOW(repeat_end))))";
        $query = preg_replace('/DOW\((.*?)\)/', 'DAYOFWEEK(FROM_UNIXTIME($1))', $query);

        $parameters[':day_of_week'] = $day_of_week;
    }
    $quey .= " ORDER BY begin ASC";

    $statement = DBManager::get()->prepare($query);
    $statement->execute($parameters);

    //handle the assigns und create all the repeated stuff
    while ($assign_id = $statement->fetchColumn()) {
        $assign_object = new AssignObject($assign_id);
        create_assigns($assign_object, $assEvtLst, $begin, $end, $filter);
    }
}

function create_assigns($assign_object, &$assEvtLst, $begin=0, $end=0, $filter = FALSE) {
    $year_offset=0;
    $week_offset=0;
    $month_offset=0;
    $day_offset=0;
    $quantity=0;
    $temp_ts=0;

    // fetch all Holidays
    $all_holidays = SemesterHoliday::getAll();

    //if no begin/end-date submitted, we create all the assigns from the given assign-object
    if (!$begin)
        $begin = intval($assign_object->getBegin());
    if (!$end)
        $end = intval($assign_object->getRepeatEnd());

        //take a whole day!
    if(!($begin == -1 && $end == -1)){
        $begin = mktime(0,0,0,date("m", $begin), date("d", $begin), date("Y", $begin));
        $end = mktime(23,59,59,date("m", $end), date("d", $end), date("Y", $end));
    }
    $ao_repeat_mode = $assign_object->getRepeatMode();
    $ao_begin = $assign_object->getBegin();
    $ao_end = $assign_object->getEnd();
    $ao_r_end = $assign_object->getRepeatEnd();
    $ao_r_q = $assign_object->getRepeatQuantity();
    $ao_r_i = $assign_object->getRepeatInterval();
    //$ao_owner_type = $assign_object->getOwnerType();
    if ($ao_repeat_mode == "na") {
        // date without repeatation, we have to create only one event (object = event)
        $assEvt = new AssignEvent($assign_object->getId(), $ao_begin, $ao_end,
                                $assign_object->getResourceId(), $assign_object->getAssignUserId(),
                                $assign_object->getUserFreeName());
        $assEvt->setRepeatMode($ao_repeat_mode);

        if (!$filter || !isFiltered($filter, $assEvt->getRepeatMode(TRUE)))
            $assEvtLst->events[] = $assEvt;
    } elseif ($ao_repeat_mode == "sd") {
        // several days mode, we create multiple assigns

        //first day
        $temp_ts_end=mktime(23, 59, 59,
                    date("n",$ao_begin),
                    date("j",$ao_begin),
                    date("Y",$ao_begin));

        if (($temp_ts_end <= $end) && ($ao_begin >= $begin)) {
            $assEvt = new AssignEvent($assign_object->getId(), $ao_begin, $temp_ts_end,
                                $assign_object->getResourceId(), $assign_object->getAssignUserId(),
                                $assign_object->getUserFreeName());
            $assEvt->setRepeatMode($ao_repeat_mode);
            if (!isFiltered($filter, $assEvt->getRepeatMode()))
                $assEvtLst->events[] = $assEvt;
        }
        //in between days
        $date_ao_r_end = new DateTime();
        $date_ao_r_end->setTimestamp(strtotime('T12:00', $ao_r_end));
        $date_ao_begin = new DateTime();
        $date_ao_begin->setTimestamp(strtotime('T12:00', $ao_begin));
        $num_days = $date_ao_r_end->diff($date_ao_begin)->days;
        for ($d=date("j",$ao_begin)+1; $d < date("j",$ao_begin) + $num_days; $d++) {
            $temp_ts=mktime(0, 0, 0,
                    date("n",$ao_begin),
                    $d,
                    date("Y",$ao_begin));

            $temp_ts_end=mktime(23, 59, 59,
                    date("n",$ao_begin),
                    $d,
                    date("Y",$ao_begin));
            if (($temp_ts_end <= $end) && ($temp_ts >= $begin)) {
                $assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
                                $assign_object->getResourceId(), $assign_object->getAssignUserId(),
                                $assign_object->getUserFreeName());
                $assEvt->setRepeatMode($ao_repeat_mode);
                if (!isFiltered($filter, $assEvt->getRepeatMode()))
                    $assEvtLst->events[] = $assEvt;
            }
        }

        //last_day
        $temp_ts=mktime(0, 0, 0,
                    date("n",$ao_r_end),
                    date("j",$ao_r_end),
                    date("Y",$ao_r_end));

        $temp_ts_end=mktime(date("G",$ao_end),
                    date("i",$ao_end),
                    0,
                    date("n",$ao_r_end),
                    date("j",$ao_r_end),
                    date("Y",$ao_r_end));

        if (($temp_ts_end <= $end) && ($temp_ts >= $begin)) {
            $assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
                                $assign_object->getResourceId(), $assign_object->getAssignUserId(),
                                $assign_object->getUserFreeName());
            $assEvt->setRepeatMode($ao_repeat_mode);
            if (!isFiltered($filter, $assEvt->getRepeatMode()))
                $assEvtLst->events[] = $assEvt;
        }

    } elseif ((($ao_r_end >= $begin) && ($ao_begin <= $end)) ||
            (($begin == -1) &&($end == -1) && ($ao_r_q  >0)))
        do {

        //create a temp_ts to try every possible repeatation
        $temp_ts=mktime(date("G",$ao_begin),
                    date("i",$ao_begin),
                    0,
                    date("n",$ao_begin)+($month_offset * $ao_r_i),
                    date("j",$ao_begin)+($week_offset * $ao_r_i * 7) + ($day_offset * $ao_r_i),
                    date("Y",$ao_begin)+($year_offset * $ao_r_i));
        $temp_ts_end=mktime(date("G",$ao_end),
                    date("i",$ao_end),
                    0,
                    date("n",$ao_begin) + ($month_offset * $ao_r_i),
                    date("j",$ao_end)+($week_offset * $ao_r_i * 7)  + ($day_offset * $ao_r_i),
                    date("Y",$ao_end)+($year_offset * $ao_r_i));
        //change the offsets
        if ($ao_repeat_mode == "y") $year_offset++;
        if ($ao_repeat_mode == "w") $week_offset++;
        if ($ao_repeat_mode == "m") $month_offset++;
        if ($ao_repeat_mode == "d") $day_offset++;

        //inc the count
        $quantity++;
        //check for holidays (we do this only for repeated assign (means only here) and only for assigns by seminars!))
        /*
        if ($ao_owner_type == "sem") {
            $holiday_skipping = FALSE;
            foreach ($all_holidays as $val) {
                if (($val["beginn"] <= $temp_ts) && ($temp_ts <=$val["ende"]))
                    $holiday_skipping = TRUE;
            }
            if ($red_letter_day = holiday($temp_ts)){
                if ($red_letter_day["col"]==3){
                    $holiday_skipping = TRUE;
                }
            }
        }

        if (!$holiday_skipping) {
        */
            //check if we want to show the event and if it is not outdated
            if (($begin == -1) && ($end == -1) && ($ao_r_q  >0))
                    $assEvtLst->events[] = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
                                            $assign_object->getResourceId(), $assign_object->getAssignUserId(),
                                            $assign_object->getUserFreeName());
            elseif ($temp_ts >= $begin) {
                 if (($temp_ts <=$end) && ($temp_ts <= $ao_r_end) && (($quantity <= $ao_r_q ) || ($ao_r_q  == -1)))  {
                    $assEvt = new AssignEvent($assign_object->getId(), $temp_ts, $temp_ts_end,
                                            $assign_object->getResourceId(), $assign_object->getAssignUserId(),
                                            $assign_object->getUserFreeName());
                    $assEvt->setRepeatMode($ao_repeat_mode);

                    if (!isFiltered($filter, $assEvt->getRepeatMode()))
                        $assEvtLst->events[] = $assEvt;
                }
            }
        //}

        //security break
        if ($quantity > 150)
            break;

        } while((($temp_ts <=$end) && ($temp_ts <= $ao_r_end) && ($quantity < $ao_r_q  || $ao_r_q  == -1)) ||
                (($begin == -1) &&($end == -1) &&($ao_r_q ) >0) && ($quantity < $ao_r_q ));
}

function isFiltered($filter, $mode)
{
    $filters = [   // filter rules (a filter consists of one or more repeat_modes)
        "all"=>["na", "sd", "meta", "d", "m", "y", "w"],
        "single"=>["na", "sd"],
        "repeated"=>["meta", "d","m", "y", "w"],
        "semschedulesingle" => ['na','sd','d','m','y']];
    if ($filter) {
        return !in_array($mode, $filters[$filter]);
    } else {
        return FALSE;
    }
}

function createNormalizedAssigns($resource_id, $begin, $end, $explain_user_name = false, $day_of_week = false)
{
    $a_obj = new AssignObject(null);

    if ($day_of_week){
        $day_of_week = (++$day_of_week == 8 ? 1 : $day_of_week);
    }

    // Prepare teacher statement
    $query = "SELECT trp.user_id
              FROM seminar_cycle_dates AS scd
              INNER JOIN termine AS t USING(metadate_id)
              INNER JOIN termin_related_persons AS trp ON trp.range_id = t.termin_id
              WHERE scd.metadate_id = ?";
    $teacher_statement = DBManager::get()->prepare($query);

    // Prepare and execute statement that obtains all assignments for a
    // given resource
    $query = "SELECT assign_id
              FROM resources_assign
              WHERE resource_id = :resource_id
                AND (begin BETWEEN :begin AND :end OR (begin <= :end AND (repeat_end > :begin OR end > :begin)))";
    if ($day_of_week) {
        $query .= " AND DAYOFWEEK(FROM_UNIXTIME(begin)) = :day_of_week ";
    }
    $query .= "ORDER BY begin ASC";

    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':resource_id', $resource_id);
    $statement->bindValue(':begin', $begin);
    $statement->bindValue(':end', $end);
    if ($day_of_week) {
        $statement->bindValue(':day_of_week', $day_of_week);
    }
    $statement->execute();

    $events = [];
    while ($assign_id = $statement->fetchColumn()) {
        if($a_obj->restore($assign_id)) {
            $seminar_id = $sem_doz_names = false;
            unset($sem_obj);
            $repmode = $a_obj->getRepeatMode();
            if ($repmode == 'na' && $a_obj->getAssignUserId()
            && ($metadate = SeminarCycleDate::findByTermin($a_obj->getAssignUserId())) ){
                $repmode = 'meta';
                $seminar_id = $metadate->seminar_id;
                $sem_obj = Seminar::GetInstance($seminar_id);

                $teacher_statement->execute([
                    $metadate->getId()
                ]);
                $r_dozenten = $teacher_statement->fetchAll(PDO::FETCH_COLUMN);
                $teacher_statement->closeCursor();

                if (count($r_dozenten)) {
                    $dozenten = array_intersect_key($sem_obj->getMembers('dozent'), array_flip($r_dozenten));
                } else {
                    $dozenten = $sem_obj->getMembers('dozent');
                }
                $sem_doz_names = array_map(function ($a) { return $a['Nachname']; }, array_slice($dozenten,0,3, true));
                $sem_doz_names = join(', ' , $sem_doz_names);
            }
            if($repmode == 'meta'){
                $event_id = getNormalizedEventId($a_obj->getBegin(),$a_obj->getEnd(),$seminar_id);
                if (!isset($events[$event_id])){
                    $events[$event_id] = ['begin' => $a_obj->getBegin(),
                                                'end' => $a_obj->getEnd(),
                                                'assign_id' => $a_obj->getId(),
                                                'assign_user_id' => $a_obj->getAssignUserId(),
                                                'repeat_mode' => $repmode,
                                                'is_meta' => 1,
                                                'repeat_interval' => $metadate->cycle + 1,
                                                'seminar_id' => $seminar_id,
                                                'name' => $a_obj->getUserName(false,$explain_user_name),
                                                'sem_doz_names' => $sem_doz_names
                                                ];

                }
            } else if ($repmode == 'w'){
                $events[$a_obj->getId()] = ['begin' => $a_obj->getBegin(),
                                                'end' => $a_obj->getEnd(),
                                                'assign_id' => $a_obj->getId(),
                                                'assign_user_id' => $a_obj->getAssignUserId(),
                                                'repeat_mode' => $repmode,
                                                'repeat_interval' => $a_obj->getRepeatInterval(),
                                                'is_meta' => 0,
                                                'name' => $a_obj->getUserName(true,$explain_user_name),
                                                'sem_doz_names' => $sem_doz_names
                                                ];
            }

        }
    }
    return $events;
}

function getNormalizedEventId($begin,$end,$sem_id){
    return md5($sem_id . ':'.date('G', $begin).':'.date('i', $begin).':'.date('s', $begin).':'.date('w',$begin).':'.date('G', $end).':'.date('i', $end).':'.date('s', $end).':'.date('w',$end));
}
