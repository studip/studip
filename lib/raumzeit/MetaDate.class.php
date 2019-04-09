<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// MetaDate.class.php
//
// Repräsentiert die Zeit- und Turnusdaten einer Veranstaltung
//
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


/**
 * MetaDate.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     28. Juni 2007
 * @access      protected
 * @package     raumzeit
 */
class MetaDate
{
    var $seminar_id          = '';
    var $seminarStartTime    = 0;
    var $seminarDurationTime = 0;
    var $cycles              = [];

    /**
     * Constructor
     * @param string $seminar_id
     */
    function __construct($seminar_id = '')
    {
        if ($seminar_id != '') {
            $this->seminar_id = $seminar_id;
            $this->restore();
        }

    }

    /**
     * art is no longer used and always 1
     *
     * @deprecated
     * @return number
     */
    function getArt()
    {
        return 1;
    }

    /**
     * returns start week (Semesterwoche) for a cycledate
     * for compatibility the first cycledate is chosen if no one is specified
     *
     * @deprecated
     * @param string id of cycledate
     * @return int
     */
    function getStartWoche($metadate_id = null)
    {
        if ($metadate_id) {
            return $this->cycles[$metadate_id]->week_offset;
        } else {
            $first_metadate = $this->getFirstMetadate();
            return $first_metadate ? $first_metadate->week_offset : null;
        }
    }

    /**
     * sets start week (Semesterwoche) for a cycledate
     * for compatibility the first cycledate is chosen if no one is specified
     *
     * @deprecated
     * @param int    $start_woche
     * @param string $metadate_id
     * @return null|Ambigous <NULL, unknown>
     */
    function setStartWoche($start_woche, $metadate_id = null)
    {
        if ($metadate_id) {
            return $this->cycles[$metadate_id]->week_offset = $start_woche;
        } else {
            $first_metadate = $this->getFirstMetadate();
            return $first_metadate ? $first_metadate->week_offset = $start_woche : null;
        }
    }

    /**
     * returns first cycledate
     *
     * @return CycleData
     */
    function getFirstMetadate()
    {
        $first_metadate_id = array_shift(array_keys($this->cycles));
        return $first_metadate_id ? $this->cycles[$first_metadate_id] : null;
    }

    /**
     * returns the cycle for a cycledate
     * for compatibility the first cycledate is chosen if no one is specified
     *
     * @deprecated
     * @param string $metadate_id
     * @return int 0,1,2 for weekly, biweekly ...
     */
    function getTurnus($metadate_id = null)
    {
        if ($metadate_id) {
            return $this->cycles[$metadate_id]->cycle;
        } else {
            $first_metadate = $this->getFirstMetadate();
            return $first_metadate ? $first_metadate->cycle : null;
        }
    }

    /**
     * set the cycle for a cycledate
     * for compatibility the first cycledate is chosen if no one is specified
     *
     * @deprecated
     * @param        int 0,1,2 for weekly, biweekly ...
     * @param string $metadate_id
     * @return int
     */
    function setTurnus($turnus, $metadate_id = null)
    {
        if ($metadate_id) {
            return $this->cycles[$metadate_id]->cycle = $turnus;
        } else {
            $first_metadate = $this->getFirstMetadate();
            return $first_metadate ? $first_metadate->cycle = $turnus : null;
        }
    }

    function setSeminarStartTime($start)
    {
        $this->seminarStartTime = $start;
    }

    function setSeminarDurationTime($duration)
    {
        $this->seminarDurationTime = $duration;
    }

    function getSeminarID()
    {
        return $this->seminar_id;
    }

