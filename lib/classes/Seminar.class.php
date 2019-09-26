<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
 * Seminar.class.php - This class represents a Seminar in Stud.IP
 *
 * This class provides functions for seminar-members, seminar-dates, and seminar-modules
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uni-osnabrueck.de>
 * @author      Stefan Suchi <suchi@data-quest>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'lib/admission.inc.php';
require_once 'lib/dates.inc.php';

class Seminar
{
    var $issues = null;                 // Array of Issue
    var $irregularSingleDates = null;   // Array of SingleDates
    var $messages = [];            // occured errors, infos, and warnings
    var $semester = null;
    var $filterStart = 0;
    var $filterEnd = 0;
    var $hasDatesOutOfDuration = -1;
    var $message_stack = [];

    var $user_number = 0;//?
    var $commands; //?
    var $BookedRoomsStatTemp; //???

    var $request_id;//TODO
    var $requestData;
    var $room_request;

    private $_metadate = null;               // MetaDate

    private $alias = [
        'seminar_number' => 'VeranstaltungsNummer',
        'subtitle' => 'Untertitel',
        'description' => 'Beschreibung',
        'location' => 'Ort',
        'misc' => 'Sonstiges',
        'read_level' => 'Lesezugriff',
        'write_level' => 'Schreibzugriff',
        'semester_start_time' => 'start_time',
        'semester_duration_time' => 'duration_time',
        'form' => 'art',
        'participants' => 'teilnehmer',
        'requirements' => 'vorrausetzungen',
        'orga' => 'lernorga',
    ];

    private $course = null;

    private $course_set = null;

    private static $seminar_object_pool;

    public static function GetInstance($id = false, $refresh_cache = false)
    {
        if ($id) {
            if ($refresh_cache) {
                self::$seminar_object_pool[$id] = null;
            }
            if (is_object(self::$seminar_object_pool[$id]) && self::$seminar_object_pool[$id]->getId() == $id) {
                return self::$seminar_object_pool[$id];
            } else {
                self::$seminar_object_pool[$id] = new Seminar($id);
                return self::$seminar_object_pool[$id];
            }
        } else {
            return new Seminar(false);
        }
    }

    public static function setInstance(Seminar $seminar)
    {
        return self::$seminar_object_pool[$seminar->id] = $seminar;
    }

    /**
     * Constructor
     *
     * Pass nothing to create a seminar, or the seminar_id from an existing seminar to change or delete
     * @access   public
     * @param    string  $seminar_id the seminar to be retrieved
     */
    public function __construct($course_or_id = FALSE)
    {
        $course = Course::toObject($course_or_id);
        if ($course) {
            $this->course = $course;
        } elseif ($course_or_id === false) {
            $this->course = new Course();
            $this->course->setId($this->course->getNewId());
        } else { //hmhmhm
            throw new Exception(sprintf(_('Fehler: Konnte das Seminar mit der ID %s nicht finden!'), $course_or_id));
        }
    }

    public function __get($field)
    {
        if ($field == 'is_new') {
            return $this->course->isNew();
        }
        if ($field == 'metadate') {
            if ($this->_metadate === null) {
                $this->_metadate = new MetaDate($this->id);
                $this->_metadate->setSeminarStartTime($this->start_time);
                $this->_metadate->setSeminarDurationTime($this->duration_time);
            }
            return $this->_metadate;
        }
        if(isset($this->alias[$field])) {
            $field = $this->alias[$field];
        }
        return $this->course->$field;
    }

    public function __set($field, $value)
    {
        if(isset($this->alias[$field])) {
            $field = $this->alias[$field];
        }
        if ($field == 'metadate') {
            return $this->_metadate = $value;
        }
        return $this->course->$field = $value;
    }

