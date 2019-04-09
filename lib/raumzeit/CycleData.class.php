<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// CycleData.class.php
//
// Repräsentiert ein Turnusdatum eines MetaDates
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
 * This class is subject to change, for now it wraps getter
 * and setter to SeminarCycleDate. For compatibility reasons it has
 * magic __get() __set() __isset, and it combines the old metadata_dates
 * keys and the new fields from SeminarCycleDate (see CycleData::$alias)
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */
class CycleData
{
    /**
     * list of aliases to translate old style metadata_dates keys to
     * new fields of SeminarCycleDate
     *
     * @var array
     */
    private $alias = [
        'start_stunde' => 'start_hour',
        'end_stunde'   => 'end_hour',
        'day'          => 'weekday',
        'desc'         => 'description',
        'is_visible'   => 'is_visible'
    ];

    /**
     * this is mostly filtered, see readSingleDates()
     * should not be public
     *
     * @var array of SingleDate
     */
    public $termine = NULL; // Array

    /**
     * Enter description here ...
     * @var SeminarCycleDate
     */
    private $cycle_date = null;

    /**
     * Constructor
     * @param SeminarCycleDate|array
     */
    function __construct($cycle_data = FALSE)
    {
        if ($cycle_data instanceof SeminarCycleDate) {
            $this->cycle_date = $cycle_data;
        } else {
            if ($cycle_data['metadate_id']) {
                $metadate_id = $cycle_data['metadate_id'];
            } else {
                $metadate_id = md5(uniqid('metadate_id'));
            }
            $this->cycle_date = new SeminarCycleDate($metadate_id);
            $this->setStart($cycle_data['start_stunde'], $cycle_data['start_minute']);
            $this->setEnd($cycle_data['end_stunde'], $cycle_data['end_minute']);
            $this->setDay($cycle_data['day']);
            $this->setDescription($cycle_data['desc']);
        }
    }

    function getDescription()
    {
        return $this->cycle_date->description;
    }

    function getCycleDate() {
        return $this->cycle_date;
    }
    function setDescription($description)
    {
        $this->cycle_date->description = $description;
    }

    function setStart($start_stunde, $start_minute)
    {
        $this->cycle_date->start_hour = (int)$start_stunde;
        $this->cycle_date->start_minute = (int)$start_minute;
    }

    function setEnd($end_stunde, $end_minute)
    {
        $this->cycle_date->end_hour = (int)$end_stunde;
        $this->cycle_date->end_minute = (int)$end_minute;
    }

    function getStartStunde ()
    {
        return $this->cycle_date->start_hour;
    }

    function getStartMinute ()
    {
        return $this->cycle_date->start_minute;
    }

    function getEndStunde ()
    {
        return $this->cycle_date->end_hour;
    }

    function getEndMinute ()
    {
        return $this->cycle_date->end_minute;
    }

    function getMetaDateID()
    {
        return $this->cycle_date->getId();
    }

    function getDay()
    {
        return $this->cycle_date->weekday;
    }

    function getStartTime()
    {
        return sprintf('%02d:%02d',$this->getStartStunde(), $this->getStartMinute());
    }

    function getEndTime()
    {
        return sprintf('%02d:%02d',$this->getEndStunde(), $this->getEndMinute());
    }

    function setDay($day)
    {
        $this->cycle_date->weekday = $day;
    }

    /**
     * Check if there is a least one not cancelled date for this cycle data
     *
     * @return bool   true, if there is at least one not cancelled date
     */
    function getIsVisible()
    {
        return $this->cycle_date->is_visible;
    }

    function __get($field)
    {
        if(isset($this->alias[$field])) {
            $field = $this->alias[$field];
        }
        return $this->cycle_date->$field;
    }

    function __set($field, $value)
    {
        if(isset($this->alias[$field])) {
            $field = $this->alias[$field];
        }
        return $this->cycle_date->$field = $value;
    }

    function __isset($field)
    {
        if(isset($this->alias[$field])) {
            $field = $this->alias[$field];
        }
        return isset($this->cycle_date->$field);
    }

    /**
     * stores only the cycledate data
     *
     * @return boolean
     */
    function storeCycleDate()
    {
        if (!$this->description) $this->description = '';
        return $this->cycle_date->store();
    }

    /**
     * stores the single dates belonging to this cycledate,
     * but only the ones which are currently loaded!
     * (see readSingleDates())
     * should be private
     *
     * @return boolean
     */
    function store()
    {
        foreach ($this->termine as $val) {
            $val->store();
        }

        return TRUE;
    }

    /**
     * refreshes the currently loaded single dates from database,
     * does not reload cycledate data!
     * should be private
     *
     * @return boolean
     */
    function restore()
    {
        foreach ($this->termine as $key => $val) {
            $new_termine[$key] = $val->restore();
        }
        $this->termine =& $new_termine;
        return TRUE;
    }

    /**
     * deletes cycledate and corresponding single dates
     *
     * @param boolean $removeSingles
     * @return boolean
     */
    function delete($removeSingles = TRUE)
    {
        if ($removeSingles) {
            if (!$this->termine) {
                $this->readSingleDates();
            }
            foreach ($this->termine as $termin) {
                $termin->delete();
            }
        }
        return $this->cycle_date->delete();
    }