    /**
     * internal method to apply cycledate data from assoc array to a given
     * CycleData object. checks the start and endtime and retruns false if wrong
     *
     * @deprecated
     * @param           array assoc, see CycleData, metadate_id must be in $data['cycle_id']
     * @param CycleData $cycle
     * @return boolean
     */
    function setCycleData($data = [], $cycle)
    {
        $cycle->seminar_id = $this->getSeminarId();
        if ($last_one = array_pop(array_keys($this->cycles))) {
            $cycle->sorter = $this->cycles[$last_one]->sorter > 0 ? $this->cycles[$last_one]->sorter + 1 : 0;
        }
        if ($cycle->getDescription() != $data['description']) {
            $cycle->setDescription($data['description']);
        }

        if (isset($data['weekday'])) $cycle->weekday = (int)$data['weekday'];
        if (isset($data['week_offset'])) $cycle->week_offset = (int)$data['week_offset'];
        if (isset($data['cycle'])) $cycle->cycle = (int)$data['cycle'];
        if (isset($data['sws'])) $cycle->sws = $data['sws'];
        if (isset($data['endWeek'])) $cycle->end_offset = (int)$data['endWeek'];
        if (isset($data['day']) && isset($data['start_stunde']) && isset($data['start_minute']) && isset($data['end_stunde']) && isset($data['end_minute'])) {

            if (
                ($data['start_stunde'] > 23) || ($data['start_stunde'] < 0) ||
                ($data['end_stunde'] > 23) || ($data['end_stunde'] < 0) ||
                ($data['start_minute'] > 59) || ($data['start_minute'] < 0) ||
                ($data['end_minute'] > 59) || ($data['end_minute'] < 0)
            ) {
                return FALSE;
            }

            if (mktime((int)$data['start_stunde'], (int)$data['start_minute']) < mktime((int)$data['end_stunde'], (int)$data['end_minute'])) {
                $cycle->setDay($data['day']);
                $cycle->setStart($data['start_stunde'], $data['start_minute']);
                $cycle->setEnd($data['end_stunde'], $data['end_minute']);
                return TRUE;
            }
        }

        return FALSE;
    }


    /**
     * adds a new cycledate, single dates are created if second param is true
     *
     * @param      array assoc, see CycleData, metadate_id must be in $data['cycle_id']
     * @param bool $create_single_dates
     * @return string|boolean metadate_id of created cycle
     */
    function addCycle($data = [], $create_single_dates = true)
    {
        $data['day'] = (int)$data['day'];
        $data['start_stunde'] = (int)$data['start_stunde'];
        $data['start_minute'] = (int)$data['start_minute'];
        $data['end_stunde'] = (int)$data['end_stunde'];
        $data['end_minute'] = (int)$data['end_minute'];

        $cycle = new CycleData();

        if ($this->setCycleData($data, $cycle)) {
            $this->cycles[$cycle->getMetadateID()] = $cycle;
            $this->sortCycleData();
            if ($create_single_dates) $this->createSingleDates($cycle->getMetadateID());
            return $cycle->getMetadateID();
        }
        return FALSE;
    }

