<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* SemScheduleWeek.class.php
* 
* creates a grafical schedule view for different purposes, ie. a personal timetable
* or a timetable for a ressource like a room, a device or a building
* 
*
* @author       André Noack <noack@data-quest.de>
* @access       public
* @package      resources
* @modulegroup  resources_modules
* @module       ScheduleWeek.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ScheduleWeek.class.php
// Modul zum Erstellen grafischer Belegungspläne
// Copyright (C) 2005 André Noack <noack@data-quest.de>
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
require_once "lib/resources/views/ScheduleWeek.class.php";

class SemScheduleWeek  extends ScheduleWeek {
    
    //Kontruktor
    function __construct($start_hour = '', $end_hour = '', $show_days = '', $start_date = '') {
        
        parent::__construct($start_hour, $end_hour, $show_days, $start_date, false);

        //the base_date have to be 0:00
        $first_monday = date("j",$this->start_date)  - (date("w", $this->start_date) - 1);
        if (date("w", $this->start_date) > 1){
            $first_monday += 7;
        }
        $this->base_date = mktime(0, 0, 0, date("n", $this->start_date), $first_monday ,  date("Y", $this->start_date));
    }
    
    function getColumnName($id, $print_view = false){
        $ts = mktime (0,0,0,date("n",$this->base_date), date("j",$this->base_date)+$id-1, date("Y",$this->base_date));
        $out = strftime("%A", $ts);
        return $out;
    }
    
}
?>
