<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* VeranstaltungResourcesAssign.class.php
*
* updates the saved settings from dates and metadates from a Veranstaltung
* and the linked resources (rooms)
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @package      resources
* @modulegroup  resources_modules
* @module       VeranstaltungResourcesAssign.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// VeranstaltungResourcesAssign.class.php
// Modul zum Verknuepfen von Veranstaltungszeiten mit Resourcenbelegung
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

class VeranstaltungResourcesAssign {
    var $db;
    var $db2;
    var $seminar_id;
    var $assign_id;
    var $dont_check;

    //Konstruktor
    function __construct($seminar_id=FALSE)
    {
        //make shure to load all the classes from resources, if this class is extern used °change if the classes are storen in own scripts
        $this->seminar_id = $seminar_id;
        $this->dont_check=FALSE;
    }

    function updateAssign($check_locks = true)
    {
        global $TERMIN_TYP;

        $query = "SELECT termin_id FROM termine WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->seminar_id]);
        while ($termin_id = $staetment->fetchColumn()) {
            $result = array_merge((array)$result, (array)$this->changeDateAssign($termin_id));
        }
        //kill all assigned rooms (only roomes and only resources assigned directly to the Veranstaltung, not to a termin!) to create new ones
        $this->deleteAssignedRooms();

        //Raumanfrage als bearbeitet markieren, wenn vorhanden
        if(Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS){
            $request = new RoomRequest(RoomRequest::existsByCourse($this->seminar_id));
            if (!$request->isNew()){
                $request->checkOpen(true);
            }
        }
        return $result;
    }



    function getDateAssignObjects($presence_dates_only = FALSE) {
        $sem = Seminar::getInstance($this->seminar_id);

        // get regular metadates
        foreach ($sem->getCycles() as $cycle_id => $cycle) {
            // get the assigned singledates
            $dates = $sem->getSingleDatesForCycle($cycle_id);
            foreach ($dates as $date) {
                if ($date->isPresence() || !$presence_dates_only) {
                    $assignObjects[$date->getSingleDateId()] =& $this->getDateAssignObject($date->getSingleDateId());
                }
            }
        }

        // get irregular singledates
        foreach ($sem->getSingledates() as $date) {
            if ($date->isPresence() || !$presence_dates_only) {
                $assignObjects[$date->getSingleDateId()] =& $this->getDateAssignObject($date->getSingleDateId());
            }
        }

        return $assignObjects;
    }

    function getMetaDateAssignObjects($cycle_id) {
        $sem = Seminar::getInstance($this->seminar_id);
        // get the assigned singledates
        $dates = $sem->getSingleDatesForCycle($cycle_id);
        foreach ($dates as $date) {
            $assignObjects[$date->getSingleDateId()] = $this->getDateAssignObject($date->getSingleDateId());
        }
        return $assignObjects;
    }

    //this method creates an assign-object for a seminar-date
    function getDateAssignObject($termin_id, $resource_id = '', $begin = 0, $end = 0)
    {
        if (!$begin) {
            $query = "SELECT date, end_time, assign_id
                      FROM termine
                      LEFT JOIN resources_assign ON (assign_user_id = termin_id)
                      WHERE termin_id = ?
                      ORDER BY date, content";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$termin_id]);
            if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $assign_id = $row['assign_id'];
                $begin     = $row['date'];
                $end       = $row['end_time'];
            }
        } else if (!$end) {
            $end = $begin;
        }

        $AssignObject = AssignObject::Factory($assign_id);
        if ($resource_id) {
            $AssignObject->setResourceId($resource_id);
        }
        if (!$AssignObject->getAssignUserId()) {
            $AssignObject->setAssignUserId($termin_id);
        }

        $AssignObject->setBegin($begin);
        $AssignObject->setEnd($end);
        $AssignObject->setRepeatEnd($end);
        $AssignObject->setRepeatQuantity(0);
        $AssignObject->setRepeatInterval(0);
        $AssignObject->setRepeatMonthOfYear(0);
        $AssignObject->setRepeatDayOfMonth(0);
        $AssignObject->setRepeatWeekOfMonth(0);
        $AssignObject->setRepeatDayOfWeek(0);
        if (!$AssignObject->getId()) {
            $AssignObject->createId();
        }