    /**
     * change existing cycledate, changes also corresponding single dates
     *
     * @param array assoc, see CycleData, metadate_id must be in $data['cycle_id']
     * @return number|boolean
     */
    function editCycle($data = [])
    {
        $cycle = $this->cycles[$data['cycle_id']];
        $new_start = mktime((int)$data['start_stunde'], (int)$data['start_minute']);
        $new_end = mktime((int)$data['end_stunde'], (int)$data['end_minute']);
        $old_start = mktime($cycle->getStartStunde(), $cycle->getStartMinute());
        $old_end = mktime($cycle->getEndStunde(), $cycle->getEndMinute());

        if (($new_start >= $old_start) && ($new_end <= $old_end) && ($data['day'] == $cycle->day) && ($data['endWeek'] == $cycle->end_offset)) {
            // Zeitraum wurde verkuerzt, Raumbuchungen bleiben erhalten...
            if ($this->setCycleData($data, $cycle)) {
                $termine = $cycle->getSingleDates();
                foreach ($termine as $key => $val) {
                    $tos = $val->getStartTime();
                    $toe = $val->getEndTime();
                    if ($toe > time()) {
                        $t_start = mktime((int)$data['start_stunde'], (int)$data['start_minute'], 0, date('m', $tos), date('d', $tos), date('Y', $tos));
                        $t_end = mktime((int)$data['end_stunde'], (int)$data['end_minute'], 0, date('m', $toe), date('d', $toe), date('Y', $toe));
                        $termine[$key]->setTime($t_start, $t_end);
                        $termine[$key]->store();
                    } else {
                        unset($termine[$key]);
                    }
                }
                $this->sortCycleData();
            }
            return sizeof($termine);
        } else {
            if ($this->setCycleData($data, $cycle)) {
                // collect all existing themes (issues) for this cycle:
                $issues = [];
                $issue_objects = [];
                $singledate_count = 0;

                // loop through the single dates and look for themes (issues)
                $termine = $cycle->getSingleDates();
                foreach ($termine as $key => $termin) {
                    // get all isues of this date ( zero, one, or more, if the expert view is activated)
                    // and store them at the relative position of this single date
                    $issues[$singledate_count] = $termin->getIssueIDs();
                    $singledate_count++;
                }
                // remove all SingleDates in the future for this CycleData
                $count = CycleDataDB::deleteNewerSingleDates($data['cycle_id'], time(), true);
                // create new SingleDates
                $this->createSingleDates(['metadate_id'         => $cycle->getMetaDateId(),
                                               'startAfterTimeStamp' => time()
                ]);

                // clear all loaded SingleDates so no odd ones remain. The Seminar-Class will load them fresh when needed
                $cycle->termine = NULL;

                // read all new single dates
                $termine = $cycle->getSingleDates();

                // new dates counter
                $new_singledate_count = 0;

                // loop through the single dates and add the themes (issues)
                foreach ($termine as $key => $termin) {
                    // check, if there are issues for this single date
                    if ($issues[$new_singledate_count] != NULL) {
                        // add all issues:
                        foreach ($issues[$new_singledate_count] as $issue_key => $issue_id) {
                            $termin->addIssueID($issue_id);
                            $termin->store();
                        }
                    }
                    unset($issues[$new_singledate_count]);
                    $new_singledate_count++;
                }

                // delete issues, that are not assigned to a single date because of to few dates
                // (only if the schedule expert view is off)
                if (!Config::get()->RESOURCES_ENABLES_EXPERT_SCHEDULE_VIEW) {
                    if ($new_singledate_count < $singledate_count) {
                        for ($i = $new_singledate_count; $i < $singledate_count; $i++) {
                            if ($issues[$i] != NULL) {
                                foreach ($issues[$i] as $issue_id) {
                                    // delete this issue
                                    IssueDB::deleteIssue($issue_id);
                                }
                            }
                        }
                    }
                }
                $this->sortCycleData();
                return $count;
            }
        }
        return FALSE;
    }

    /**
     * completey remove cycledate
     * @see CycleData::delete()
     * @param string $cycle_id
     * @return boolean
     */
    function deleteCycle($cycle_id)
    {
        $this->cycles[$cycle_id]->delete();
        unset ($this->cycles[$cycle_id]);
        return TRUE;
    }

    function deleteSingleDate($cycle_id, $date_id, $filterStart, $filterEnd)
    {
        $this->cycles[$cycle_id]->deleteSingleDate($date_id, $filterStart, $filterEnd);
    }

    function unDeleteSingleDate($cycle_id, $date_id, $filterStart, $filterEnd)
    {
        return $this->cycles[$cycle_id]->unDeleteSingleDate($date_id, $filterStart, $filterEnd);
    }

    /**
     * store all changes to cycledates for the course, removed cycles are deleted from database
     * @return int > 0 if changes where made
     */
    function store()
    {
        $old_cycle_dates = [];
        foreach (SeminarCycleDate::findBySeminar($this->seminar_id) as $c) {
            $old_cycle_dates[$c->getId()] = $c;
        }
        $removed = array_diff(array_keys($old_cycle_dates), array_keys($this->cycles));
        foreach ($removed as $one) {
            $changed += $old_cycle_dates[$one]->delete();
        }
        foreach ($this->cycles as $one) {
            $changed += $one->storeCycleDate();
        }
        $this->sortCycleData();
        return $changed;
    }


    /**
     * load all cycledates from database
     */
    function restore()
    {
        $this->cycles = [];
        foreach (SeminarCycleDate::findBySeminar($this->seminar_id) as $c) {
            $this->cycles[$c->getId()] = new CycleData($c);
        }
        $this->sortCycleData();
    }

