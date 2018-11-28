<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
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

require_once "lib/resources/views/ScheduleView.class.php";

class ScheduleWeek extends ScheduleView
{
    var $show_dates; // If set, the dates of each day will be shown

    //Kontruktor
    public function __construct($start_hour = '', $end_hour = '', $show_days = '', $start_date = '', $show_dates = false)
    {
        parent::__construct($start_hour, $end_hour, $show_days, $start_date);

        $this->show_dates = $show_dates;

        if (!$show_dates && $start_date) {
            $this->show_dates = true;
        }
    }

    public function addEvent ($column, $name, $start_time, $end_time, $link = '', $add_info = '', $category = 0)
    {
        $week_day = date('w', $start_time);
        if ($week_day == 0) {
            $week_day = 7;
        }
        parent::AddEvent($week_day, $name, $start_time, $end_time, $link, $add_info, $category);
    }

    public function getColumnName($id, $print_view = false)
    {
        $ts = mktime(0, 0, 0,
                     date('n', $this->start_date),
                     date('j', $this->start_date) + $id - 1,
                     date('Y', $this->start_date));
        $out = strftime('%A', $ts);
        if ($this->show_dates) {
            $out .= "<br><font size=\"-1\">" . date("d.m.y", $ts) . "</font>\n";
            $holiday = $this->getHoliday($id);
            if (!empty($holiday)) {
               $out .= "<br><font size=\"-1\">" . htmlReady($holiday['name']) . "</font>\n";
            }
        }
        return $out;
    }

    /**
     * Returns if a given calendar colum is a holiday.
     * @param string $id
     * @return mixed false if no holiday was found, an array with the name and
     *               the "col" value of the holiday otherwise
     */
    public function getHoliday($id)
    {
        $ts = mktime(0, 0, 0, 
                     date('n', $this->start_date),
                     date('j', $this->start_date) + $id - 1,
                     date('Y', $this->start_date));
        $holiday_info = SemesterHoliday::isHoliday($ts, false);
        return $holiday_info['col'] > 2 ? $holiday_info : false;
    }
}