        return $AssignObject;
    }

    //this method saves the assigns and does overlap checks;
    function changeMetaAssigns($term_data='', $veranstaltung_start_time='', $veranstaltung_duration_time='', $check_only = FALSE, $assignObjects = FALSE, $check_locks = TRUE) {
        if (func_num_args() == 1)
            $assignObjects = func_get_arg(0);

        //check and save the assigns
        $i=0;
        if (is_array($assignObjects))
            foreach ($assignObjects as $obj) {
                if ($obj->getResourceId()) {
                    //check if there are overlaps (resource isn't free!)
                    if (!$this->dont_check)
                        $overlaps = $obj->checkOverlap($check_locks);

                    if ($overlaps)
                        $result[$obj->getId()]=["overlap_assigns"=>$overlaps, "resource_id"=>$obj->getResourceId()];
                    $i++;

                    if ((!$check_only) && (!$overlaps)) {
                        $obj->setCommentInternal(Request::get('comment_internal'));
                        $obj->create();
                        $result[$obj->getId()]=["overlap_assigns"=>FALSE, "resource_id"=>$obj->getResourceId()];
                    }

                }
            }
        return $result;
    }

    function changeDateAssign($termin_id, $resource_id='', $begin='', $end='', $check_only=FALSE, $check_locks = TRUE)
    {
        //load data from termin and assign object
        $query = "SELECT date, content, end_time, assign_id,
                         resources_assign.begin AS assign_begin,
                         resources_assign.end AS assign_end,
                         resources_assign.resource_id AS assign_resource_id
                  FROM termine
                  LEFT JOIN resources_assign ON (assign_user_id = termin_id)
                  WHERE termin_id = ?
                  ORDER BY date, content";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);

        if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (!$begin) {
                $assign_id = $row['assign_id'];
                $begin     = $row['date'];
                $end       = $row['end_time'];
            } else if (!$end) {
                $end = $begin;
            }

            if (!$resource_id) {
                $resource_id = $row['assign_resource_id'];
            }

            $assign_begin       = $row['assign_begin'];
            $assign_end         = $row['assign_end'];
            $assign_resource_id = $row['assign_resource_id'];
        } else {
            return FALSE;
        }

        //check the saved assign-object-times against the planned times - if the same, no update is needed.
        if (($assign_begin == $begin) && ($assign_end == $end) && (($assign_resource_id == $resource_id))) {
            return TRUE;
        }
        if ((!$assign_id) && (!$check_only)) {
            $result = $this->insertDateAssign($termin_id, $resource_id);
        } else {
            $changeAssign = AssignObject::Factory($assign_id);
            if ($resource_id)
                $changeAssign->setResourceId($resource_id);
            else
                $resource_id = $changeAssign->getResourceId();

            $changeAssign->setBegin($begin);
            $changeAssign->setEnd($end);
            $changeAssign->setRepeatEnd($end);
            $changeAssign->setRepeatQuantity(0);
            $changeAssign->setRepeatInterval(0);
            $changeAssign->setRepeatMonthOfYear(0);
            $changeAssign->setRepeatDayOfMonth(0);
            $changeAssign->setRepeatWeekOfMonth(0);
            $changeAssign->setRepeatDayOfWeek(0);
            if (!$changeAssign->getId())
                $changeAssign->createId();

            //check if there are overlaps (resource isn't free!)
            if (!$this->dont_check)
                $overlaps = $changeAssign->checkOverlap($check_locks);

            if ($overlaps) {
                $result[$changeAssign->getId()]=["overlap_assigns"=>$overlaps, "resource_id"=>$resource_id, "termin_id"=>$termin_id];
                $this->killDateAssign($termin_id);
            }

            if ((!$check_only) && (!$overlaps)) {
                $changeAssign->store();
                $result[$changeAssign->getId()]=["overlap_assigns"=>FALSE, "resource_id"=>$resource_id, "termin_id"=>$termin_id];
                //Raumanfrage als bearbeitet markieren, wenn vorhanden
                if(Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS){
                    $request = new RoomRequest(RoomRequest::existsByDate($termin_id));
                    if (!$request->isNew()){
                        $request->checkOpen(true);
                    }
                }
            }
        }
        return $result;
    }

    function insertDateAssign($termin_id, $resource_id, $begin='', $end='', $check_only=FALSE, $check_locks = TRUE)
    {
        if ($resource_id) {
            if (!$begin) {
                $query = "SELECT date, end_time FROM termine WHERE termin_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$termin_id]);
                if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $begin = $row['date'];
                    $end   = $row['end_time'];
                }
            } else if (!$end) {
                $end = $begin;
            }

            if ($begin) {
                $createAssign = AssignObject::Factory(FALSE, $resource_id, $termin_id, '',
                                            $begin, $end, $end,
                                            0, 0, 0, 0, 0, 0, Request::get('comment_internal'));
                //check if there are overlaps (resource isn't free!)
                if (!$this->dont_check)
                    $overlaps = $createAssign->checkOverlap($check_locks);

                if ($overlaps)
                    $result[$createAssign->getId()]=["overlap_assigns"=>$overlaps, "resource_id"=>$resource_id, "termin_id"=>$termin_id];

                if ((!$check_only) && (!$overlaps)) {
                    $createAssign->create();
                    $result[$createAssign->getId()]=["overlap_assigns"=>FALSE, "resource_id"=>$resource_id, "termin_id"=>$termin_id];
                }
            }
        }
        return $result;
    }

    function killDateAssign($termin_id)
    {
        if (!$termin_id) {
            return;
        }

        $query = "SELECT assign_id
                  FROM resources_assign
                  LEFT JOIN resources_objects USING (resource_id)
                  LEFT JOIN resources_categories USING (category_id)
                  WHERE assign_user_id = ? AND resources_categories.is_room = 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        while ($assign_id = $statement->fetchColumn()) {
            AssignObject::Factory($assign_id)->delete();
        }

        $query = "SELECT request_id FROM resources_requests WHERE termin_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$termin_id]);
        while ($request_id = $statement->fetchColumn()) {
            $killRequest = new RoomRequest ($request_id);
            $killRequest->delete();
        }
    }

    function deleteAssignedRooms()
    {
        if (!$this->seminar_id) {
            return;
        }

        $query = "SELECT assign_id
                  FROM resources_assign
                  LEFT JOIN resources_objects USING (resource_id)
                  LEFT JOIN resources_categories USING (category_id)
                  WHERE resources_assign.assign_user_id = ?
                    AND resources_categories.is_room = 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->seminar_id]);
        while ($assign_id = $statement->fetchColumn()) {
            AssignObject::Factory($assign_id)->delete();
        }
    }
}