    function delete($removeSingleDates = TRUE)
    {
        //TODO: Löschen eines MetaDate-Eintrages (CycleData);
    }

    /**
     * sort cycledates by sorter column and date
     */
    function sortCycleData()
    {
        uasort($this->cycles, function ($a, $b) {
            return $a->sorter - $b->sorter
                ?: $a->weekday - $b->weekday
                ?: $a->start_hour - $b->start_hour;
        });
    }

    /**
     * returns cycledates as arrays
     *
     * @param bool $show_invisibles if cycles without dates should
     *                              be in the array, defaults to false
     * @return array assoc of cycledate data arrays
     */
    function getCycleData($show_invisibles = false)
    {
        $ret = [];

        foreach ($this->cycles as $val) {
            if ($val->is_visible || $show_invisibles) {
                $ret[$val->getMetaDateID()] = $val->toArray();
            }
        }
        return $ret;
    }

    /**
     * returns the cycledate objects
     * @return array of CycleData objects
     */
    function getCycles()
    {
        return $this->cycles;
    }

    /**
     * returns an array of SingleDate objects corresponding to the given cycle id
     * use the optional params to specify a timerange
     *
     * @param string a cycle id
     * @param int    unix timestamp
     * @param int    unix timestamp
     * @return array of SingleDate objects
     */
    function getSingleDates($metadate_id, $filterStart = 0, $filterEnd = 0)
    {
        if (!$this->cycles[$metadate_id]->termine) {
            $this->readSingleDates($metadate_id, $filterStart, $filterEnd);
        }

        return $this->cycles[$metadate_id]->termine;
    }

    /**
     * reload SingleDate objects for a given cycle id
     *
     * @param string $metadate_id
     * @param int    $start
     * @param int    $end
     * @return bool
     */
    function readSingleDates($metadate_id, $start = 0, $end = 0)
    {
        return $this->cycles[$metadate_id]->readSingleDates($start, $end);
    }

    /**
     * returns true if a given cycle has at least one date at all or in the given time range
     *
     * @param     string cycle id
     * @param int $filterStart
     * @param int $filterEnd
     * @return bool
     */
    function hasDates($metadate_id, $filterStart = 0, $filterEnd = 0)
    {
        if (!isset($this->hasDatesTmp[$metadate_id])) {
            $this->hasDatesTmp[$metadate_id] = MetaDateDB::has_dates($metadate_id, $this->getSeminarID(), $filterStart, $filterEnd);
        }

        return $this->hasDatesTmp[$metadate_id];
    }

    /**
     * create single dates for one cycle and all semester and store them in database, deleting obsolete ones
     *
     * @param mixed cycle id (string) or array with 'metadate_id' => string cycle id, 'startAfterTimeStamp' => int timestamp to override semester start
     */
    function createSingleDates($data)
    {
        foreach ($this->getVirtualSingleDates($data) as $semester_id => $dates_for_semester) {
            list($dates, $dates_to_delete) = array_values($dates_for_semester);
            foreach ($dates_to_delete as $d) $d->delete();
            foreach ($dates as $d) {
                if ($d->isUpdate()) continue; //vorhandene Termine nicht speichern wg. chdate
                $d->store();
            }
        }
        //das sollte nicht nötig sein, muss aber erst genauer untersucht werden
        $this->store();
        $this->restore();
    }

