<?php
/*
 * studip_seminar_info.php - base class for seminars
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Studip_Seminar_Info extends Studip_Ws_Struct
{
    function init() {
        Studip_Seminar_Info::add_element('title', 'string');
        Studip_Seminar_Info::add_element('lecturers', ['Studip_User']);
        Studip_Seminar_Info::add_element('turnus', 'string');
        Studip_Seminar_Info::add_element('lecture_number', 'string');
    }
}
