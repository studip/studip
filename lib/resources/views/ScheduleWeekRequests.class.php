<?php
# Lifter002: TODO
# Lifter010: TODO
/**
* ScheduleWeek.class.php
*
* creates a grafical schedule view for different purposes, ie. a personal timetable
* or a timetable for a ressource like a room, a device or a building
*
*
* @author       Cornelis Kater <ckater@gwdg.de>
* @access       public
* @package      resources
* @modulegroup  resources_modules
* @module       ScheduleWeek.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ScheduleWeek.class.php
// Modul zum Erstellen grafischer Belegungspläne
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>
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

class ScheduleWeekRequests extends ScheduleWeek {

    //Kontruktor
    function __construct($start_hour = '', $end_hour = '', $show_days = '', $start_date = '', $show_dates = true) {

        parent::__construct($start_hour, $end_hour, $show_days, $start_date);
        $this->categories[5] = [
                                "bg-picture"   => Assets::image_path('calendar/category12_small.jpg'),
                                "border-color" => "#d082b0"];
        $this->categories[6] = [
                                "bg-picture"   => Assets::image_path('calendar/category10_small.jpg'),
                                "border-color" => "#ffbd33"];
        $this->add_info_is_html = true;

    }



    function getColumnName($id, $print_view = false) {
        $ts = mktime (0,0,0,date("n",$this->start_date), date("j",$this->start_date)+$id-1, date("Y",$this->start_date));
        $out = strftime("%A", $ts);
        if ($this->show_dates) $out .= "<br><font size=\"-1\">" . date("d.m.y", $ts) . "</font>\n";;
        return $out;
    }

}