    /**
     * generate single date objects for one cycle and all semester, existing dates are merged in
     *
     * @param mixed cycle id (string) or array with 'metadate_id' => string cycle id, 'startAfterTimeStamp' => int timestamp to override semester start
     * @return array array of arrays, for each semester id  an array of two arrays of SingleDate objects: 'dates' => all new and surviving dates, 'dates_to_delete' => obsolete dates
     */
    function getVirtualSingleDates($data)
    {
        if (is_array($data)) {
            $metadate_id = $data['metadate_id'];
            $startAfterTimeStamp = $data['startAfterTimeStamp'];
        } else {
            $metadate_id = $data;
            $startAfterTimeStamp = 0;
        }

        $ret = [];

        $all_semester = SemesterData::getAllSemesterData();

        // get the starting-point for creating singleDates for the choosen cycleData
        foreach ($all_semester as $val) {
            if (($this->seminarStartTime >= $val["beginn"]) && ($this->seminarStartTime <= $val["ende"])) {
                $sem_begin = mktime(0, 0, 0, date("n", $val["vorles_beginn"]), date("j", $val["vorles_beginn"]), date("Y", $val["vorles_beginn"]));
            }
        }

        // get the end-point
        if ($this->seminarDurationTime == -1) {
            foreach ($all_semester as $val) {
                $sem_end = $val['vorles_ende'];
            }
        } else {
            $i = 0;
            foreach ($all_semester as $val) {
                $i++;
                $timestamp = $this->seminarDurationTime + $this->seminarStartTime;
                if (($timestamp >= $val['beginn']) && ($timestamp <= $val['ende'])) {
                    $sem_end = $val["vorles_ende"];
                }
            }
        }

        $passed = false;
        foreach ($all_semester as $val) {
            if ($sem_begin <= $val['vorles_beginn']) {
                $passed = true;
            }
            if ($passed && ($sem_end >= $val['vorles_ende']) && ($startAfterTimeStamp <= $val['ende'])) {
                // correction calculation, if the semester does not start on monday
                $dow = date("w", $val['vorles_beginn']);
                if ($dow <= 5)
                    $corr = ($dow - 1) * -1;
                elseif ($dow == 6)
                    $corr = 2;
                elseif ($dow == 0)
                    $corr = 1;
                else
                    $corr = 0;
                $ret[$val['semester_id']] = $this->getVirtualSingleDatesForSemester($metadate_id, $val['vorles_beginn'], $val['vorles_ende'], $startAfterTimeStamp, $corr);
            }
        }

        return $ret;
    }

