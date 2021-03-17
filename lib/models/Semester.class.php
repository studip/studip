<?php

/**
 * Semester.class.php
 * model class for table semester_data
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string semester_id database column
 * @property string id alias column for semester_id
 * @property string name database column
 * @property string description database column
 * @property string semester_token database column
 * @property string beginn database column
 * @property string ende database column
 * @property string vorles_beginn database column
 * @property string vorles_ende database column
 * @property string first_sem_week computed column
 * @property string last_sem_week computed column
 * @property string past computed column
 */
class Semester extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'semester_data';

        $config['additional_fields']['first_sem_week']['get'] = 'getFirstSemesterWeek';
        $config['additional_fields']['last_sem_week']['get'] = 'getLastSemesterWeek';
        $config['additional_fields']['current']['get'] = 'isCurrent';
        $config['additional_fields']['past']['get'] = 'isPast';

        $config['additional_fields']['absolute_seminars_count'] = [
            'get' => 'seminarCounter',
            'set' => false,
        ];
        $config['additional_fields']['duration_seminars_count'] = [
            'get' => 'seminarCounter',
            'set' => false,
        ];
        $config['additional_fields']['continuous_seminars_count'] = [
            'get' => 'seminarCounter',
            'set' => false,
        ];

        $config['alias_fields']['token'] = 'semester_token';

        $config['registered_callbacks']['after_store'][] = 'refreshCache';
        $config['registered_callbacks']['after_delete'][] = 'refreshCache';

        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['description'] = true;
        $config['i18n_fields']['semester_token'] = true;

        parent::configure($config);
    }

    /**
     * cache
     */
    private static $semester_cache;
    private static $current_semester;


    /**
     * returns semester object for given id or null
     * @param string $id
     * @return NULL|Semester
     */
    public static function find($id)
    {
        $semester_cache = self::getAll();
        return $semester_cache[$id] ?: null;
    }

    /**
     * returns Semester for given timestamp
     * @param integer $timestamp
     * @return null|Semester
     */
    public static function findByTimestamp($timestamp)
    {
        foreach (self::getAll() as $semester) {
            if ($timestamp >= $semester->beginn && $timestamp <= $semester->ende) {
                return $semester;
            }
        }
        return null;
    }

    /**
     * returns following Semester for given timestamp
     * @param integer $timestamp
     * @return null|Semester
     */
    public static function findNext($timestamp = null)
    {
        $timestamp = $timestamp ?: time();
        $semester = self::findByTimestamp($timestamp);
        if ($semester) {
            return self::findByTimestamp((int)$semester->ende + 1);
        }

        return null;
    }

    /**
     * returns current Semester
     */
    public static function findCurrent()
    {
        self::getAll();
        return self::$current_semester;
    }

    /**
     * Return a specially orderd array of all semesters
     */
    public static function findAllVisible($with_before_first = true): array
    {
        return array_values(
            array_filter(self::getAllAsArray(), function ($semester, $key) use($with_before_first) {
                return $GLOBALS['perm']->have_perm('admin') || $semester['visible'] || ((int)$key === 0 && $with_before_first);
            }, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * returns array of all existing semester objects
     * orderd by begin
     * @param boolean $force_reload
     * @return array
     */
    public static function getAll($force_reload = false)
    {
        if (!is_array(self::$semester_cache) || $force_reload) {
            self::$semester_cache = [];
            if (!$force_reload) {
                $cache = StudipCacheFactory::getCache();
                $semester_data_array = unserialize($cache->read('DB_SEMESTER_DATA'));
                if ($semester_data_array) {
                    foreach ($semester_data_array as $semester_data) {
                        $semester = self::buildExisting($semester_data);
                        self::$semester_cache[$semester->getId()] = $semester;
                        if ($semester->isCurrent()) {
                            self::$current_semester = $semester;
                        }
                    }
                }
            }
            if (!count(self::$semester_cache)) {
                $semester_data = [];
                foreach (self::findBySql('1 ORDER BY beginn') as $semester) {
                    self::$semester_cache[$semester->getId()] = $semester;
                    if ($semester->isCurrent()) {
                        self::$current_semester = $semester;
                    }
                    $semester_data[] = $semester->toRawArray();
                }
                $cache = StudipCacheFactory::getCache();
                $cache->write('DB_SEMESTER_DATA', serialize($semester_data));
            }
        }
        return self::$semester_cache;
    }

    /**
     * Returns a list of all semesters as array, optionally with an entry at
     * the beginning that represents the time before the first semester in the
     * system.
     *
     * @param boolean $with_before_first Show the optional first entry as described above
     * @param boolean $force_reload
     * @return array
     */
    public static function getAllAsArray($with_before_first = true, $force_reload = false)
    {
        $result = array_map(function ($semester) {
            return $semester->toArray();
        }, self::getAll($force_reload));
        $result = array_values($result);

        if ($with_before_first) {
            array_unshift($result, [
                'name' => _('abgelaufene Semester'),
                'past' => true,
            ]);
        }

        return $result;
    }

    /**
     * returns the index for a given semester id, in an array returned from self::getAllAsArray(), beware of second parameter
     *
     * @param $semester_id
     * @param bool $with_before_first
     * @param bool $only_visible
     * @return bool|int
     * @deprecated ASK YOURSELF WHAT THE F!!! YOU ARE DOING
     */
    public static function getIndexById($semester_id, $with_before_first = true, $only_visible = false)
    {
        if($only_visible) {
            $semesters = self::findAllVisible($with_before_first);
        } else {
            $semesters = self::getAllAsArray($with_before_first);
        }
        foreach ($semesters as $index => $semester) {
            if (@$semester['semester_id'] === $semester_id) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Returns an html fragment with a semester select-box
     *
     * @param array $select_attributes
     * @param integer $default
     * @param string $option_value
     * @param boolean $include_all
     * @return string
     */
    public static function getSemesterSelector(
        $select_attributes = null,
        $default = 0,
        $option_value = 'semester_id',
        $include_all = true)
    {
        $semester = Semester::findAllVisible();

        unset($semester[0]);

        if ($include_all) {
            $semester['all'] = [
                'name' => _('alle'),
                'semester_id' => 0
            ];
        }
        $semester = array_reverse($semester, true);

        if (!$select_attributes['name']) {
            $select_attributes['name'] = 'sem_select';
        }

        $template = $GLOBALS['template_factory']->open('shared/semester-selector');
        $template->semesters = $semester;
        $template->select_attributes = $select_attributes;
        $template->default = $default;
        $template->option_value = $option_value;
        return $template->render();
    }

    /**
     * Caches seminar counts
     */
    protected $seminar_counts = null;

    /**
     * Counts the number of different seminar types in this semester.
     * This method caches the result in $seminar_counts so the db
     * will only be queried once per semester.
     *
     * @param String $field Name of the seminar (/additional_fields) type
     * @return int The count of seminars of this type
     */
    protected function seminarCounter($field)
    {
        if ($this->seminar_counts === null) {
            $query = "
                SELECT SUM(IF(semester_courses.semester_id IS NULL, 1, 0)) AS continuous,
                       0 AS duration,
                       SUM(IF(semester_courses.semester_id IS NOT NULL, 1, 0)) AS absolute
                FROM seminare
                    LEFT JOIN semester_courses ON (seminare.Seminar_id = semester_courses.course_id)
                WHERE start_time <= :beginn
                    AND (semester_courses.semester_id IS NULL OR semester_courses.semester_id = :semester_id)
            ";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':beginn', $this['beginn']);
            $statement->bindValue(':semester_id', $this['semester_id']);
            $statement->execute();
            $this->seminar_counts = $statement->fetch(PDO::FETCH_ASSOC);
        }

        $index = str_replace('_seminars_count', '', $field);
        return (int)$this->seminar_counts[$index];
    }

    /**
     * Returns the calendar week number of the first week of the lecture
     * period.
     *
     * @return int Calendar week number of the first week of lecture
     */
    public function getFirstSemesterWeek()
    {
        return (int)strftime('%W', $this['vorles_beginn']);
    }

    /**
     * Returns the calendar week number of the last week of the lecture
     * period.
     *
     * @return int Calendar week number of the last week of lecture
     */
    public function getLastSemesterWeek()
    {
        return (int)strftime('%W', $this['vorles_ende']);
    }

    /**
     * Return whether this semester is in the past.
     *
     * @return bool Indicating whether this semester is in the past
     */
    public function isPast()
    {
        return $this->ende < time();
    }

    /**
     * Returns whether this semester is the current semester.
     *
     * @return bool Indicating if this is the current semester
     */
    public function isCurrent()
    {
        return time() >= $this->beginn && time() < $this->ende;
    }

    /**
     * Returns the start week dates for this semester (and other
     * semesters if duration is > 0).
     *
     * @param mixed $duration Duration time (false to restrict to current
     *                        semester, -1 for indefinite duration, otherwise
     *                        the int value for the duration so that
     *                        semester start + duration = end)
     * @return array containing the start weeks
     */
    public function getStartWeeks($duration = false)
    {
        if ($duration === false) {
            $end_semester = $this;
        } elseif ($duration == -1) {
            $semesters = self::getAll();
            $end_semester = end($semesters);
        } else {
            $end_semester = self::findByTimestamp((int)$this->beginn + (int)$duration);
        }

        $timestamp = $this->getCorrectedLectureBegin();
        $end_date = $end_semester->vorles_ende;

        $i = 0;

        $start_weeks = [];
        while ($timestamp < $end_date) {
            $start_weeks[$i] = sprintf(
                _('%u. Semesterwoche (ab %s)'),
                $i + 1,
                strftime('%x', $timestamp));

            $i += 1;

            $timestamp = strtotime('+1 week', $timestamp);
        }

        return $start_weeks;
    }

    /**
     * Returns the corrected begin of lectures which ensures that the begin
     * is always on a monday.
     *
     * @return int unix timestamp of correct begin of lectures
     */
    public function getCorrectedLectureBegin()
    {
        $dow = (int)date('w', $this->vorles_beginn);

        // Date is already on a monday
        if ($dow === 1) {
            return $this->vorles_beginn;
        }

        // Saturday or sunday: return next monday
        if ($dow === 0 || $dow === 6) {
            return strtotime('next monday', $this->vorles_beginn);
        }

        // Otherwise return last monday
        return strtotime('last monday', $this->vorles_beginn);
    }


    /**
     * returns "Semesterwoche" for a given timestamp
     * @param integer $timestamp
     * @return number|boolean
     */
    public function getSemWeekNumber($timestamp)
    {
        $current_sem_week = (int)strftime('%W', $timestamp);
        if (strftime('%Y', $timestamp) > strftime('%Y', $this->vorles_beginn)) {
            $current_sem_week += 52;
        }
        if ($this->last_sem_week < $this->first_sem_week) {
            $last_sem_week = (int)$this->last_sem_week + 52;
        } else {
            $last_sem_week = $this->last_sem_week;
        }
        if ($current_sem_week >= $this->first_sem_week && $current_sem_week <= $last_sem_week) {
            return $current_sem_week - $this->first_sem_week + 1;
        }

        return false;
    }

    /**
     * Returns an array representation of this semester.
     *
     * @param mixed $only_these_fields List of fields to extract
     * @return array represenation
     */
    public function toArray($only_these_fields = null)
    {
        if (!isset($only_these_fields)) {
            $fields = array_flip(array_diff($this->known_slots, array_keys($this->relations)));
            unset($fields['absolute_seminars_count']);
            unset($fields['duration_seminars_count']);
            unset($fields['continuous_seminars_count']);
            $only_these_fields = array_flip($fields);
        }
        return parent::toArray($only_these_fields);
    }

    /**
     * Flushes the cache just after storing and deleting a semester
     */
    public function refreshCache()
    {
        StudipCacheFactory::getCache()->expire('DB_SEMESTER_DATA');
    }
}
