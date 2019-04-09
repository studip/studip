<?php

/*
 * contentbox.php - Calender Contentbox controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     calender
 */

require_once 'app/models/calendar/SingleCalendar.php';

class Calendar_ContentboxController extends StudipController {

    /**
     * Widget controller to produce the formally known show_dates()
     *
     * @param String $range_id range id (or array of range ids) of the news to get displayed
     * @return array() Array of votes
     */
    function display_action($range_id, $timespan = 604800, $start = null) {

        // Fetch time if needed
        $this->start = $start ? : strtotime('today');
        $this->timespan = $timespan;

        // To array fallback of $range_id
        if (!is_array($range_id)) {
            $this->single = true;
            $range_id = [$range_id];
        }

        foreach ($range_id as $id) {
            switch (get_object_type($id, ['user', 'sem'])) {
                case 'user':
                    $this->parseUser($id);
                    $this->userRange = true;
                    break;
                case 'sem':
                    $this->parseSeminar($id);
                    break;
            }
        }

        // Check permission to edit
        if ($this->single) {
            $this->admin = $range_id[0] == $GLOBALS['user']->id
                || (get_object_type($range_id[0], ['sem']) === 'sem' && $GLOBALS['perm']->have_studip_perm('tutor', $range_id[0]));
            // Set range_id
            $this->range_id = $range_id[0];
        }

        // Forge title
        if ($this->termine) {
            $this->title = sprintf(_('Termine für die Zeit vom %s bis zum %s'), strftime("%d. %B %Y", $this->start), strftime("%d. %B %Y", $this->start + $this->timespan));
        } else {
            $this->title = _('Termine');
        }

        // Check out if we are on a profile
        if ($this->admin) {
            $this->isProfile = $this->single && $this->userRange;
        }
    }

    private function parseSeminar($id) {
        $course = Course::find($id);
        $dates = $course->getDatesWithExdates()->findBy('end_time', [$this->start, $this->start + $this->timespan], '><');
        foreach ($dates as $courseDate) {

            // Build info
            $info = [];
            if ($courseDate->dozenten[0]) {
                $info[_('Durchführende Dozenten')] = join(', ', $courseDate->dozenten->getFullname());
            }
            if ($courseDate->statusgruppen[0]) {
                $info[_('Beteiligte Gruppen')] = join(', ', $courseDate->statusgruppen->getValue('name'));
            }

            // Store for view
            $this->termine[] = [
                'id' => $courseDate->id,
                'chdate' => $courseDate->chdate,
                'title' => $courseDate->getFullname() . ($courseDate->topics[0] ? ', ' . join(', ', $courseDate->topics->getValue('title')) : ""),
                'description' => $courseDate instanceOf CourseExDate ? $courseDate->content : '',
                'topics' => $courseDate->topics->toArray('title description'),
                'room' => $courseDate->getRoomName(),
                'info' => $info
            ];
        }
    }

    private function parseUser($id) {
        $restrictions = ($GLOBALS['user']->id == $id ? [] : ['CLASS' => 'PUBLIC']);
        $events = SingleCalendar::getEventList($id, $this->start,
                $this->start + $this->timespan, null, $restrictions);

        // Prepare termine
        $this->termine = [];

        foreach ($events as $termin) {
            // Adjust title
            if (date("Ymd", $termin->getStart()) == date("Ymd", time())) {
                $title = _('Heute') . date(", H:i", $termin->getStart());
            } else {
                $title = mb_substr(strftime("%a", $termin->getStart()), 0, 2);
                $title .= date(". d.m.Y, H:i", $termin->getStart());
            }

            if ($termin->getStart() < $termin->getEnd()) {
                if (date("Ymd", $termin->getStart()) < date("Ymd", $termin->getEnd())) {
                    $title .= " - " . mb_substr(strftime("%a", $termin->getEnd()), 0, 2);
                    $title .= date(". d.m.Y, H:i", $termin->getEnd());
                } else {
                    $title .= " - " . date("H:i", $termin->getEnd());
                }
            }

            if ($termin->getTitle()) {
                $tmp_titel = htmlReady(mila($termin->getTitle())); //Beschneiden des Titels
                $title .= ", " . $tmp_titel;
            }

            // Store for view
            $this->termine[] = [
                'id' => $termin->id,
                'type' => get_class($termin),
                'range_id' => $termin->range_id,
                'event_id' => $termin->event_id,
                'chdate' => $termin->chdate,
                'title' => $title,
                'description' => $termin->getDescription(),
                'room' => $termin->getLocation(),
                'info' => [
                    _('Kategorie') => $termin->toStringCategories(),
                    _('Priorität') => $termin->toStringPriority(),
                    _('Sichtbarkeit') => $termin->toStringAccessibility(),
                    _('Wiederholung') => $termin->toStringRecurrence()]
            ];
        }
    }

}