    /**
     * this does not delete a single date, but set it to be marked
     * as to not take place. do not use!
     *
     * @deprecated
     * @param sting $date_id
     * @param int $filterStart
     * @param int $filterEnd
     */
    function deleteSingleDate($date_id, $filterStart, $filterEnd)
    {
        if (!$this->termine) {
            $this->readSingleDates($filterStart, $filterEnd);
        }

        $this->termine[$date_id]->setExTermin(true);
        $this->termine[$date_id]->store();
    }

    /**
     * this should ressurect a single date whis is marked
     * as to not take place. do not use!
     *
     * @deprecated
     * @param sting $date_id
     * @param int $filterStart
     * @param int $filterEnd
     */
    function unDeleteSingleDate($date_id, $filterStart, $filterEnd)
    {
        if (!$this->termine) {
            $this->readSingleDates($filterStart, $filterEnd);
        }

        if (!$this->termine[$date_id]->isExTermin()) {
            return false;
        }

        $this->termine[$date_id]->setExTermin(false);
        $this->termine[$date_id]->store();
        return true;
    }

    /**
     * load corresponding single dates from database
     * give timestamps as params to filter by time range
     *
     * @param int $start
     * @param int $end
     * @return boolean
     */
    function readSingleDates($start = 0, $end = 0)
    {
        $this->termine = [];
        $termin_data = CycleDataDB::getTermine($this->metadate_id, $start, $end);
        if ($termin_data) {
            foreach ($termin_data as $val) {
                unset($termin);
                $termin = new SingleDate();
                $termin->fillValuesFromArray($val);
                $termin->setExTermin($val['ex_termin']);
                $this->termine[$val['termin_id']] = $termin;
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * get the currently loaded single dates, or all when no dates
     * are loaded. you must use readSingleDates() before to be shure what to get!
     *
     * @return array of SingleDate
     */
    function getSingleDates()
    {
        if (!$this->termine) {
            $this->readSingleDates();
        }
        return $this->termine;
    }

    /**
     * returns an assoc array, keys are room names values are number of dates for this room
     * give timestamps as params to filter by time range
     *
     * @param int $filterStart
     * @param int $filterEnd
     * @return array
     */
    function getFreeTextPredominantRoom($filterStart = 0, $filterEnd = 0)
    {
        if ($room = CycleDataDB::getFreeTextPredominantRoomDB($this->metadate_id, $filterStart, $filterEnd)) {
            return $room;
        }
    }

    /**
     * returns an assoc array, keys are resource_id of rooms values are number of dates for this room
     * give timestamps as params to filter by time range
     *
     * @param int $filterStart
     * @param int $filterEnd
     * @return array
     */
    function getPredominantRoom($filterStart = 0, $filterEnd = 0)
    {
        if ($rooms = CycleDataDB::getPredominantRoomDB($this->metadate_id, $filterStart, $filterEnd)) {
            return $rooms;
        }

        return false;
    }

    /**
     * returns a formatted string for cycledate
     *
     * @see SeminarCycleDate::toString()
     * @param boolean $short
     * @return string
     */
    function toString($short = false)
    {
        if($short === false) {
            return $this->cycle_date->toString('long');
        } else if ($short === true) {
            return $this->cycle_date->toString('short');
        } else {
            return $this->cycle_date->toString($short);
        }
    }

    /**
     * return all fields from SeminarCycleDate and old style
     * metadata_dates, combined with info about rooms
     *
     * @return array
     */
    function toArray()
    {
        $ret = $this->cycle_date->toArray();
        foreach($this->alias as $a => $o) {
            $ret[$a] = $this->cycle_date->$o;
        }
        $ret['assigned_rooms'] = $this->getPredominantRoom();
        $ret['freetext_rooms'] = $this->getFreetextPredominantRoom();
        $ret['tostring']       = $this->toString();
        $ret['tostring_short'] = $this->toString(true);

        $ret['start_minute'] = leadingZero($ret['start_minute']);
        $ret['end_minute'] = leadingZero($ret['end_minute']);
        return $ret;
    }

    /**
     * assign single dates one by one to a list of issues
     * seems not to be the right place for this method
     *
     * @deprecated
     * @param array $themen
     * @param int $filterStart
     * @param int $filterEnd
     */
    function autoAssignIssues($themen, $filterStart, $filterEnd)
    {
        $this->readSingleDates($filterStart, $filterEnd);
        $z = 0;
        foreach ($this->termine as $key => $val) {
            if (sizeof($val->getIssueIDs()) == 0) {
                if (!$themen[$z]) break;
                if (!$val->isExTermin()) {
                    $this->termine[$key]->addIssueID($themen[$z++]);
                }
            }
        }
        $this->store();
    }

    /**
     * use SingleDate::removeRequest()
     *
     * @deprecated
     * @param string $singledate_id
     * @param int $filterStart
     * @param int $filterEnd
     */
    function removeRequest($singledate_id, $filterStart, $filterEnd)
    {
        $this->readSingleDates($filterStart, $filterEnd);
        $this->termine[$singledate_id]->removeRequest();
    }

}