    public function __isset($field)
    {
        if ($field == 'metadate') {
            return is_object($this->_metadate);
        }
        if(isset($this->alias[$field])) {
            $field = $this->alias[$field];
        }
        return isset($this->course->$field);
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->course, $method], $params);
    }

    public static function GetSemIdByDateId($date_id)
    {
        $stmt = DBManager::get()->prepare("SELECT range_id FROM termine WHERE termin_id = ? LIMIT 1");
        $stmt->execute([$date_id]);
        return $stmt->fetchColumn();
    }

    /**
     *
     * creates an new id for this object
     * @access   private
     * @return   string  the unique id
     */
    public function createId()
    {
        return $this->course->getNewId();
    }

    public function getMembers($status = 'dozent')
    {
        $ret = [];
        foreach($this->course->getMembersWithStatus($status) as $m) {
            $ret[$m->user_id]['user_id'] = $m->user_id;
            $ret[$m->user_id]['username'] = $m->username;
            $ret[$m->user_id]['Vorname'] = $m->vorname;
            $ret[$m->user_id]['Nachname'] = $m->nachname;
            $ret[$m->user_id]['Email'] = $m->email;
            $ret[$m->user_id]['position'] = $m->position;
            $ret[$m->user_id]['label'] = $m->label;
            $ret[$m->user_id]['status'] = $m->status;
            $ret[$m->user_id]['mkdate'] = $m->mkdate;
            $ret[$m->user_id]['fullname'] = $m->getUserFullname();
        }
        return $ret;
    }

    public function getAdmissionMembers($status = 'awaiting')
    {
        $ret = [];
        foreach($this->course->admission_applicants->findBy('status', $status)->orderBy('position nachname') as $m) {
            $ret[$m->user_id]['user_id'] = $m->user_id;
            $ret[$m->user_id]['username'] = $m->username;
            $ret[$m->user_id]['Vorname'] = $m->vorname;
            $ret[$m->user_id]['Nachname'] = $m->nachname;
            $ret[$m->user_id]['Email'] = $m->email;
            $ret[$m->user_id]['position'] = $m->position;
            $ret[$m->user_id]['status'] = $m->status;
            $ret[$m->user_id]['mkdate'] = $m->mkdate;
            $ret[$m->user_id]['fullname'] = $m->getUserFullname();
        }
        return $ret;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * return the field VeranstaltungsNummer for the seminar
     *
     * @return  string  the seminar-number for the current seminar
     */
    public function getNumber()
    {
        return $this->seminar_number;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function getInstitutId()
    {
        return $this->institut_id;
    }

    public function getSemesterStartTime()
    {
        return $this->semester_start_time;
    }

    public function getSemesterDurationTime()
    {
        return $this->semester_duration_time;
    }

    public function getNextDate($return_mode = 'string')
    {
        $next_date = '';
        if ($return_mode == 'int') {
            echo __class__.'::'.__function__.', line '.__line__.', return_mode "int" ist not supported by this function!';die;
        }

        if (!$termine = SeminarDB::getNextDate($this->id))
            return false;

        foreach ($termine['termin'] as $singledate_id) {
            $next_date .= DateFormatter::formatDateAndRoom($singledate_id, $return_mode) . '<br>';
        }

        if (!empty($termine['ex_termin'])) {
            foreach ($termine['ex_termin'] as $ex_termin_id) {
                $ex_termin = new SingleDate($ex_termin_id);

                $missing_date  = '<div style="border:1px solid black; background:#FFFFDD; display: inline-block">';
                $missing_date .= sprintf(_("Der Termin am %s findet nicht statt."),
                    DateFormatter::formatDateAndRoom($ex_termin_id, $return_mode));

                $missing_date .= '<br>' . _("Kommentar"). ': '.htmlReady($ex_termin->getComment());
                $missing_date .= '</div>';

                if (!empty($termine['termin'])) {
                    $termin = new SingleDate($termine['termin'][0]);
                    if ($ex_termin->getStartTime() <= $termin->getStartTime()) {
                        return $next_date . $missing_date;
                    } else {
                        return $next_date;
                    }
                } else {
                    return $missing_date;
                }
            }
        } else {
            return $next_date;
        }

        return false;
    }

    public function getFirstDate($return_mode = 'string') {
        if (!$dates = SeminarDB::getFirstDate($this->id)) {
            return false;
        }

        return DateFormatter::formatDateWithAllRooms(['termin' => $dates], $return_mode);
    }

    /**
     * This function returns an associative array of the dates owned by this seminar
     *
     * @returns  mixed  a multidimensional array of seminar-dates
     */
    public function getUndecoratedData($filter = false)
    {

        // Caching
        $cache = StudipCacheFactory::getCache();
        $cache_key = 'course/undecorated_data/'. $this->id;

        if ($filter) {
            $sub_key = $_SESSION['_language'] .'/'. $this->filterStart .'-'. $this->filterEnd;
        } else {
            $sub_key = $_SESSION['_language'] .'/unfiltered';
        }

        $data = unserialize($cache->read($cache_key));

        // build cache from scratch
        if (!$data || !$data[$sub_key]) {
            $cycles = $this->metadate->getCycleData();
            $dates = $this->getSingleDates($filter, $filter);
            $rooms = [];

            foreach (array_keys($cycles) as $id) {
                if ($this->filterStart && $this->filterEnd
                    && !$this->metadate->hasDates($id, $this->filterStart, $this->filterEnd))
                {
                    unset($cycles[$id]);
                    continue;
                }

                $cycles[$id]['first_date'] = CycleDataDB::getFirstDate($id);
                if (!empty($cycles[$id]['assigned_rooms'])) {
                    foreach ($cycles[$id]['assigned_rooms'] as $room_id => $count) {
                        $rooms[$room_id] += $count;
                    }
                }
            }

            // besser wieder mit direktem Query statt Objekten
            if (is_array($cycles) && count($cycles) === 0) {
                $cycles = false;
            }

            $ret['regular']['turnus_data'] = $cycles;

            // the irregular single-dates
            foreach ($dates as $val) {
                $zw = [
                    'metadate_id' => $val->getMetaDateID(),
                    'termin_id'   => $val->getTerminID(),
                    'date_typ'    => $val->getDateType(),
                    'start_time'  => $val->getStartTime(),
                    'end_time'    => $val->getEndTime(),
                    'mkdate'      => $val->getMkDate(),
                    'chdate'      => $val->getMkDate(),
                    'ex_termin'   => $val->isExTermin(),
                    'orig_ex'     => $val->isExTermin(),
                    'range_id'    => $val->getRangeID(),
                    'author_id'   => $val->getAuthorID(),
                    'resource_id' => $val->getResourceID(),
                    'raum'        => $val->getFreeRoomText(),
                    'typ'         => $val->getDateType(),
                    'tostring'    => $val->toString()
                ];

                if ($val->getResourceID()) {
                    $rooms[$val->getResourceID()]++;
                }

                $ret['irregular'][$val->getTerminID()] = $zw;
            }

            $ret['rooms'] = $rooms;
            $ret['ort']   = $this->location;

            $data[$sub_key] = $ret;

            // write data to cache
            $cache->write($cache_key, serialize($data), 600);
        }

        return $data[$sub_key];
    }

    public function getFormattedTurnus($short = FALSE)
    {
        // activate this with StEP 00077
        /* $cache = Cache::instance();
         * $cache_key = "formatted_turnus".$this->id;
         * if (! $return_string = $cache->read($cache_key))
         * {
         */
        return $this->getDatesExport(['short' => $short, 'shrink' => true]);

        // activate this with StEP 00077
        // $cache->write($cache_key, $return_string, 60*60);
        // }
    }

    public function getFormattedTurnusDates($short = FALSE)
    {
        if ($cycles = $this->metadate->getCycles()) {
            $return_string = [];
            foreach ($cycles as $id => $c) {
                $return_string[$id] = $c->toString($short);
                //hmm tja...
                if ($c->description){
                    $return_string[$id] .= ' ('. htmlReady($c->description) .')';
                }
            }
            return $return_string;
        } else
            return FALSE;
    }

    public function getMetaDateCount()
    {
        return sizeof($this->metadate->cycles);
    }

    public function getMetaDateValue($key, $value_name)
    {
        return $this->metadate->cycles[$key]->$value_name;
    }

    public function setMetaDateValue($key, $value_name, $value)
    {
        $this->metadate->cycles[$key]->$value_name = $value;
    }

    /**
     * restore the data
     *
     * the complete data of the object will be loaded from the db
     * @access   public
     * @throws   Exception  if there is no such course
     * @return   boolean    always true
     */
    public function restore()
    {
        if ($this->course->id) {
            $this->course->restore();
        }
        $this->irregularSingleDates = null;
        $this->issues = null;
        $this->_metadate = null;
        $this->course_set = null;

        return TRUE;
    }

    /**
     * returns an array of variables from the seminar-object, excluding variables
     * containing objects or arrays
     *
     * @return  array
     */
    public function getSettings() {
        return $this->course->toArray();
    }

    public function store($trigger_chdate = true)
    {
        // activate this with StEP 00077
        // $cache = Cache::instance();
        // $cache->expire("formatted_turnus".$this->id);

        //check for security consistency
        if ($this->write_level < $this->read_level) // hier wusste ein Dozent nicht, was er tat
            $this->write_level = $this->read_level;

        if ($this->irregularSingleDates) {
            foreach ($this->irregularSingleDates as $val) {
                $val->store();
            }
        }

        if ($this->issues) {
            foreach ($this->issues as $val) {
                $val->store();
            }
        }

        $metadate_changed = isset($this->metadate) ? $this->metadate->store() : 0;
        $course_changed = $this->course->store();
        if ($metadate_changed && $trigger_chdate) {
            return $this->course->triggerChdate();
        } else {
            return $course_changed ?: false;
        }
    }

    public function setStartSemester($start)
    {
        global $perm;

        if ($perm->have_perm('tutor') && $start != $this->semester_start_time) {
            // logging >>>>>>
            StudipLog::log("SEM_SET_STARTSEMESTER", $this->getId(), $start);
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
            // logging <<<<<<
            $this->semester_start_time = $start;
            $this->metadate->setSeminarStartTime($start);
            $this->createMessage(_("Das Startsemester wurde geändert."));
            $this->createInfo(_("Beachten Sie, dass Termine, die nicht mit den Einstellungen der regelmäßigen Zeit übereinstimmen (z.B. auf Grund einer Verschiebung der regelmäßigen Zeit), teilweise gelöscht sein könnten!"));
            return TRUE;
        }
        return FALSE;
    }

    public function removeAndUpdateSingleDates()
    {
        SeminarCycleDate::removeOutRangedSingleDates(
            $this->semester_start_time,
            $this->getEndSemesterVorlesEnde(),
            $this->id
        );

        foreach ($this->metadate->cycles as $key => $val) {
            $this->metadate->cycles[$key]->readSingleDates();
            $this->metadate->createSingleDates($key);
            $this->metadate->cycles[$key]->termine = NULL;
        }
        NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
    }

    public function getStartSemester()
    {
        return $this->semester_start_time;
    }

    /*
     * setEndSemester
     * @param   end integer 0 (one Semester), -1 (eternal), or timestamp of last happening semester
     * @returns TRUE on success, FALSE on failure
     */
    public function setEndSemester($end)
    {
        global $perm;

        $previousEndSemester = $this->getEndSemester();     // save the end-semester before it is changed, so we can choose lateron in which semesters we need to be rebuilt the SingleDates

        if ($end != $this->getEndSemester()) {  // only change Duration if it differs from the current one

            if ($end == 0) {                    // the seminar takes place just in the selected start-semester
                $this->semester_duration_time = 0;
                $this->metadate->setSeminarDurationTime(0);
                // logging >>>>>>
                StudipLog::log("SEM_SET_ENDSEMESTER", $this->getId(), $end, 'Laufzeit: 1 Semester');
                // logging <<<<<<
            } else if ($end == -1) {    // the seminar takes place in every semester above and including the start-semester
                // logging >>>>>>
                StudipLog::log("SEM_SET_ENDSEMESTER", $this->getId(), $end, 'Laufzeit: unbegrenzt');
                // logging <<<<<<
                $this->semester_duration_time = -1;
                $this->metadate->setSeminarDurationTime(-1);
                SeminarCycleDate::removeOutRangedSingleDates(
                    $this->semester_start_time,
                    $this->getEndSemesterVorlesEnde(),
                    $this->id
                );
            } else {                                    // the seminar takes place  between the selected start~ and end-semester
                // logging >>>>>>
                StudipLog::log("SEM_SET_ENDSEMESTER", $this->getId(), $end);
                // logging <<<<<<
                $this->semester_duration_time = $end - $this->semester_start_time;  // the duration is stored, not the real end-point
                $this->metadate->setSeminarDurationTime($this->semester_duration_time);
            }

            $this->createMessage(_("Die Dauer wurde geändert."));
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);

            /*
             * If the duration has been changed, we have to create new SingleDates
             * if the new duration is longer than the previous one
             */
            if ( ($previousEndSemester != -1) && ( ($previousEndSemester < $this->getEndSemester()) || (($previousEndSemester == 0) && ($this->getEndSemester() == -1) ) )) {
                // if the previous duration was unlimited, the only option choosable is
                // a shorter duration then 'ever', so there cannot be any new SingleDates

                // special case: if the previous selection was 'one semester' and the new one is 'eternal',
                // than we have to find out the end of the only semester, the start-semester
                if ($previousEndSemester == 0) {
                    $startAfterTimeStamp = $this->course->start_semester->ende;
                } else {
                    $startAfterTimeStamp = $previousEndSemester;
                }

                foreach ($this->metadate->cycles as $key => $val) {
                    $this->metadate->createSingleDates(['metadate_id' => $key, 'startAfterTimeStamp' => $startAfterTimeStamp]);
                    $this->metadate->cycles[$key]->termine = NULL;  // emtpy the SingleDates for each cycle, so that SingleDates, which were not in the current view, are not loaded and therefore should not be visible
                }
            }
        }

        return TRUE;
    }

    /*
     * getEndSemester
     * @returns 0 (one Semester), -1 (eternal), or TimeStamp of last Semester for this Seminar
     */
    public function getEndSemester()
    {
        if ($this->semester_duration_time == 0) return 0;                                       // seminar takes place only in the start-semester
        if ($this->semester_duration_time == -1) return -1;                                 // seminar takes place eternally
        return $this->semester_start_time + $this->semester_duration_time;  // seminar takes place between start~ and end-semester
    }

    public function getEndSemesterVorlesEnde()
    {
        if ($this->semester_duration_time == -1) {
            $very_last_semester = array_pop(Semester::getAll());
            return $very_last_semester->vorles_ende;
        }
        return $this->course->end_semester->vorles_ende;
    }

    /**
     * return the name of the seminars start-semester
     *
     * @return  string  the name of the start-semester or false if there is no start-semester
     */
    public function getStartSemesterName()
    {
        return $this->course->start_semester->name;
    }

    /**
     * return an array of singledate-objects for the submitted cycle identified by metadate_id
     *
     * @param  string  $metadate_id  the id identifying the cycle
     *
     * @return mixed   an array of singledate-objects
     */
    public function readSingleDatesForCycle($metadate_id)
    {
        return $this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
    }

    public function readSingleDates($force = FALSE, $filter = FALSE)
    {
        if (!$force) {
            if (is_array($this->irregularSingleDates)) {
                return TRUE;
            }
        }
        $this->irregularSingleDates = [];

        if ($filter) {
            $data = SeminarDB::getSingleDates($this->id, $this->filterStart, $this->filterEnd);
        } else {
            $data = SeminarDB::getSingleDates($this->id);
        }

        foreach ($data as $val) {
            unset($termin);
            $termin = new SingleDate();
            $termin->fillValuesFromArray($val);
            $this->irregularSingleDates[$val['termin_id']] =& $termin;
        }
    }

    public function &getSingleDate($singleDateID, $cycle_id = '')
    {
        if ($cycle_id == '') {
            $this->readSingleDates();
            return $this->irregularSingleDates[$singleDateID];
        } else {
            $data =& $this->metadate->getSingleDates($cycle_id, $this->filterStart, $this->filterEnd);
            return $data[$singleDateID];
        }
    }

    public function &getSingleDates($filter = false, $force = false, $include_deleted_dates = false)
    {
        $this->readSingleDates($force, $filter);
        if (!$include_deleted_dates) {
            return $this->irregularSingleDates;
        } else {
            $deleted_dates = [];
            foreach (SeminarDB::getDeletedSingleDates($this->getId(), $this->filterStart, $this->filterEnd) as $val) {
                $termin = new SingleDate();
                $termin->fillValuesFromArray($val);
                $deleted_dates[$val['termin_id']] = $termin;
            }
            $dates = array_merge($this->irregularSingleDates, $deleted_dates);
            uasort($dates, function($a,$b) {
                    if ($a->getStartTime() == $b->getStartTime()) return 0;
                    return $a->getStartTime() < $b->getStartTime() ? -1 : 1;}
            );
            return $dates;
        }
    }

    public function getCycles()
    {
        return $this->metadate->getCycles();
    }

    public function &getSingleDatesForCycle($metadate_id)
    {
        if (!$this->metadate->cycles[$metadate_id]->termine) {
            $this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
            if (!$this->metadate->cycles[$metadate_id]->termine) {
                $this->readSingleDates();
                $this->metadate->createSingleDates($metadate_id, $this->irregularSingleDates);
                $this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
            }
            //$this->metadate->readSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
        }

        return $this->metadate->getSingleDates($metadate_id, $this->filterStart, $this->filterEnd);
    }

    public function readIssues($force = false)
    {
        if (!is_array($this->issues) || $force) {
            $this->issues = [];
            $data = SeminarDB::getIssues($this->id);

            foreach ($data as $val) {
                unset($issue);
                $issue = new Issue();
                $issue->fillValuesFromArray($val);
                $this->issues[$val['issue_id']] =& $issue;
            }
        }
    }

    public function addSingleDate(&$singledate)
    {
        // logging >>>>>>
        StudipLog::log("SEM_ADD_SINGLEDATE", $this->getId(), $singledate->toString(), 'SingleDateID: '.$singledate->getTerminID());
        // logging <<<<<<

        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/'. $this->getId());

        $this->readSingleDates();
        $this->irregularSingleDates[$singledate->getSingleDateID()] =& $singledate;
        NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
        return TRUE;
    }

    public function addIssue(&$issue)
    {
        $this->readIssues();
        if ($issue instanceof Issue) {
            $max = -1;
            if (is_array($this->issues)) foreach ($this->issues as $val) {
                if ($val->getPriority() > $max) {
                    $max = $val->getPriority();
                }
            }
            $max++;
            $issue->setPriority($max);
            $this->issues[$issue->getIssueID()] =& $issue;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function deleteSingleDate($date_id, $cycle_id = '')
    {
        $this->readSingleDates();
        // logging >>>>>>
        StudipLog::log("SEM_DELETE_SINGLEDATE",$date_id, $this->getId(), 'Cycle_id: '.$cycle_id);
        // logging <<<<<<
        if ($cycle_id == '') {
            $this->irregularSingleDates[$date_id]->delete(true);
            unset ($this->irregularSingleDates[$date_id]);
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
            return TRUE;
        } else {
            $this->metadate->deleteSingleDate($cycle_id, $date_id, $this->filterStart, $this->filterEnd);
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
            return TRUE;
        }
    }

    public function cancelSingleDate($date_id, $cycle_id = '')
    {
        if ($cycle_id) {
            return $this->deleteSingleDate($date_id, $cycle_id);
        }
        $this->readSingleDates();
        // logging >>>>>>
        StudipLog::log("SEM_DELETE_SINGLEDATE",$date_id, $this->getId(), 'appointment cancelled');
        // logging <<<<<<
        $this->irregularSingleDates[$date_id]->setExTermin(true);
        $this->irregularSingleDates[$date_id]->store();
        unset ($this->irregularSingleDates[$date_id]);
        NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
        return TRUE;
    }

    public function unDeleteSingleDate($date_id, $cycle_id = '')
    {
        // logging >>>>>>
        StudipLog::log("SEM_UNDELETE_SINGLEDATE",$date_id, $this->getId(), 'Cycle_id: '.$cycle_id);
        NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
        // logging <<<<<<
        if ($cycle_id == '') {
            $termin = new SingleDate($date_id);
            if (!$termin->isExTermin()) {
                return false;
            }
            $termin->setExTermin(false);
            $termin->store();
            return true;
        } else {
            return $this->metadate->unDeleteSingleDate($cycle_id, $date_id, $this->filterStart, $this->filterEnd);
        }
    }

    /**
     * return all stacked messages as a multidimensional array
     *
     * The array has the following structure:
     *   array( 'type' => ..., 'message' ... )
     * where type is one of error, info and success
     *
     * @return mixed the array of stacked messages
     */
    public function getStackedMessages()
    {
        if ( is_array( $this->message_stack ) ) {
            $ret = [];

            // cycle through message types and set title and details appropriate
            foreach ($this->message_stack as $type => $messages ) {
                switch ( $type ) {
                    case 'error':
                        $ret['error'] = [
                            'title'   => _("Es sind Fehler/Probleme aufgetreten!"),
                            'details' => $this->message_stack['error']
                        ];
                        break;

                    case 'info':
                        $ret['info'] = [
                            'title'   => implode('<br>', $this->message_stack['info']),
                            'details' => []
                        ];
                        break;

                    case 'success':
                        $ret['success'] = [
                            'title'   => _("Ihre Änderungen wurden gespeichert!"),
                            'details' => $this->message_stack['success']
                        ];
                        break;
                }
            }

            return $ret;
        }

        return false;
    }

    /**
     * return the next stacked messag-string
     *
     * @return string a message-string
     */
    public function getNextMessage()
    {
        if ($this->messages[0]) {
            $ret = $this->messages[0];
            unset ($this->messages[0]);
            sort($this->messages);
            return $ret;
        }
        return FALSE;
    }

    /**
     * stack an error-message
     *
     * @param string $text the message to stack
     */
    public function createError($text)
    {
        $this->messages[] = 'error§'.$text.'§';
        $this->message_stack['error'][] = $text;
    }

    /**
     * stack an info-message
     *
     * @param string $text the message to stack
     */
    public function createInfo($text)
    {
        $this->messages[] = 'info§'.$text.'§';
        $this->message_stack['info'][] = $text;
    }

    /**
     * stack a success-message
     *
     * @param string $text the message to stack
     */
    public function createMessage($text)
    {
        $this->messages[] = 'msg§'.$text.'§';
        $this->message_stack['success'][] = $text;
    }

    /**
     * add an array of messages to the message-stack
     *
     * @param mixed $messages array of pre-marked message-strings
     * @param bool returns true on success
     */
    public function appendMessages( $messages )
    {
        if (!is_array($messages)) return false;

        foreach ( $messages as $type => $msgs ) {
            foreach ($msgs as $msg) {
                $this->message_stack[$type][] = $msg;
            }
        }
        return true;
    }

    public function addCycle($data = [])
    {
        $new_id = $this->metadate->addCycle($data);
        if($new_id){
            $this->setStartWeek($data['startWeek'], $new_id);
            $this->setTurnus($data['turnus'], $new_id);
        }
        // logging >>>>>>
        if($new_id){
            $cycle_info = $this->metadate->cycles[$new_id]->toString();
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
            StudipLog::log("SEM_ADD_CYCLE", $this->getId(), NULL, $cycle_info, '<pre>'.print_r($data,true).'</pre>');
        }
        // logging <<<<<<
        return $new_id;
    }

    /**
     * Change a regular timeslot of the seminar. The data is passed as an array
     * conatining the following fields:
     *   start_stunde, start_minute, end_stunde, end_minute
     *   description, turnus, startWeek, day, sws
     *
     * @param array $data the cycle-data
     *
     * @return void
     */
    public function editCycle($data = [])
    {
        $cycle = $this->metadate->cycles[$data['cycle_id']];
        $new_start = mktime($data['start_stunde'], $data['start_minute']);
        $new_end = mktime($data['end_stunde'], $data['end_minute']);
        $old_start = mktime($cycle->getStartStunde(),$cycle->getStartMinute());
        $old_end = mktime($cycle->getEndStunde(), $cycle->getEndMinute());
        $do_changes = false;

        // check, if the new timeslot exceeds the old one
        if (($new_start < $old_start) || ($new_end > $old_end) || ($data['day'] != $cycle->day) ) {
            $has_bookings = false;

            // check, if there are any booked rooms
            foreach($cycle->getSingleDates() as $singleDate) {
                if ($singleDate->getStarttime() > (time() - 3600) && $singleDate->hasRoom()) {
                    $has_bookings = true;
                    break;
                }
            }

            // if the timeslot exceeds the previous one and has some booked rooms
            // they would be lost, so ask the user for permission to do so.
            if (!$data['really_change'] && $has_bookings) {
                $link_params = [
                    'editCycle_x' => '1',
                    'editCycle_y' => '1',
                    'cycle_id' => $data['cycle_id'],
                    'start_stunde' => $data['start_stunde'],
                    'start_minute' => $data['start_minute'],
                    'end_stunde' => $data['end_stunde'],
                    'end_minute' => $data['end_minute'],
                    'day' => $data['day'],
                    'really_change' => 'true'
                ];
                $question = _("Wenn Sie die regelmäßige Zeit auf %s ändern, verlieren Sie die Raumbuchungen für alle in der Zukunft liegenden Termine!")
                    ."\n". _("Sind Sie sicher, dass Sie die regelmäßige Zeit ändern möchten?");
                $question_time = '**'. strftime('%A', $data['day']) .', '. $data['start_stunde'] .':'. $data['start_minute']
                    .' - '. $data['end_stunde'] .':'. $data['end_minute'] .'**';

                echo createQuestion(sprintf($question, $question_time), $link_params);

            } else {
                $do_changes = true;
            }
        } else {
            $do_changes = true;
        }

        $messages = false;
        $same_time = false;

        // only apply changes, if the user approved the change or
        // the change does not need any approval
        if ($do_changes) {
            if ($data['description'] != $cycle->getDescription()) {
                $this->createMessage(_("Die Beschreibung des regelmäßigen Eintrags wurde geändert."));
                $message = true;
                $do_changes = true;
            }

            if ($old_start == $new_start && $old_end == $new_end) {
                $same_time = true;
            }
            if ($data['startWeek'] != $cycle->week_offset) {
                $this->setStartWeek($data['startWeek'], $cycle->metadate_id);
                $message = true;
                $do_changes = true;
            }
            if ($data['turnus'] != $cycle->cycle) {
                $this->setTurnus($data['turnus'], $cycle->metadate_id);
                $message = true;
                $do_changes = true;
            }
            if ($data['day'] != $cycle->day) {
                $message = true;
                $same_time = false;
                $do_changes = true;
            }
            if (round(str_replace(',','.', $data['sws']),1) != $cycle->sws) {
                $cycle->sws = $data['sws'];
                $this->createMessage(_("Die Semesterwochenstunden für Dozenten des regelmäßigen Eintrags wurden geändert."));
                $message = true;
                $do_changes = true;
            }

            $change_from = $cycle->toString();
            if ($this->metadate->editCycle($data)) {
                if (!$same_time) {
                    // logging >>>>>>
                    StudipLog::log("SEM_CHANGE_CYCLE", $this->getId(), NULL, $change_from .' -> '. $cycle->toString());
                    NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
                    // logging <<<<<<
                    $this->createMessage(sprintf(_("Die regelmäßige Veranstaltungszeit wurde auf \"%s\" für alle in der Zukunft liegenden Termine geändert!"),
                        '<b>'.getWeekday($data['day']) . ', ' . $data['start_stunde'] . ':' . $data['start_minute'].' - '.
                        $data['end_stunde'] . ':' . $data['end_minute'] . '</b>'));
                    $message = true;
                }
            } else {
                if (!$same_time) {
                    $this->createInfo(sprintf(_("Die regelmäßige Veranstaltungszeit wurde auf \"%s\" geändert, jedoch gab es keine Termine die davon betroffen waren."),
                        '<b>'.getWeekday($data['day']) . ', ' . $data['start_stunde'] . ':' . $data['start_minute'].' - '.
                        $data['end_stunde'] . ':' . $data['end_minute'] . '</b>'));
                    $message = true;
                }
            }
            $this->metadate->sortCycleData();

            if (!$message) {
                $this->createInfo("Sie haben keine Änderungen vorgenommen!");
            }
        }
    }

    public function deleteCycle($cycle_id)
    {
        // logging >>>>>>
        $cycle_info = $this->metadate->cycles[$cycle_id]->toString();
        StudipLog::log("SEM_DELETE_CYCLE", $this->getId(), NULL, $cycle_info);
        NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
        // logging <<<<<<
        return $this->metadate->deleteCycle($cycle_id);
    }

    public function setTurnus($turnus, $metadate_id = false)
    {
        if ($this->metadate->getTurnus($metadate_id) != $turnus) {
            $this->metadate->setTurnus($turnus, $metadate_id);
            $key = $metadate_id ? $metadate_id : $this->metadate->getFirstMetadate()->metadate_id;
            $this->createMessage(sprintf(_("Der Turnus für den Termin %s wurde geändert."), $this->metadate->cycles[$key]->toString()));
            $this->metadate->createSingleDates($key);
            $this->metadate->cycles[$key]->termine = null;
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
        }
        return TRUE;
    }

    public function getTurnus($metadate_id = false)
    {
        return $this->metadate->getTurnus($metadate_id);
    }

    public function bookRoomForSingleDate($singleDateID, $roomID, $cycle_id = '', $append_messages = true)
    {
        if (!$roomID) {
            return false;
        }
        if ($cycle_id != '') {  // SingleDate of an MetaDate
            $this->readSingleDatesForCycle($cycle_id, $this->filterStart, $this->filterEnd);    // Let the cycle-object read in all of his single dates

            if (isset($this->metadate->cycles[$cycle_id]->termine[$singleDateID])) {
                if (!$return = $this->metadate->cycles[$cycle_id]->termine[$singleDateID]->bookRoom($roomID)) {
                    $this->appendMessages($this->metadate->cycles[$cycle_id]->termine[$singleDateID]->getMessages());
                    return false;
                }
            }
        } else {    // an irregular SingleDate
            $this->readSingleDates();

            if (isset($this->irregularSingleDates[$singleDateID])) {
                if (!$return = $this->irregularSingleDates[$singleDateID]->bookRoom($roomID)) {
                    $this->appendMessages($this->irregularSingleDates[$singleDateID]->getMessages());
                    return false;
                }
            }
        }
        return $return;
    }

    /**
     * get StatOfNotBookedRooms returns an array:
     * open:        number of rooms with no booking
     * all:         number of singleDates, which can have a booking
     * open_rooms:  array of singleDates which have no booking
     *
     * @param String $cycle_id Id of cycle
     * @return array as described above
     */
    public function getStatOfNotBookedRooms($cycle_id)
    {
        if (!isset($this->BookedRoomsStatTemp[$cycle_id])) {
            $this->BookedRoomsStatTemp[$cycle_id] = SeminarDB::getStatOfNotBookedRooms($cycle_id, $this->id, $this->filterStart, $this->filterEnd);
        }
        return $this->BookedRoomsStatTemp[$cycle_id];
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getBookedRoomsTooltip($cycle_id)
    {
        $stat = $this->getStatOfNotBookedRooms($cycle_id);
        $pattern = '%s , %s, %s-%s <br />';
        if (($stat['open'] > 0) && ($stat['open'] == $stat['all'])) {
            $return = '';
        } else if ($stat['open'] > 0) {
            $return = _('Folgende Termine haben keine Raumbuchung:') . '<br />';

            foreach ($stat['open_rooms'] as $aSingleDate) {
                $return .= sprintf($pattern,strftime('%a', $aSingleDate['date']),
                    strftime('%d.%m.%Y', $aSingleDate['date']),
                    strftime('%H:%M', $aSingleDate['date']),
                    strftime('%H:%M', $aSingleDate['end_time']));
            }
        }

        // are there any dates with declined room-requests?
        if ($stat['declined'] > 0) {
            $return .= _('Folgende Termine haben eine abgelehnte Raumanfrage') . '<br />';
            foreach ($stat['declined_dates'] as $aSingleDate) {
                $return .= sprintf($pattern,strftime('%a', $aSingleDate['date']),
                    strftime('%d.%m.%Y', $aSingleDate['date']),
                    strftime('%H:%M', $aSingleDate['date']),
                    strftime('%H:%M', $aSingleDate['end_time']));
            }
        }

        return $return;
    }

    public function getRequestsInfo($cycle_id)
    {
        $zahl =  SeminarDB::countRequestsForSingleDates($cycle_id, $this->id, $this->filterStart, $this->filterEnd);
        if ($zahl > 0) {
            return sprintf(_('%u noch offen'), $zahl);
        }
        return _('keine offen');
    }

    /**
     * @param $cycle_id
     * @return string
     */
    public function getCycleColorClass($cycle_id)
    {
        if (Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ENABLE_BOOKINGSTATUS_COLORING) {
            if (!$this->metadate->hasDates($cycle_id, $this->filterStart, $this->filterEnd)) {
                return 'red';
            }

            $stat = $this->getStatOfNotBookedRooms($cycle_id);

            if ($stat['open'] > 0 && $stat['open'] == $stat['all']) {
                return 'red';
            }
            if ($stat['open'] > 0) {
                return 'yellow ';
            }
            return 'green ';
        }

        return '';
    }

    public function &getIssues($force = false)
    {
        $this->readIssues($force);
        $this->renumberIssuePrioritys();
        if (is_array($this->issues)) {
            uasort($this->issues, function ($a, $b) {
                return $a->getPriority() - $b->getPriority();
            });
        }
        return $this->issues;
    }

    public function deleteIssue($issue_id)
    {
        $this->issues[$issue_id]->delete();
        unset($this->issues[$issue_id]);
        return TRUE;
    }

    public function &getIssue($issue_id)
    {
        $this->readIssues();
        return $this->issues[$issue_id];
    }

    /*
     * changeIssuePriority
     *
     * changes an issue with an given id to a new priority
     *
     * @param
     * issue_id             the issue_id of the issue to be changed
     * new_priority     the new priority
     */
    public function changeIssuePriority($issue_id, $new_priority)
    {
        /* REMARK:
         * This function only works, when an issue is moved ONE slote higher or lower
         * It does NOT work with ARBITRARY movements!
         */
        $this->readIssues();
        $old_priority = $this->issues[$issue_id]->getPriority();    // get old priority, so we can just exchange prioritys of two issues
        foreach ($this->issues as $id => $issue) {                              // search for the concuring issue
            if ($issue->getPriority() == $new_priority) {
                $this->issues[$id]->setPriority($old_priority);             // the concuring issue gets the old id of the changed issue
                $this->issues[$id]->store();                                                    // ###store_problem###
            }
        }

        $this->issues[$issue_id]->setPriority($new_priority);           // changed issue gets the new priority
        $this->issues[$issue_id]->store();                                              // ###store_problem###

    }

    public function renumberIssuePrioritys()
    {
        if (is_array($this->issues)) {

            $sorter = [];
            foreach ($this->issues as $id => $issue) {
                $sorter[$id] = $issue->getPriority();
            }
            asort($sorter);
            $i = 0;
            foreach ($sorter as $id => $old_priority) {
                $this->issues[$id]->setPriority($i);
                $i++;
            }
        }
    }

    public function autoAssignIssues($themen, $cycle_id)
    {
        $this->metadate->cycles[$cycle_id]->autoAssignIssues($themen, $this->filterStart, $this->filterEnd);
    }

    public function hasRoomRequest()
    {
        if (!$this->request_id) {
            $this->request_id = RoomRequest::existsByCourse($this->is);
            if (!$this->request_id) return FALSE;

            $rD = new RoomRequest($this->request_id);
            if ($rD->getClosed() != 0) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * this function returns a human-readable status of a room-request, if any, false otherwise
     *
     * the int-values of the states are:
     *  0 - room-request is open
     *  1 - room-request has been edited, but no confirmation has been sent
     *  2 - room-request has been edited and a confirmation has been sent
     *  3 - room-request has been declined
     *
     * they are mapped with:
     *  0 - open
     *  1 - pending
     *  2 - closed
     *  3 - declined
     *
     * @return string the mapped text
     */
    public function getRoomRequestStatus()
    {
        // check if there is any room-request
        if (!$this->request_id) {
            $this->request_id = getSeminarRoomRequest($this->id);

            // no room request found
            if (!$this->request_id) return FALSE;
        }

        // room-request found, parse int-status and return string-status
        if (!$this->room_request) {
            $this->room_request = new RoomRequest($this->request_id);
            if ($this->room_request->isNew()) {
                throw new Exception("Room-Request with the id {$this->request_id} does not exists!");
            }
        }

        switch ($this->room_request->getClosed()) {
            case '0'; return 'open'; break;
            case '1'; return 'pending'; break;
            case '2'; return 'closed'; break;
            case '3'; return 'declined'; break;
        }

        return FALSE;
    }

    public function applyTimeFilter($start, $end)
    {
        $this->filterStart = $start;
        $this->filterEnd = $end;
    }

    public function setFilter($timestamp)
    {
        if ($timestamp == 'all') {
            $_SESSION['raumzeitFilter'] = 'all';
            $this->applyTimeFilter(0, 0);
        } else {
            $filterSemester = SemesterData::getSemesterDataByDate($timestamp);
            $_SESSION['raumzeitFilter'] = $filterSemester['beginn'];
            $this->applyTimeFilter($filterSemester['beginn'], $filterSemester['ende']);
        }
    }

    public function registerCommand($command, $function)
    {
        $this->commands[$command] = $function;
    }

    public function processCommands()
    {
        global $cmd;

        // workaround for multiple submit-buttons with new Button-API
        foreach ($this->commands as $r_cmd => $func) {
            if (Request::submitted($r_cmd)) {
                $cmd = $r_cmd;
            }
        }

        if (!isset($cmd) && Request::option('cmd')) $cmd = Request::option('cmd');
        if (!isset($cmd)) return FALSE;

        if (isset($this->commands[$cmd])) {
            call_user_func($this->commands[$cmd], $this);
        }
    }

    public function getFreeTextPredominantRoom($cycle_id)
    {
        if (!($room = $this->metadate->cycles[$cycle_id]->getFreeTextPredominantRoom($this->filterStart, $this->filterEnd))) {
            return FALSE;
        }
        return $room;
    }

    public function getPredominantRoom($cycle_id, $list = FALSE)
    {
        if (!($rooms = $this->metadate->cycles[$cycle_id]->getPredominantRoom($this->filterStart, $this->filterEnd))) {
            return FALSE;
        }
        if ($list) {
            return $rooms;
        } else {
            return $rooms[0];
        }
    }

    public function getFormattedPredominantRooms($cycle_id, $link = true, $show = 3)
    {
        if (!($rooms = $this->metadate->cycles[$cycle_id]->getPredominantRoom($this->filterStart, $this->filterEnd))) {
            return FALSE;
        }

        $roominfo = '';

        foreach ($rooms as $key => $val) {
            // get string-representation of predominant booked rooms
            if ($key >= $show) {
                if ($show > 1) {
                    $roominfo .= ', '.sprintf(_("und %s weitere"), (sizeof($rooms)-$show));
                }
                break;
            } else {
                if ($key > 0) {
                    $roominfo .= ', ';
                }
                $resObj = ResourceObject::Factory($val);
                if ($link) {
                    $roominfo .= $resObj->getFormattedLink(TRUE, TRUE, TRUE);
                } else {
                    $roominfo .= $resObj->getName();
                }
                unset($resObj);
            }
        }
        return $roominfo;
    }

    /**
     * removes a room-request for a single date. If no cycle_id is given, the single date
     * is an irregular date of the seminar, otherwise it is a single date of a regular entry.
     *
     * @param string $singledate_id the id of the date
     * @param string $cycle_id the metadate_id of the regular entry (optional)
     *
     * @return boolean true on success
     */
    public function removeRequest($singledate_id,  $cycle_id = '')
    {
        if ($cycle_id == '') {
            $this->irregularSingleDates[$singledate_id]->removeRequest();
        } else {
            $this->metadate->cycles[$cycle_id]->removeRequest($singledate_id, $this->filterStart, $this->filterEnd);
        }
        $this->createMessage(_("Die Raumanfrage wurde zurückgezogen!"));
        return TRUE;
    }

    public function hasDatesOutOfDuration($force = false)
    {
        if ($this->hasDatesOutOfDuration == -1 || $force) {
            $this->hasDatesOutOfDuration = SeminarDB::hasDatesOutOfDuration($this->getStartSemester(), $this->getEndSemesterVorlesEnde(), $this->id);
        }
        return $this->hasDatesOutOfDuration;
    }

    public function getStartWeek($metadate_id = false)
    {
        return $this->metadate->getStartWoche($metadate_id);
    }

    public function setStartWeek($week, $metadate_id = false)
    {
        if ($this->metadate->getStartWoche($metadate_id) == $week) {
            return FALSE;
        } else {
            $this->metadate->setStartWoche($week, $metadate_id);
            $key = $metadate_id ? $metadate_id : $this->metadate->getFirstMetadate()->metadate_id;
            $this->createMessage(sprintf(_("Die Startwoche für den Termin %s wurde geändert."), $this->metadate->cycles[$key]->toString()));
            $this->metadate->createSingleDates($key);
            $this->metadate->cycles[$key]->termine = null;
            NotificationCenter::postNotification("CourseDidChangeSchedule", $this);
        }
    }

    // Funktion fuer die Ressourcenverwaltung
    public function getGroupedDates($singledate = null, $metadate = null)
    {
        $i = 0;
        $first_event = FALSE;
        $all_semester = SemesterData::getAllSemesterData();

        if (Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES) {
            // filtering
            foreach ($all_semester as $semester) {
                if ($semester['ende'] > time()) {
                    $new_as[] = $semester;
                }
            }
            $all_semester = $new_as;
        }

        if (!$singledate) {
            foreach ($all_semester as $semester) {
                foreach ($this->metadate->cycles as $metadate_id => $cycle) {
                    if ($metadate && $metadate_id != $metadate) continue;
                    $group = $cycle->getSingleDates();
                    $metadate_has_termine = 0;
                    $single = true;
                    foreach ($group as $termin) {
                        if (!$termin->isExTermin() && $termin->getStartTime() >= $semester['beginn'] && $termin->getStartTime() <= $semester['ende'] && (!Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES || $termin->getStartTime() >= time()) && $termin->isPresence()) {
                            if (empty($first_event)) {
                                $first_event = $termin->getStartTime();
                            }
                            $groups[$i]["termin_ids"][$termin->getSingleDateId()] = TRUE;
                            $metadate_has_termine = 1;

                            if (empty($info[$i]['raum'])) {
                                $info[$i]['raum'] = $termin->resource_id;
                            } else if ($info[$i]['raum'] != $termin->resource_id) {
                                $single = false;
                            }
                        }
                    }

                    if ($metadate_has_termine) {
                        $info[$i]['name'] = $cycle->toString('long').' ('.$semester['name'].')';
                        $info[$i]['export'] = $cycle->toString('long').' ('.$semester['name'].')';
                        $info[$i]['weekend'] = ($cycle->getDay() == 6 || $cycle->getDay() == 0);
                        $this->applyTimeFilter($semester['beginn'], $semester['ende']);
                        $raum = $this->getDatesTemplate('dates/seminar_predominant_html', ['cycle_id' => $metadate_id]);
                        if ($raum) {
                            $info[$i]['name'] .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;'.$raum;
                            $room_stat = $this->getStatOfNotBookedRooms($cycle->getMetadateId());
                            $info[$i]['name'] .= sprintf(_(" (%s von %s belegt)"), $room_stat['all'] - $room_stat['open'] , $room_stat['all']);
                            $info[$i]['export'] .= sprintf(_(" (%s von %s belegt)"), $room_stat['all'] - $room_stat['open'] , $room_stat['all']);
                            $groups[$i]['complete'] = ($room_stat['all'] - $room_stat['open'] >= sizeof($groups[$i]['termin_ids'])) ? true : false;
                        }
                        if (!$single) unset($info[$i]['raum']);
                        $i++;
                    }
                }
            }
            if (!$metadate) {
                $irreg = $this->getSingleDates();

                if (Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES) {
                    $anzahl = 0;
                    foreach ($irreg as $termin_id => $termin) {
                        if ($termin->getStartTime() > time() - 3600) {
                            $anzahl++;
                        }
                    }
                } else {
                    $anzahl = sizeof($irreg);
                }

                if ($anzahl > Config::get()->RESOURCES_ALLOW_SINGLE_DATE_GROUPING) {
                    $single = true;
                    $first = true;
                    foreach ($irreg as $termin_id => $termin) {
                        if ($termin->isPresence()) {
                            if (!Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES || $termin->getStartTime() > time() - 3600) {
                                if (empty($first_event)) {
                                    $first_event = $termin->getStartTime();
                                }
                                $groups[$i]["termin_ids"][$termin->getSingleDateId()] = TRUE;
                                if (!$first) $info[$i]['name'] .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;';
                                $info[$i]['name'] .= $termin->toString();
                                $info[$i]['export'] .= $termin->toString();
                                $resObj = ResourceObject::Factory($termin->resource_id);

                                if ($link = $resObj->getFormattedLink($termin->getStartTime())) {
                                    $info[$i]['name'] .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;'.$link;
                                    $info[$i]['export'] .= ': ' . $resObj->getName();
                                    if (empty($info[$i]['raum'])) {
                                        $info[$i]['raum'] = $termin->resource_id;
                                    } else if ($info[$i]['raum'] != $termin->resource_id) {
                                        $single = false;
                                    }
                                }

                                if (date('w', $termin->getStartTime()) == 6 || date('w', $termin->getStartTime()) == 0) {
                                    $info[$i]['weekend'] = true;
                                }
                                $first = false;
                            }
                        }
                    }
                    if (!$single) unset($info[$i]['raum']);
                } else {
                    foreach ($irreg as $termin_id => $termin) {
                        if ($termin->isPresence()) {
                            if (!Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES || $termin->getStartTime() > time() - 3600) {
                                if (empty($first_event)) {
                                    $first_event = $termin->getStartTime();
                                }
                                $groups[$i]["termin_ids"][$termin->getSingleDateId()] = TRUE;
                                $info[$i]['name'] = $termin->toString();
                                $info[$i]['export'] = $termin->toString();
                                $resObj = ResourceObject::Factory($termin->resource_id);

                                if ($link = $resObj->getFormattedLink($termin->getStartTime())) {
                                    $info[$i]['name'] .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;'.$link;
                                    $info[$i]['raum'] = $termin->resource_id;
                                    $info[$i]['export'] .= ': ' . $resObj->getName();
                                }

                                $info[$i]['weekend'] = (date('w', $termin->getStartTime()) == 6 || date('w', $termin->getStartTime()) == 0);
                                $i++;
                            }
                        }
                    }
                }
            }
        } else {    // we have a single date
            $termin = new SingleDate($singledate);
            $groups[0]['termin_ids'][$termin->getSingleDateID()] = TRUE;
            $info[0]['name']   = $this->getDatesTemplate('dates/date_html', ['date' => $termin]);
            $info[0]['export'] = $termin->toString();
            $info[0]['raum']   = $termin->resource_id;
            $info[0]['weekend'] = (date('w', $termin->getStartTime()) == 6 || date('w', $termin->getStartTime()) == 0);
            $first_event = $termin->getStartTime();
        }

        return ['first_event' => $first_event, 'groups' => $groups, 'info' => $info];
    }

    /**
     * creates a textual, status-dependent representation of a room-request for a seminar.
     *
     * @return string conatining room, responsible person, properties, current status and message / decline-message
     */
    public function getRoomRequestInfo()
    {
        $room_request = $this->getRoomRequestStatus();
        if ($room_request) {
            if (!$this->requestData) {
                $rD = new RoomRequest($this->request_id);
                $resObject = ResourceObject::Factory($rD->resource_id);
                $this->requestData .= 'Raum: '.$resObject->getName() . "\n";
                $this->requestData .= 'verantwortlich: '.$resObject->getOwnerName() ."\n\n";
                foreach ($rD->getProperties() as $val) {
                    $this->requestData .= $val['name'].': ';
                    if ($val['type'] == 'bool') {
                        if ($val['state'] == 'on') {
                            $this->requestData .= "vorhanden\n";
                        } else {
                            $this->requestData .= "nicht vorhanden\n";
                        }
                    } else {
                        $this->requestData .= $val['state'] . "\n";
                    }
                }
                if  ($rD->getClosed() == 0) {
                    $txt = _("Die Anfrage wurde noch nicht bearbeitet.");
                } else if ($rD->getClosed() == 3) {
                    $txt = _("Ihre Anfrage wurde abgelehnt!");
                } else {
                    $txt = _("Die Anfrage wurde bearbeitet.");
                }

                $this->requestData .= "\nStatus: $txt\n";

                // if the room-request has been declined, show the decline-notice placed by the room-administrator
                if ($room_request == 'declined') {
                    if ($rD->getReplyComment()) {
                        $this->requestData .= "\nNachricht Raumadmin:\n";
                        $this->requestData .= $rD->getReplyComment();
                    }
                } else {
                    if ($rD->getComment()) {
                        $this->requestData .= "\nNachricht an Raumadmin:\n";
                        $this->requestData .= $rD->getComment();
                    }
                }

            }

            return $this->requestData;
        } else {
            return FALSE;
        }
    }

    public function removeSeminarRequest()
    {
        $request_id = RoomRequest::existsByCourse($this->getId());
        if ($request_id) {
            // logging >>>>>>
            StudipLog::log("SEM_DELETE_REQUEST", $this->getId());
            // logging <<<<<<
            $this->requestData = '';
            return RoomRequest::find($request_id)->delete();
        }
    }

    /**
     * instance method
     *
     * returns number of participants for each usergroup in seminar,
     * total, lecturers, tutors, authors, users
     *
     * @param string (optional) return count only for given usergroup
     *
     * @return array <description>
     */

    public function getNumberOfParticipants()
    {
        $args = func_get_args();
        array_unshift($args, $this->id);
        return call_user_func_array(["Seminar", "getNumberOfParticipantsBySeminarId"], $args);
    }

    /**
     * class method
     *
     * returns number of participants for each usergroup in given seminar,
     * total, lecturers, tutors, authors, users
     *
     * @param string seminar_id
     *
     * @param string (optional) return count only for given usergroup
     *
     * @return array <description>
     */

    public function getNumberOfParticipantsBySeminarId($sem_id)
    {
        $db = DBManager::get();
        $stmt1 = $db->prepare("SELECT
                               COUNT(Seminar_id) AS anzahl,
                               COUNT(IF(status='dozent',Seminar_id,NULL)) AS anz_dozent,
                               COUNT(IF(status='tutor',Seminar_id,NULL)) AS anz_tutor,
                               COUNT(IF(status='autor',Seminar_id,NULL)) AS anz_autor,
                               COUNT(IF(status='user',Seminar_id,NULL)) AS anz_user
                               FROM seminar_user
                               WHERE Seminar_id = ?
                               GROUP BY Seminar_id");
        $stmt1->execute([$sem_id]);
        $numbers = $stmt1->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $db->prepare("SELECT COUNT(*) as anzahl
                               FROM admission_seminar_user
                               WHERE seminar_id = ?
                               AND status = 'accepted'");
        $stmt2->execute([$sem_id]);
        $acceptedUsers = $stmt2->fetch(PDO::FETCH_ASSOC);


        $count = 0;
        if ($numbers["anzahl"]) {
            $count += $numbers["anzahl"];
        }
        if ($acceptedUsers["anzahl"]) {
            $count += $acceptedUsers["anzahl"];
        }

        $participant_count = [];
        $participant_count['total']     = $count;
        $participant_count['lecturers'] = $numbers['anz_dozent'] ? (int) $numbers['anz_dozent'] : 0;
        $participant_count['tutors']    = $numbers['anz_tutor']  ? (int) $numbers['anz_tutor']  : 0;
        $participant_count['authors']   = $numbers['anz_autor']  ? (int) $numbers['anz_autor']  : 0;
        $participant_count['users']     = $numbers['anz_user']   ? (int) $numbers['anz_user']   : 0;

        // return specific parameter if
        $params = func_get_args();
        if (sizeof($params) > 1) {
            if (in_array($params[1], array_keys($participant_count))) {
                return $participant_count[$params[1]];
            } else {
                trigger_error(get_class($this)."::__getParticipantInfos - unknown parameter requested");
            }
        }

        return $participant_count;
    }


    /**
     * Returns the IDs of this course's study areas.
     *
     * @return array     an array of IDs
     */
    public function getStudyAreas()
    {
        $stmt = DBManager::get()->prepare("SELECT DISTINCT sem_tree_id ".
            "FROM seminar_sem_tree ".
            "WHERE seminar_id=?");

        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Sets the study areas of this course.
     *
     * @param  array      an array of IDs
     *
     * @return void
     */
    public function setStudyAreas($selected)
    {
        $old = $this->getStudyAreas();
        $sem_tree = TreeAbstract::GetInstance("StudipSemTree");
        $removed = array_diff($old, $selected);
        $added = array_diff($selected, $old);
        $count_removed = 0;
        $count_added = 0;
        foreach($removed as $one){
            $count_removed += $sem_tree->DeleteSemEntries($one, $this->getId());
        }
        foreach($added as $one){
            $count_added += $sem_tree->InsertSemEntry($one, $this->getId());
        }
        if ($count_added || $count_removed) {
            NotificationCenter::postNotification("CourseDidChangeStudyArea", $this);
        }
        return count($old) + $count_added - $count_removed;
    }

    /**
     * @return boolean    returns TRUE if this course is publicly visible,
     *                    FALSE otherwise
     */
    public function isPublic()
    {
        return Config::get()->ENABLE_FREE_ACCESS && $this->read_level == 0;
    }

    /**
     * @return boolean  returns TRUE if this course is a studygroup,
     *                  FALSE otherwise
     */
    public function isStudygroup()
    {
        global $SEM_CLASS, $SEM_TYPE;
        return $SEM_CLASS[$SEM_TYPE[$this->status]["class"]]["studygroup_mode"];
    }

    /**
     * @return int      returns default colour group for new members (shown in meine_seminare.php)
     *
     **/
    public function getDefaultGroup()
    {
        if ($this->isStudygroup()) {
            return 8;
        } else {
            return select_group ($this->semester_start_time);
        }
    }


    /**
     *  Deletes the current seminar
     *
     * @return void       returns success-message if seminar could be deleted
     *                    otherwise an  error-message
     */

    public function delete()
    {
        $s_id = $this->id;

        // Delete that Seminar.

        // Alle Benutzer aus dem Seminar rauswerfen.
        $query = "DELETE FROM seminar_user WHERE Seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);
        if (($db_ar = $statement->rowCount()) > 0) {
            $this->createMessage(sprintf(_("%s Teilnehmende und Lehrende archiviert."), $db_ar));
        }

        // Alle Benutzer aus Wartelisten rauswerfen
        $query = "DELETE FROM admission_seminar_user WHERE seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);

        // Alle beteiligten Institute rauswerfen
        $query = "DELETE FROM seminar_inst WHERE Seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);
        if (($db_ar = $statement->rowCount()) > 0) {
            $this->createMessage(sprintf(_("%s Zuordnungen zu Einrichtungen archiviert."), $db_ar));
        }

        // user aus den Statusgruppen rauswerfen
        $count = Statusgruppen::deleteBySQL('range_id = ?', [$s_id]);
        if ($count > 0) {
            $this->createMessage(sprintf(_('%s Funktionen/Gruppen gelöscht.'), $count));
        }

        // Alle Eintraege aus dem Vorlesungsverzeichnis rauswerfen
        $sem_tree = TreeAbstract::GetInstance('StudipSemTree');
        $db_ar = $sem_tree->DeleteSemEntries(null, $s_id);
        if ($db_ar > 0) {
            $this->createMessage(sprintf(_("%s Zuordnungen zu Bereichen archiviert."), $db_ar));
        }

        // Alle Termine mit allem was dranhaengt zu diesem Seminar loeschen.
        if (($db_ar = SingleDateDB::deleteAllDates($s_id)) > 0) {
            $this->createMessage(sprintf(_("%s Veranstaltungstermine archiviert."), $db_ar));
        }

        //Themen
        IssueDB::deleteAllIssues($s_id);

        //Cycles
        SeminarCycleDate::deleteBySQL('seminar_id = ' . DBManager::get()->quote($s_id));

        // Alle weiteren Postings zu diesem Seminar in den Forums-Modulen löschen
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $plugin->deleteContents($s_id);  // delete content irrespective of plugin-activation in the seminar

            if ($plugin->isActivated($s_id)) {   // only show a message, if the plugin is activated, to not confuse the user
                $this->createMessage(sprintf(_('Einträge in %s archiviert.'), $plugin->getPluginName()));
            }
        }


        // Alle Dokumente zu diesem Seminar loeschen.
        $folder = Folder::findTopFolder($s_id);
        if($folder) {
            if($folder->delete()) {
                $this->createMessage(_("Dokumente und Ordner archiviert."));
            }
        }


        // Freie Seite zu diesem Seminar löschen
        $query = "DELETE FROM scm WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);
        if (($db_ar = $statement->rowCount()) > 0) {
            $this->createMessage(_("Freie Seite der Veranstaltung archiviert."));
        }

        // delete literatur
        $del_lit = StudipLitList::DeleteListsByRange($s_id);
        if ($del_lit) {
            $this->createMessage(sprintf(_("%s Literaturlisten archiviert."),$del_lit['list']));
        }

        // Alle News-Verweise auf dieses Seminar löschen
        if ( ($db_ar = StudipNews::DeleteNewsRanges($s_id)) ) {
            $this->createMessage(sprintf(_("%s Ankündigungen gelöscht."), $db_ar));
        }
        //delete entry in news_rss_range
        StudipNews::UnsetRssId($s_id);

        //kill the datafields
        DataFieldEntry::removeAll($s_id);

        //kill all wiki-pages
        $query = "DELETE FROM wiki WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);
        if (($db_wiki = $statement->rowCount()) > 0) {
            $this->createMessage(sprintf(_("%s Wiki-Seiten archiviert."), $db_wiki));
        }

        $query = "DELETE FROM wiki_links WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);

        $query = "DELETE FROM wiki_locks WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);

        // remove wiki page config
        WikiPageConfig::deleteBySQL('range_id = ?', [$s_id]);

        // delete course config values
        ConfigValue::deleteBySQL('range_id = ?', [$s_id]);

        // kill all the ressources that are assigned to the Veranstaltung (and all the linked or subordinated stuff!)
        if (Config::get()->RESOURCES_ENABLE) {
            $killAssign = new DeleteResourcesUser($s_id);
            $killAssign->delete();
            if ($rr = RoomRequest::existsByCourse($s_id)) {
                RoomRequest::find($rr)->delete();
            }
        }

        // kill virtual seminar-entries in calendar
        $query = "DELETE FROM schedule_seminare WHERE seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);

        if(get_config('ELEARNING_INTERFACE_ENABLE')){
            global $connected_cms;
            $del_cms = 0;
            $cms_types = ObjectConnections::GetConnectedSystems($s_id);
            if(count($cms_types)){
                foreach($cms_types as $system){
                    ELearningUtils::loadClass($system);
                    $del_cms += $connected_cms[$system]->deleteConnectedModules($s_id);
                }
                $this->createMessage(sprintf(_("%s Verknüpfungen zu externen Systemen gelöscht."), $del_cms ));
            }
        }

        //kill the object_user_vists for this seminar
        object_kill_visits(null, $s_id);

        // Logging...
        $query = "SELECT CONCAT(seminare.VeranstaltungsNummer, ' ', seminare.name, '(', semester_data.name, ')')
                  FROM seminare
                  LEFT JOIN semester_data ON (seminare.start_time = semester_data.beginn)
                  WHERE seminare.Seminar_id='$s_id'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$s_id]);
        $semlogname = $statement->fetchColumn() ?: sprintf('unknown sem_id: %s', $s_id);

        StudipLog::log("SEM_ARCHIVE",$s_id,NULL,$semlogname);
        // ...logged

        // delete deputies if necessary
        deleteAllDeputies($s_id);

        UserDomain::removeUserDomainsForSeminar($s_id);

        AutoInsert::deleteSeminar($s_id);

        //Anmeldeset Zordnung entfernen
        $cs = $this->getCourseSet();
        if ($cs) {
            CourseSet::removeCourseFromSet($cs->getId(), $this->getId());
            $cs->load();
            if (!count($cs->getCourses())
                && $cs->isGlobal()
                && $cs->getUserid() != '') {
                $cs->delete();
            }
        }
        AdmissionPriority::unsetAllPrioritiesForCourse($this->getId());
        // und das Seminar loeschen.
        $this->course->delete();
        $this->restore();
        return true;
    }

    /**
     * returns a html representation of the seminar-dates
     *
     * @param  array  optional variables which are passed to the template
     * @return  string  the html-representation of the dates
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    public function getDatesHTML($params = [])
    {
        return $this->getDatesTemplate('dates/seminar_html.php', $params);
    }

    /**
     * returns a representation without html of the seminar-dates
     *
     * @param  array  optional variables which are passed to the template
     * @return  string  the representation of the dates without html
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    public function getDatesExport($params = [])
    {
        return $this->getDatesTemplate('dates/seminar_export.php', $params);
    }

    /**
     * returns a xml-representation of the seminar-dates
     *
     * @param  array  optional variables which are passed to the template
     * @return  string  the xml-representation of the dates
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    public function getDatesXML($params = [])
    {
        return $this->getDatesTemplate('dates/seminar_xml.php', $params);
    }

    /**
     * returns a representation of the seminar-dates with a specifiable template
     *
     * @param  mixed  this can be a template-object or a string pointing to a template in path_to_studip/templates
     * @param  array  optional parameters which are passed to the template
     * @return  string  the template output of the dates
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     */
    public function getDatesTemplate($template, $params = [])
    {
        if (!$template instanceof Flexi_Template && is_string($template)) {
            $template = $GLOBALS['template_factory']->open($template);
        }

        if ($params['semester_id']) {
            $semester = Semester::find($params['semester_id']);
            if ($semester) {
                // apply filter
                $this->applyTimeFilter($semester->beginn, $semester->ende);
            }
        }

        $template->dates = $this->getUndecoratedData(isset($params['semester_id']));
        $template->seminar_id = $this->getId();

        $template->set_attributes($params);
        return trim($template->render());
    }

    /**
     * returns an asscociative array with the attributes of the seminar depending
     * on the field-names in the database
     * @return array
     */
    public function getData()
    {
        $data = $this->course->toArray();
        foreach($this->alias as $a => $o) {
            $data[$a] = $this->course->$o;
        }
        return $data;
    }

    /**
     * returns an array with all IDs of Institutes this seminar is related to
     * @param sem_id string:    optional ID of a seminar, when null, this ID will be used
     * @return: array of IDs (not associative)
     */
    public function getInstitutes($sem_id = null)
    {
        if (!$sem_id && $this) {
            $sem_id = $this->id;
        }

        $query = "SELECT institut_id FROM seminar_inst WHERE seminar_id = :sem_id
                  UNION
                  SELECT Institut_id FROM seminare WHERE Seminar_id = :sem_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(compact('sem_id'));
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * set the entries for seminar_inst table in database
     * seminare.institut_id will always be added
     * @param institutes array: array of Institut_id's
     * @return bool:  if something changed
     */
    public function setInstitutes($institutes = [])
    {
        if (is_array($institutes)) {
            $institutes[] = $this->institut_id;
            $institutes = array_unique($institutes);

            $query = "SELECT institut_id FROM seminar_inst WHERE seminar_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->id]);
            $old_inst = $statement->fetchAll(PDO::FETCH_COLUMN);

            $todelete = array_diff($old_inst, $institutes);

            $query = "DELETE FROM seminar_inst WHERE seminar_id = ? AND institut_id = ?";
            $statement = DBManager::get()->prepare($query);

            foreach($todelete as $inst) {
                $tmp_instname= get_object_name($inst, 'inst');
                StudipLog::log('CHANGE_INSTITUTE_DATA', $this->id, $inst, 'Die beteiligte Einrichtung "'. $tmp_instname['name'] .'" wurde gelöscht.');
                $statement->execute([$this->id, $inst]);
                NotificationCenter::postNotification('SeminarInstitutionDidDelete', $inst, $this->id);

            }

            $toinsert = array_diff($institutes, $old_inst);

            $query = "INSERT INTO seminar_inst (seminar_id, institut_id) VALUES (?, ?)";
            $statement = DBManager::get()->prepare($query);

            foreach($toinsert as $inst) {
                $tmp_instname= get_object_name($inst, 'inst');
                StudipLog::log('CHANGE_INSTITUTE_DATA', $this->id, $inst, 'Die beteiligte Einrichtung "'. $tmp_instname['name'] .'" wurde hinzugefügt.');
                $statement->execute([$this->id, $inst]);
                NotificationCenter::postNotification('SeminarInstitutionDidCreate', $inst, $this->id);
            }
            if ($todelete || $toinsert) {
                NotificationCenter::postNotification("CourseDidChangeInstitutes", $this);
            }
            return $todelete || $toinsert;
        } else {
            $this->createError(_("Ungültige Eingabe der Institute. Es muss " .
                "mindestens ein Institut angegeben werden."));
            return false;
        }
    }

    /**
     * adds a user to the seminar with the given status
     * @param user_id string: ID of the user
     * @param status string: status of the user for the seminar "user", "autor", "tutor", "dozent"
     * @param force bool: if false (default) the user will only be upgraded and not degraded in his/her status
     */
    public function addMember($user_id, $status = 'autor', $force = false)
    {

        if (in_array($GLOBALS['perm']->get_perm($user_id), ["admin", "root"])) {
            $this->createError(_("Admin und Root dürfen nicht Mitglied einer Veranstaltung sein."));
            return false;
        }
        $db = DBManager::get();

        $rangordnung = array_flip(['user', 'autor', 'tutor', 'dozent']);
        if ($rangordnung[$status] > $rangordnung['autor'] && SeminarCategories::getByTypeId($this->status)->only_inst_user) {
            //überprüfe, ob im richtigen Institut:
            $user_institute_stmt = $db->prepare(
                "SELECT Institut_id " .
                "FROM user_inst " .
                "WHERE user_id = :user_id " .
                "");
            $user_institute_stmt->execute(['user_id' => $user_id]);
            $user_institute = $user_institute_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if (!in_array($this->institut_id, $user_institute) && !count(array_intersect($user_institute, $this->getInstitutes()))) {
                $this->createError(_("Einzutragender Nutzer stammt nicht einem beteiligten Institut an."));

                return false;
            }
        }

        if (!$force) {
            $query = "SELECT status FROM seminar_user WHERE user_id = ? AND Seminar_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$user_id, $this->id]);
            $old_status = $statement->fetchColumn();
        }

        $query = "SELECT MAX(position) + 1 FROM seminar_user WHERE status = ? AND Seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$status, $this->id]);
        $new_position = $statement->fetchColumn();

        $query = "SELECT COUNT(*) FROM seminar_user WHERE Seminar_id = ? AND status = 'dozent'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        $numberOfTeachers = $statement->fetchColumn();

        if (!$old_status) {
            $query = "INSERT INTO seminar_user (Seminar_id, user_id, status, position, gruppe, visible, mkdate)
                      VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->id,
                $user_id,
                $status,
                $new_position ?: 0,
                (int)select_group($this->getSemesterStartTime()),
                in_array($status, words('tutor dozent')) ? 'yes' : 'unknown',
            ]);
            // delete the entries, user is now in the seminar
            $stmt = DBManager::get()->prepare('DELETE FROM admission_seminar_user
                                            WHERE user_id = ? AND seminar_id = ?');
            $stmt->execute([$user_id, $this->getId()]);
            if ($stmt->rowCount()) {
                //renumber the waiting/accepted/lot list, a user was deleted from it
                renumber_admission($this->getId());
            }
            $cs = $this->getCourseSet();
            if ($cs) {
                $prio_delete = AdmissionPriority::unsetPriority($cs->getId(), $user_id, $this->getId());
            }

            CalendarScheduleModel::deleteSeminarEntries($user_id, $this->getId());
            NotificationCenter::postNotification("CourseDidGetMember", $this, $user_id);
            NotificationCenter::postNotification('UserDidEnterCourse', $this->id, $user_id);
            StudipLog::log('SEM_USER_ADD', $this->id, $user_id, $status, 'Wurde in die Veranstaltung eingetragen');
            $this->course->resetRelation('members');
            $this->course->resetRelation('admission_applicants');

            // Check if we need to add user to parent course as well.
            if ($this->parent_course) {
                $parent = new Seminar($this->parent);
                $parent->addMember($user_id, $status, $force);
            }

            return $this;
        } elseif (($force || $rangordnung[$old_status] < $rangordnung[$status])
            && ($old_status !== "dozent" || $numberOfTeachers > 1)) {
            $query = "UPDATE seminar_user
                      SET status = ?, visible = IFNULL(?, visible), position = ?
                      WHERE Seminar_id = ? AND user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $status,
                in_array($status, words('tutor dozent')) ? 'yes' : null,
                $new_position,
                $this->id,
                $user_id,
            ]);

            if ($old_status === 'dozent') {
                $query = "SELECT termin_id FROM termine WHERE range_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$this->id]);
                $termine = $statement->fetchAll(PDO::FETCH_COLUMN);

                $query = "DELETE FROM termin_related_persons WHERE range_id = ? AND user_id = ?";
                $statement = DBManager::get()->prepare($query);

                foreach ($termine as $termin_id) {
                    $statement->execute([$termin_id, $user_id]);
                }
            }
            NotificationCenter::postNotification("CourseDidChangeMember", $this, $user_id);
            $this->course->resetRelation('members');
            $this->course->resetRelation('admission_applicants');
            return $this;
        } else {
            if ($old_status === "dozent" && $numberOfTeachers <= 1) {
                $this->createError(sprintf(_("Die Veranstaltung muss wenigstens <b>einen/eine</b> VeranstaltungsleiterIn (%s) eingetragen haben!"),
                        get_title_for_status('dozent', 1, $this->status)) .
                    ' ' . _("Tragen Sie zunächst einen anderen ein, um diesen herabzustufen."));
            }

            return false;
        }
    }

    /**
     * deletes a user from the seminar by respecting the rule that at least one
     * user with status "dozent" must stay there
     * @param user_id string:   user_id of the user to delete
     * @param return:   false or $this for chaining
     */
    public function deleteMember($user_id)
    {
        $dozenten = $this->getMembers('dozent');
        if (count($dozenten) >= 2 || !$dozenten[$user_id]) {
            $query = "DELETE FROM seminar_user WHERE Seminar_id = ? AND user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->id, $user_id]);
            if ($statement->rowCount() === 0) {
                return $this;
            }
            // If this course is a child of another course...
            if ($this->parent_course) {

                // ... check if user is member in another sibling ...
                $other = CourseMember::countBySQL(
                    "`user_id` = :user AND `Seminar_id` IN (:courses) AND `Seminar_id` != :this",
                    ['user' => $user_id, 'courses' => $this->parent->children->pluck('seminar_id'), 'this' => $this->id]
                );

                // ... and delete from parent course if this was the only
                // course membership in this family.
                if ($other == 0) {
                    $s = new Seminar($this->parent);
                    $s->deleteMember($user_id);
                }
            }

            if ($this->children != null) {
                foreach ($this->children as $child) {
                    $s = new Seminar($child);
                    $s->deleteMember($user_id);
                }
            }

            if ($dozenten[$user_id]) {
                $query = "SELECT termin_id FROM termine WHERE range_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$this->id]);
                $termine = $statement->fetchAll(PDO::FETCH_COLUMN);

                $query = "DELETE FROM termin_related_persons WHERE range_id = ? AND user_id = ?";
                $statement = DBManager::get()->prepare($query);

                foreach ($termine as $termin_id) {
                    $statement->execute([$termin_id, $user_id]);
                }
                if (isDefaultDeputyActivated()) {
                    $other_dozenten = array_diff(array_keys($dozenten), [$user_id]);
                    foreach (Deputy::findByRange_id($user_id) as $default_deputy) {
                        if ($default_deputy->user_id != $GLOBALS['user']->id &&
                                !Deputy::countBySql("range_id IN (?)", [$other_dozenten])) {
                            Deputy::deleteBySQL("range_id = ? AND user_id = ?", [$this->id, $default_deputy->user_id]);
                        }
                    }
                }
            }

            // Remove from associated status groups
            foreach (Statusgruppen::findBySeminar_id($this->id) as $group) {
                $group->removeUser($user_id, true);
            }

            $this->createMessage(sprintf(
                _('Nutzer %s wurde aus der Veranstaltung entfernt.'),
                '<i>' . htmlReady(get_fullname($user_id)) . '</i>'
            ));
            NotificationCenter::postNotification('CourseDidChangeMember', $this, $user_id);
            NotificationCenter::postNotification('UserDidLeaveCourse', $this->id, $user_id);
            StudipLog::log('SEM_USER_DEL', $this->id, $user_id, 'Wurde aus der Veranstaltung entfernt');
            $this->course->resetRelation('members');
            return $this;
        } else {
            $this->createError(
                sprintf(
                    _('Die Veranstaltung muss wenigstens <b>einen/eine</b> VeranstaltungsleiterIn (%s) eingetragen haben!'),
                    get_title_for_status('dozent', 1, $this->status)
                )
                . ' ' . _('Tragen Sie zunächst einen anderen ein, um diesen zu löschen.')
            );
            return false;
        }
    }

    /**
     * sets the almost never used column position in the table seminar_user
     * @param members array: array of user_id's - wrong IDs will be ignored
     * @return $this
     */
    public function setMemberPriority($members)
    {
        $query = "UPDATE seminar_user
                  SET position = ?
                  WHERE Seminar_id = ? AND user_id = ?";
        $statement = DBManager::get()->prepare($query);

        foreach(array_values($members) as $num => $member) {
            $statement->execute([$num, $this->id, $member]);
        }

        return $this;
    }

    public function setLabel($user_id, $label) {
        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->getId(), $user_id)) {
            $statement = DBManager::get()->prepare(
                "UPDATE seminar_user " .
                "SET label = :label " .
                "WHERE user_id = :user_id " .
                "AND Seminar_id = :seminar_id " .
                "");
            $statement->execute([
                'user_id' => $user_id,
                'seminar_id' => $this->getId(),
                'label' => $label
            ]);
            NotificationCenter::postNotification("CourseDidChangeMemberLabel", $this);
        }
    }

    /**
     * returns array with information about enrolment to this course for given user_id
     * ['enrolment_allowed'] : true or false
     * ['cause']: keyword to describe the cause
     * ['description'] : readable description of the cause
     *
     * @param string $user_id
     * @return array
     */
    public function getEnrolmentInfo($user_id)
    {
        $info = [];
        $user = User::find($user_id);
        if ($this->getSemClass()->isGroup()) {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'grouped';
            $info['description'] = _("Dies ist eine Veranstaltungsgruppe. Sie können sich nur in deren Unterveranstaltungen eintragen.");
            return $info;
        }
        if ($this->read_level == 0 && get_config('ENABLE_FREE_ACCESS') && !$GLOBALS['perm']->get_studip_perm($this->getId(), $user_id)) {
            $info['enrolment_allowed'] = true;
            $info['cause'] = 'free_access';
            $info['description'] = _("Für die Veranstaltung ist keine Anmeldung erforderlich.");
            return $info;
        }
        if (!$user) {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'nobody';
            $info['description'] = _("Sie sind nicht in Stud.IP angemeldet.");
            return $info;
        }
        if ($GLOBALS['perm']->have_perm('root', $user_id)) {
            $info['enrolment_allowed'] = true;
            $info['cause'] = 'root';
            $info['description'] = _("Sie dürfen ALLES.");
            return $info;
        }
        if ($GLOBALS['perm']->have_studip_perm('admin', $this->getId(), $user_id)) {
            $info['enrolment_allowed'] = true;
            $info['cause'] = 'courseadmin';
            $info['description'] = _("Sie sind Administrator_in der Veranstaltung.");
            return $info;
        }
        if ($GLOBALS['perm']->have_perm('admin', $user_id)) {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'admin';
            $info['description'] = _("Als Administrator_in können Sie sich nicht für eine Veranstaltung anmelden.");
            return $info;
        }
        //Ist bereits Teilnehmer
        if ($GLOBALS['perm']->have_studip_perm('user', $this->getId(), $user_id)) {
            $info['enrolment_allowed'] = true;
            $info['cause'] = 'member';
            $info['description'] = _("Sie sind für die Veranstaltung angemeldet.");
            return $info;
        }
        $admission_status = $user->admission_applications->findBy('seminar_id', $this->getId())->val('status');
        if ($admission_status == 'accepted') {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'accepted';
            $info['description'] = _("Sie wurden für diese Veranstaltung vorläufig akzeptiert.");
            return $info;
        }
        if ($admission_status == 'awaiting') {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'awaiting';
            $info['description'] = _("Sie stehen auf der Warteliste für diese Veranstaltung.");
            return $info;
        }
        if ($GLOBALS['perm']->get_perm($user_id) == 'user') {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'user';
            $info['description'] = _("Sie haben nicht die erforderliche Berechtigung sich für eine Veranstaltung anzumelden.");
            return $info;
        }
        //falsche Nutzerdomäne
        $same_domain = true;
        $user_domains = UserDomain::getUserDomainsForUser($user_id);
        if (count($user_domains) > 0) {
            $seminar_domains = UserDomain::getUserDomainsForSeminar($this->getId());
            $same_domain = count(array_intersect($seminar_domains, $user_domains)) > 0;
        }
        if (!$same_domain && !$this->isStudygroup()) {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'domain';
            $info['description'] = _("Sie sind nicht in einer zugelassenenen Nutzerdomäne, Sie können sich nicht eintragen!");
            return $info;
        }
        //Teilnehmerverwaltung mit Sperregel belegt
        if (LockRules::Check($this->getId(), 'participants')) {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'locked';
            $lockdata = LockRules::getObjectRule($this->getId());
            $info['description'] = _("In diese Veranstaltung können Sie sich nicht eintragen!") . ($lockdata['description'] ? '<br>' . formatLinks($lockdata['description']) : '');
            return $info;
        }
        //Veranstaltung unsichtbar für aktuellen Nutzer
        if (!$this->visible && !$this->isStudygroup() && !$GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'), $user_id)) {
            $info['enrolment_allowed'] = false;
            $info['cause'] = 'invisible';
            $info['description'] = _("Die Veranstaltung ist gesperrt, Sie können sich nicht eintragen!");
            return $info;
        }
        if ($courseset = $this->getCourseSet()) {
            $info['enrolment_allowed'] = true;
            $info['cause'] = 'courseset';
            $info['description'] = _("Die Anmeldung zu dieser Veranstaltung folgt speziellen Regeln. Lesen Sie den Hinweistext.");
            $user_prio = AdmissionPriority::getPrioritiesByUser($courseset->getId(), $user_id);
            if (isset($user_prio[$this->getId()])) {
                if ($courseset->hasAdmissionRule('LimitedAdmission')) {
                    $info['description'] .= ' ' . sprintf(_("(Sie stehen auf der Anmeldeliste für die automatische Platzverteilung mit der Priorität %s.)"), $user_prio[$this->getId()]);
                } else {
                    $info['description'] .= ' ' . _("(Sie stehen auf der Anmeldeliste für die automatische Platzverteilung.)");
                }
            }
            return $info;
        }
        $info['enrolment_allowed'] = true;
        $info['cause'] = 'normal';
        $info['description'] = '';
        return $info;
    }

    /**
     * adds user with given id as preliminary member to course
     *
     * @param string $user_id
     * @return integer 1 if successfull
     */
    public function addPreliminaryMember($user_id, $comment = '')
    {
        $new_admission_member = new AdmissionApplication();
        $new_admission_member->user_id = $user_id;
        $new_admission_member->position = 0;
        $new_admission_member->status = 'accepted';
        $new_admission_member->comment = $comment;
        $this->course->admission_applicants[] = $new_admission_member;
        $ok = $new_admission_member->store();
        if ($ok && $this->isStudygroup()) {
            StudygroupModel::applicationNotice($this->getId(), $user_id);
        }
        $cs = $this->getCourseSet();
        if ($cs) {
            $prio_delete = AdmissionPriority::unsetPriority($cs->getId(), $user_id, $this->getId());
        }
        // LOGGING
        StudipLog::log('SEM_USER_ADD', $this->getId(), $user_id, 'accepted', 'Vorläufig akzeptiert');
        return $ok;
    }

    /**
     * returns courseset object for this  course
     *
     * @return CourseSet courseset object or null
     */
    public function getCourseSet()
    {
        if ($this->course_set === null) {
            $this->course_set = CourseSet::getSetForCourse($this->id);
            if ($this->course_set === null) {
                $this->course_set = false;
            }
        }
        return $this->course_set ?: null;
    }

    /**
     * returns true if the number of participants of this course is limited
     *
     * @return boolean
     */
    public function isAdmissionEnabled()
    {
        $cs = $this->getCourseSet();
        return ($cs && $cs->isSeatDistributionEnabled());
    }

    /**
     * returns the number of free seats in the course or true if not limited
     *
     * @return integer
     */
    public function getFreeAdmissionSeats()
    {
        if ($this->isAdmissionEnabled()) {
            return $this->course->getFreeSeats();
        } else {
            return true;
        }
    }

    /**
     * returns true if the course is locked
     *
     * @return boolean
     */
    public function isAdmissionLocked()
    {
        $cs = $this->getCourseSet();
        return ($cs && $cs->hasAdmissionRule('LockedAdmission'));
    }

    /**
     * returns true if the course is password protected
     *
     * @return boolean
     */
    public function isPasswordProtected()
    {
        $cs = $this->getCourseSet();
        return ($cs && $cs->hasAdmissionRule('PasswordAdmission'));
    }

    /**
     * returns array with start and endtime of course enrolment timeframe
     * return null if there is no timeframe
     *
     * @return array assoc array with start_time end_time as keys timestamps as values
     */
    public function getAdmissionTimeFrame()
    {
        $cs = $this->getCourseSet();
        return ($cs && $cs->hasAdmissionRule('TimedAdmission')) ?
            ['start_time' => $cs->getAdmissionRule('TimedAdmission')->getStartTime(),
                  'end_time' => $cs->getAdmissionRule('TimedAdmission')->getEndTime()] : [];
    }

    /**
     * returns StudipModule object for given slot, null when deactivated or not available
     *
     * @param string $slot
     * @return StudipModule
     */
    public function getSlotModule($slot)
    {
        $m = new Modules();
        $activated_slots = array_filter($m->getLocalModules($this->id, 'sem', $this->modules, $this->status));
        if (isset($activated_slots[$slot])) {
            $module = $this->getSemClass()->getSlotModule($slot);
            if (is_subclass_of($module, 'StudIPPlugin')) {
                return PluginManager::getInstance()->getPlugin($module);
            } else {
                return new $module();
            }
        }
    }

    /**
     * adds user with given id on waitinglist
     *
     * @param string $user_id
     * @param string $which_end 'last' or 'first'
     * @return integer|bool number on waitlist or false if not successful
     */
    public function addToWaitlist($user_id, $which_end = 'last')
    {
        if (AdmissionApplication::exists([$user_id, $this->id]) || CourseMember::find([$this->id, $user_id])) {
            return false;
        }
        switch ($which_end) {
            // Append users to waitlist end.
            case 'last':
                $maxpos = DBManager::get()->fetchColumn("SELECT MAX(`position`)
                    FROM `admission_seminar_user`
                    WHERE `seminar_id`=?
                        AND `status`='awaiting'", [$this->id]);
                $waitpos = $maxpos+1;
                break;
            // Prepend users to waitlist start.
            case 'first':
            default:
                // Move all others on the waitlist up by the number of people to add.
                DBManager::get()->execute("UPDATE `admission_seminar_user`
                        SET `position`=`position`+1
                        WHERE `seminar_id`=?
                            AND `status`='awaiting'", [$this->id]);
                $waitpos = 1;
        }
        $new_admission_member = new AdmissionApplication();
        $new_admission_member->user_id = $user_id;
        $new_admission_member->position = $waitpos;
        $new_admission_member->status = 'awaiting';
        $new_admission_member->seminar_id = $this->id;
        if ($new_admission_member->store()) {
            StudipLog::log('SEM_USER_ADD', $this->id, $user_id, 'awaiting', 'Auf Warteliste gesetzt, Position: ' . $waitpos);
            $this->course->resetRelation('admission_applicants');
            return $waitpos;
        }
        return false;
    }
}
