<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* calendar_functions.inc.php
*
* basic calendar functions
*
* @author       Peter Thienel <pthienel@web.de>
*   @access     public
* @package      studip_core
* @modulegroup      library
* @module       calendar_functions
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// calendar_functions.inc.php
//
// Copyright (C) 2001 Peter Thienel <pthienel@web.de>
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


// Hier jezt die ultimative Feiertags-"Berechnung"
// Zurueckgegeben wird ein Array mit Namen des Feiertages ("name") und
// Faerbungsgrad ("col", 0 bis 2).

function holiday ($tmstamp, $mod = "") {
    // erstmal brauchen wir den Ostersonntag fuer die meisten kirchlichen Feiertage
//  $easterday = easter_date(date("Y", $tmstamp)); // geht leider nicht
    // Berechnung nach Carters Algorithmus (gueltig von 1900 - 2099)
    $tmstamp = mktime(0,0,0,date("n",$tmstamp),date("j",$tmstamp),date("Y",$tmstamp));
    $year = date("Y", $tmstamp);
    $b = 225 - 11 * ($year % 19);
    $d = (($b - 21) % 30) + 21;
    if ($d > 48)
        $d--;
    $e = ($year + abs($year / 4) + $d + 1) % 7;
    $q = $d + 7 - $e;
    if ($q < 32)
        $easterday = date("z", mktime(0, 0, 0, 3, $q, $year)) + 1;
    else
        $easterday = date("z", mktime(0, 0, 0, 4, $q - 31, $year)) + 1;

    // Differenz in Tagen zu Ostertag berechnen
    $doy = date("z", $tmstamp) + 1;
    $dif = $doy - $easterday;
    switch ($dif) {
        case -48: $name = _("Rosenmontag"); $col = 1; break;
        case -47: $name = _("Fastnacht"); $col = 1; break;
        case -46: $name = _("Aschermittwoch"); $col = 1; break;
    //  case -8: $name = _("Palmsonntag"); $col = 1; break;
        case  -2: $name = _("Karfreitag"); $col = 3; break;
        case   0: $name = _("Ostersonntag"); $col = 3; break;
        case   1: $name = _("Ostermontag"); $col = 3; break;
        case  39: $name = _("Christi Himmelfahrt"); $col = 3; break;
        case  49: $name = _("Pfingstsonntag"); $col = 3; break;
        case  50: $name = _("Pfingstmontag"); $col = 3; break;
        case  60: $name = _("Fronleichnam"); $col = 1; break;
    }

    // die unveraenderlichen Feiertage
    switch ($doy) {
        case   1: $name = _("Neujahr"); $col = 3; break;
        case   6: $name = _("Hl. Drei Könige"); $col = 1; break;
    }

    // Schaltjahre nicht vergessen
    if (date("L", $tmstamp))
        $doy--;
    switch ($doy) {
        case  79: $name = _("Frühlingsanfang"); $col = 1; break;
        case 121: $name = _("Maifeiertag"); $col = 3; break;
//      case 125: $name = _("Europatag"); $col = 1; break;
        case 172: $name = _("Sommeranfang"); $col = 1; break;
        case 266: $name = _("Herbstanfang"); $col = 1; break;
        case 276: $name = _("Tag der deutschen Einheit"); $col = 3; break;
        case 304: $name = _("Reformationstag"); $col = 2; break;
        case 305: $name = _("Allerheiligen"); $col = 1; break;
        case 315: $name = _("Martinstag"); $col = 1; break;
        case 340: $name = _("Nikolaus"); $col = 1; break;
        case 355: $name = _("Winteranfang"); $col = 1; break;
        case 358: $name = _("Hl. Abend"); $col = 1; break;
        case 359: $name = _("1. Weihnachtstag"); $col = 3; break;
        case 360: $name = _("2. Weihnachtstag"); $col = 3; break;
        case 365: $name = _("Silvester"); $col = 1; break;
    }

    // special handling of Reformation Day in 2017
    if ($doy == 304 && $year == 2017) {
        $col = 3;
    }

    // Die Sonntagsfeiertage
    if (date("w", $tmstamp) == 0) {
        if ($doy > 127 && $doy < 135) {
            $name = _("Muttertag");
            $col = 1;
        }
        else if ($doy > 266 && $doy < 274) {
            $name = _("Erntedank");
            $col = 1;
        }
        else if ($doy > 316 && $doy < 324) {
            $name = _("Volkstrauertag");
            $col = 2;
        }
        else if ($doy > 323 && $doy < 331) {
            $name = _("Totensonntag");
            $col = 1;
        }
        else if ($doy > 330 && $doy < 338) {
            $name = _("1. Advent");
            $col = 2;
        }
        else if ($doy > 337 && $doy < 345) {
            $name = _("2. Advent");
            $col = 2;
        }
        else if ($doy > 344 && $doy < 352) {
            $name = _("3. Advent");
            $col = 2;
        }
        else if ($doy > 351 && $doy < 359) {
            $name = _("4. Advent");
            $col = 2;
        }
    }

    if ($name)
        return ["name" => $name, "col" => $col];

    return FALSE;
}

// ueberprueft eine Datumsangabe, die in einen Timestamp gewandelt werden soll
// gibt bei Erfolg den timestamp zurück mit DST
function check_date ($month, $day, $year, $hour = 0, $min = 0) {
    if (!preg_match("/^\d{1,2}$/", $day) || !preg_match("/^\d{1,2}$/", $month)
            || !preg_match("/^\d{1,2}$/", $hour) || !preg_match("/^\d{1,2}$/", $min)
            || !preg_match("/^\d{4}$/", $year)) {
        return FALSE;
    }
    if ($year < 1970 || $year > 2036)
        return FALSE;
    if (!checkdate($month, $day, $year))
        return FALSE;
    if ($hour > 23 || $hour < 0 || $min > 59 || $min < 0)
        return FALSE;

    return mktime($hour, $min, 0, $month, $day, $year);
}



/**
 * checks values that shall become a single date with start- and endtime
 *
 * @param string $day
 * @param string $month
 * @param string $year
 * @param string $start_hour
 * @param string $start_minute
 * @param string $end_hour
 * @param string $end_minute
 *
 * @return bool true if date is valid, false otherwise
 */
function check_singledate( $day, $month, $year, $start_hour, $start_minute, $end_hour, $end_minute ) {

    // check start-date
    $start = check_date($month, $day, $year, $start_hour, $start_minute);
    if (!$start) return false;

    // check end-date
    $end = check_date($month, $day, $year, $end_hour, $end_minute);
    if (!$end) return false;

    // check, that end-date is not before start_date
    return ($end > $start);
}