    /**
     * generate single date objects for one cycle and one semester, existing dates are merged in
     *
     * @param string cycle id
     * @param int    timestamp of semester start
     * @param int    timestamp of semester end
     * @param int    alternative timestamp to start from
     * @param int    correction calculation, if the semester does not start on monday (number of days?)
     * @return array returns an array of two arrays of SingleDate objects: 'dates' => all new and surviving dates, 'dates_to_delete' => obsolete dates
     */
    function getVirtualSingleDatesForSemester($metadate_id, $sem_begin, $sem_end, $startAfterTimeStamp, $corr)
    {
        $dates = [];
        $dates_to_delete = [];

        // loads the singledates of the by metadate_id denoted regular time-entry into the object
        $this->readSingleDates($metadate_id);

        // The currently existing singledates for the by metadate_id denoted  regular time-entry
        $existingSingleDates =& $this->cycles[$metadate_id]->getSingleDates();

        $start_woche = $this->cycles[$metadate_id]->week_offset;
        $end_woche = $this->cycles[$metadate_id]->end_offset;

        $turnus = $this->cycles[$metadate_id]->cycle;

        // This variable is used to check if a given singledate shall be created in a bi-weekly seminar.
        if ($start_woche == -1) $start_woche = 0;

        $week = 0;

        // get the first presence date after sem_begin
        $day_of_week = date('l', strtotime('Sunday + ' . $this->cycles[$metadate_id]->day . ' days'));
        $stamp = strtotime('this week ' . $day_of_week, $sem_begin);

        $start_time = mktime(
            (int)$this->cycles[$metadate_id]->start_stunde,     // Hour
            (int)$this->cycles[$metadate_id]->start_minute,     // Minute
            0,                                                  // Second
            date("n", $stamp),                                  // Month
            date("j", $stamp),                                  // Day
            date("Y", $stamp));                                 // Year

        $end_time = mktime(
            (int)$this->cycles[$metadate_id]->end_stunde,       // Hour
            (int)$this->cycles[$metadate_id]->end_minute,       // Minute
            0,                                                  // Second
            date("n", $stamp),                                  // Month
            date("j", $stamp),                                  // Day
            date("Y", $stamp));                                 // Year

        // loop through all possible singledates for this regular time-entry
        do {

            // if dateExists is true, the singledate will not be created. Default is of course to create the singledate
            $dateExists = false;

            // do not create singledates if they are earlier than the semester start
            if ($end_time < $sem_begin) $dateExists = true;

            // do not create singledates, if they are earlier then the chosen start-week
            if ($start_woche > $week || (isset($end_woche) && $week > $end_woche)) $dateExists = true;

            // bi-weekly check
            if ($turnus > 0 && ($week - $start_woche) > 0 && (($week - $start_woche) % ($turnus + 1))) {
                $dateExists = true;
            }

            /*
             * We only create dates, which do not already exist, so we do not overwrite existing dates.
             *
             * Additionally, we delete singledates which are not needed any more (bi-weekly, changed start-week, etc.)
             */
            foreach ($existingSingleDates as $key => $val) {
                // take only the singledate into account, that maps the current timepoint
                if ($start_time > $startAfterTimeStamp && ($val->date == $start_time) && ($val->end_time == $end_time)) {

                    // bi-weekly check
                    if ($turnus > 0 && ($week - $start_woche) > 0 && (($week - $start_woche) % ($turnus + 1))) {
                        $dates_to_delete[$key] = $val;
                        unset($existingSingleDates[$key]);
                    }

                    // delete singledates if they are earlier than the chosen start-week
                    if ($start_woche > $week || (isset($end_woche) && $week > $end_woche)) {
                        $dates_to_delete[$key] = $val;
                        unset($existingSingleDates[$key]);
                    }

                    $dateExists = true;
                    if (isset($existingSingleDates[$key])) {
                        $dates[$key] = $val;
                    }
                }
            }

            if ($start_time < $startAfterTimeStamp) {
                $dateExists = true;
            }

            if (!$dateExists) {

                $termin = new SingleDate(['seminar_id' => $this->seminar_id]);

                $all_holiday = SemesterHoliday::getAll(); // fetch all Holidays
                foreach ($all_holiday as $val2) {
                    if (($val2["beginn"] <= $start_time) && ($start_time <= $val2["ende"])) {
                        $termin->setExTermin(true);
                    }
                }

                //check for calculatable holidays
                if (!$termin->isExTermin()) {
                    $holy_type = holiday($start_time);
                    if ($holy_type["col"] == 3) {
                        $termin->setExTermin(true);
                    }
                }

                // fill the singleDate-Object with data
                $termin->setMetaDateID($metadate_id);
                $termin->setTime($start_time, $end_time);
                $termin->setDateType(1); //best guess

                $dates[$termin->getTerminID()] = $termin;
            }

            //inc the week, create timestamps for the next singledate
            $start_time = strtotime('+1 week', $start_time);
            $end_time = strtotime('+1 week', $end_time);

            $week++;

        } while ($end_time < $sem_end);

        return ['dates' => $dates, 'dates_to_delete' => $dates_to_delete];
    }

    /**
     * returns an array of AssignObjects for one cycle for given room
     * assigns are not stored, used for collision checks before the cycle is stored
     * (for now only in admin_seminare_assi.php)
     *
     * @param string id of cycle
     * @param string id of room
     * @return array array of AssignObject
     */
    function getVirtualMetaAssignObjects($metadate_id, $resource_id)
    {
        $ret = [];
        foreach ($this->getVirtualSingleDates($metadate_id) as $semester_id => $dates_for_semester) {
            list($dates, $dates_to_delete) = array_values($dates_for_semester);
            foreach ($dates as $d) {
                if (!$d->isExTermin()) {
                    $ao = new AssignObject(null);
                    $ao->setResourceId($resource_id);
                    $ao->setBegin($d->getStartTime());
                    $ao->setEnd($d->getEndTime());
                    $ao->setRepeatEnd($d->getEndTime());
                    $ao->setRepeatQuantity(0);
                    $ao->setRepeatInterval(0);
                    $ao->setRepeatMonthOfYear(0);
                    $ao->setRepeatDayOfMonth(0);
                    $ao->setRepeatWeekOfMonth(0);
                    $ao->setRepeatDayOfWeek(0);
                    $ret[] = $ao;
                }
            }
        }
        return $ret;
    }
}
