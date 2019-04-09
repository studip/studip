<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
raumzeit_functions.inc.php
Helper functions for the "RaumZeit"-pages
Copyright (C) 2005-2007 Till Glöggler <tgloeggl@uos.de>

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

function getAllSortedSingleDates(&$sem) {
    $turnus = $sem->getFormattedTurnusDates();

    $termine = [];
    foreach ($sem->metadate->cycles as $metadate_id => $val) {
        $termine = array_merge($termine, $sem->getSingleDatesForCycle($metadate_id));
    }

    $termine = array_merge($termine, $sem->getSingleDates(true, false, true));
    uasort ($termine, function ($a, $b) {
        if ($a->getStartTime() === $b->getStartTime()) {
            return strnatcasecmp($a->getRoom(), $b->getRoom());
        }
        return $a->getStartTime() - $b->getStartTime();
    });

    return $termine;
}

/**
 * @param string $comment
 * @param array $dates SingleDate
 */
function raumzeit_send_cancel_message($comment, $dates)
{
    if (!is_array($dates)) {
        $dates = [$dates];
    }
    $course = Course::find($dates[0]->range_id);
    if ($course) {
        $subject = sprintf(_("[%s] Terminausfall"), $course->name);
        $recipients = $course->members->pluck('username');
        $lecturers = $course->members->findBy('status', 'dozent')->pluck('nachname');
        $message = sprintf(_("In der Veranstaltung %s fällt der/die folgende(n) Termine aus:"),
                 $course->name . ' ('. join(',', $lecturers) .') ' . $course->start_semester->name);
        $message .= "\n\n- ";
        $message .= join("\n- " , array_map(function($a) {return $a->toString();}, $dates));
        if ($comment) {
            $message .= "\n\n" . $comment;
        }
        $msg = new messaging();
        return $msg->insert_message($message, $recipients, '____%system%____', '', '', '', '', $subject, true);
    }

}
