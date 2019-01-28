<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* AssignEventList.class.php
*
* container for an list of assign-events
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       AssignEventList.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// AssignEventList.php
// Containerklasse, die eine Liste von Assign-Events bereitstellt
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

/*****************************************************************************
AssignEventList, creates a event-list for an assignobject
/*****************************************************************************/

class AssignEventList{

    var $begin;     // starttime as unix-timestamp
    var $end;       // endtime as unix-timestamp
    var $assign;        // ressources-assignements (Object[])
    var $range_id;      // range_id (String)
    var $user_id;       // userId from PhpLib (String)
    var $filter;        // filter mode (single, repeated, all)

    // Konstruktor
    // if activated without timestamps, we take the current semester
    function __construct($begin = 0, $end = 0, $resource_id='', $range_id='', $user_id='', $sort = TRUE, $filter = FALSE, $day_of_week = false){
        $semester = Semester::findCurrent();

        if (!$begin)
            $begin = $semester->beginn;
        if (!$end )
            $end = $semester->ende;

        $this->dow = $day_of_week;
        $this->begin = $begin;
        $this->end = $end;
        $this->filter = $filter;
        $this->resource_id = $resource_id;
        $this->range_id = $range_id;
        $this->user_id = $user_id;
        $this->events = [];
        $this->restore();
        if($sort)
            $this->sort();
    }

    // public
    function getBegin(){
        return $this->begin;
    }

    // public
    function getEnd(){
        return $this->end;
    }

    // public
    function getResourceId(){
        return $this->resource_id;
    }

    // public
    function getRangeId(){
        return $this->range_id;
    }

    // public
    function getUserId(){
        return $this->user_id;
    }

    // private
    function restore() {
        list_restore_assign($this, $this->resource_id,  $this->begin, $this->end, FALSE, FALSE, $this->filter, $this->dow);
    }

    // public
    function numberOfEvents(){
        return sizeof($this->events);
    }

    function existEvent(){
        return sizeof($this->events) > 0 ? TRUE : FALSE;
    }

    // public
    function nextEvent(){
        if (is_array($this->events)) {
            if (list(,$ret) = each($this->events))
                return $ret;
        }
        return FALSE;
    }

    function sort(){
        if($this->events)
            usort($this->events,"cmp_assign_events");
    }

}
