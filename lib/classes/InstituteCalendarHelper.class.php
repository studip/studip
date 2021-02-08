<?php

/**
 * InstituteCalendarHelper.class.php - class for institute calendar convenience functions
 *
 * @author      Timo Hartge <hartge@data-quest>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 */

class InstituteCalendarHelper
{
    const COLUMN_DATAFIELD_ID = '69f6485f3c937766866a03d9d642ecbb';
    const COLOR_DATAFIELD_ID = '41cda2be71fe9efd6e28b853fc0681f3';
    const INST_DEFAULT_COLOR_DATAFIELD_ID = '0c63321a8e93b3ccc927611709248e07';
    const DEFAULT_EVENT_COLOR = '#899ab9';

    /**
     * Returns the default calendar columns.
     *
     * @return array   default column names
     */
    private static function getDefaultColumns()
    {
        return [
            1 => ['Spalte 1', 1],
            2 => ['Spalte 2', 1],
            3 => ['Spalte 3', 1],
            4 => ['Spalte 4', 1],
            5 => ['Spalte 5', 1],
            6 => ['Spalte 6', 1]
        ];
    }

    /**
     * Fetches the stores columns merged with defaults.
     *
     * @param string $institut_id
     * @param boolean $only_visible only return visible columns
     *
     * @return array column array in a fullcalender expected format
     */
    public static function getResourceColumns($institut_id, $only_visible = false)
    {
        /*
        returns the columns in following format:
            $columns = [
                ['id' => '0', 'title' => 'Sammelspalte'],
                ['id' => '1', 'title' => 'Spalte 1']
            ];
        */

        $columns = [];
        $inst_columns = [
            0 => ['Sammelspalte', 1]
        ];

        $db_inst_columns = InstitutePlanColumn::findByInstitute($institut_id);

        if ($db_inst_columns) {
            foreach ($db_inst_columns as $col_info) {
                $inst_columns[$col_info['column']] = [$col_info['name'], $col_info['visible']];
            }
        } else {
            $inst_columns = array_merge($inst_columns, self::getDefaultColumns());
        }
        foreach ($inst_columns as $id => $info) {
            if ($only_visible && !$info[1]) continue;
            $columns[] = ['id' => $id, 'title' => $info[0], 'visible' => $info[1]];
        }
        return $columns;
    }

    /**
     * Adds the default resource columns
     *
     * @param string $institut_id
     *
     * @return int last resource column number
     */
    public static function addDefaultResourceColumns($institut_id)
    {
        $max_col = 0;
        foreach (self::getDefaultColumns() as $col_id => $col_info) {
            $new_col = new InstitutePlanColumn([$institut_id, $col_id]);
            $new_col->name = $col_info[0];
            $new_col->visible = $col_info[1];
            if ($new_col->store()) {
                $max_col = $col_id;
            }
        }
        return $max_col;
    }

    /**
     * Adds a resource column
     *
     * @param string $institut_id
     * @param string $name
     * @param int $specific_column_number
     *
     * @return int number of affected rows
     */
    public static function addResourceColumn($institut_id, $name, $specific_column_number = 0)
    {
        $last_col = InstitutePlanColumn::getLastColumnOfInstitute($institut_id);
        if ($last_col !== null) {
            $max_col = $last_col->column;
        } else {
            $max_col = self::addDefaultResourceColumns($institut_id);
        }
        $column_number = $specific_column_number>0?$specific_column_number:intval($max_col)+1;
        $new_col = new InstitutePlanColumn([$institut_id, $column_number]);
        $new_col->name = $name;
        $new_col->visible = 1;
        return $new_col->store();
    }

    /**
     * Looks up column id for course events
     *
     * @param Course $course
     *
     * @return array course events with column id
     */
    public static function getCourseEventcolumns(Course $course)
    {
        $df = DatafieldEntryModel::findByModel($course, self::COLUMN_DATAFIELD_ID);
        if ($df[0] && $df[0]->content) {
            $event_columns = unserialize($df[0]->content);
        } else {
            $event_columns = [];
        }
        return $event_columns;
    }

    /**
     * Sets the column id for course events
     *
     * @param Course $course
     * @param string $event_id SeminarCycleDate id
     * @param string $institut_id
     * @param string $column number of the column
     *
     * @return bool stored
     */
    public static function setCourseEventcolumn($course, $event_id, $institut_id, $column)
    {
        $df = DatafieldEntryModel::findByModel($course, self::COLUMN_DATAFIELD_ID);
        if ($df[0]) {
            $event_columns = self::getCourseEventcolumns($course);
            if (!is_array($event_columns[$event_id])) {
                unset($event_columns[$event_id]);
            }
            $event_columns[$event_id][$institut_id] = $column;
            $df[0]->content = serialize($event_columns);
            return $df[0]->store();
        }
        return false;
    }

    /**
     * Looks up color value for course events
     *
     * @param Course $course
     *
     * @return array course events with color value
     */
    public static function getCourseEventcolors($course)
    {
        $df = DatafieldEntryModel::findByModel($course, self::COLOR_DATAFIELD_ID);
        if ($df[0] && $df[0]->content) {
            $event_colors = unserialize($df[0]->content);
        } else {
            $event_colors = [];
        }
        return $event_colors;
    }

    /**
     * Sets color value for course events
     *
     * @param Course $course
     * @param string $event_id SeminarCycleDate id
     * @param string $institut_id
     * @param string $color colorcode
     *
     * @return bool stored
     */
    public static function setCourseEventcolor($course, $event_id, $institut_id, $color)
    {
        $df = DatafieldEntryModel::findByModel($course, self::COLOR_DATAFIELD_ID);
        if ($df[0]) {
            $event_colors = self::getCourseEventcolors($course);
            if (!is_array($event_colors[$event_id])) {
                unset($event_colors[$event_id]);
            }
            $event_colors[$event_id][$institut_id] = $color;
            $df[0]->content = serialize($event_colors);
            return $df[0]->store();
        }
        return false;
    }

    /**
     * Looks up default color value for institute course events
     *
     * @param Course $course
     *
     * @return array course events with color value
     */
    public static function getInstituteDefaultEventcolors($institut)
    {
        $df = DatafieldEntryModel::findByModel($institut, self::INST_DEFAULT_COLOR_DATAFIELD_ID);
        if ($df && $df[0]->content) {
            $event_colors = unserialize($df[0]->content);
        } else {
            $event_colors = [];
        }
        return $event_colors;
    }

    /**
     * Sets default institute course events color value for semtypes
     *
     * @param Institut $institut
     * @param string $semtype
     * @param string $color colorcode
     *
     * @return bool stored
     */
    public static function setInstituteDefaultEventcolor($institut, $semtype, $color)
    {
        $df = DatafieldEntryModel::findByModel($institut, self::INST_DEFAULT_COLOR_DATAFIELD_ID);
        if ($df[0]) {
            $event_colors = self::getInstituteDefaultEventcolors($institut);
            $event_colors[$semtype['name']] = $color;
            $df[0]->content = serialize($event_colors);
            return $df[0]->store();
        }
        return false;
    }

    /**
     * Sets color value for every course events of given courses semtype
     *
     * @param Course $course
     * @param string $institut_id
     * @param string $color colorcode
     *
     * @return bool stored
     */
    public static function setSemtypeEventcolor($course, $institut_id, $color)
    {
        $semtype = $course->getSemType();
        $institut = Institute::find($institut_id);
        if ($institut) {
            self::setInstituteDefaultEventcolor($institut, $semtype, $color);
        }
        $courses = Course::findBySQL('status =? AND Institut_id=?', [$semtype['id'], $course->institut_id]);
        if ($courses) {
            foreach ($courses as $semtype_course) {
                foreach (SeminarCycleDate::findBySeminar($semtype_course->seminar_id) as $cycle_date) {
                    self::setCourseEventcolor($semtype_course, $cycle_date->id, $institut_id, $color);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Prepares an array of course id and names for creation of dropable calendar events
     *
     * @param array $courses Array of courses
     * @param array $semester Semester
     *
     * @return array prepared array
     */
    public static function getEventlessCourses($courses, $semester = null)
    {
        $eventless = [];
        foreach (array_keys($courses) as $cid) {
            $course = Course::find($cid);
            $cycle_dates = SeminarCycleDate::findBySeminar($course->seminar_id);
            if (count($cycle_dates) < 1) {
                $eventless[$cid] = $course->getFullname('number-name');
            } elseif ($semester) {
                $has_date_in_semester = false;
                foreach ($cycle_dates as $cycle_date) {
                    foreach ($cycle_date->getAllDates() as $course_date) {
                        if ($course_date->date >= $semester->beginn && $course_date->date <= $semester->ende) {
                            $has_date_in_semester = true;
                            break;
                        }
                    }
                    if ($has_date_in_semester) break;
                }
                if (!$has_date_in_semester) {
                    $eventless[$cid] = $course->getFullname('number-name');
                }
            }
        }
        return $eventless;
    }

    /**
     * Creates FullCalendar event date of course events
     *
     * @param array $courses Array of courses
     * @param string $institut_id
     * @param array $semester Semester
     * @param array $specific_weekday fetch only events for specific weekday
     *
     * @return array fullcalendar events
     */
    public static function getEvents($courses, $institut_id, $semester = null, $specific_weekday = null)
    {
        $events = [];
        $today = date('w');

        $user_insts = array_map(function ($arr) {
            return $arr['Institut_id'];
        }, Institute::getMyInstitutes($GLOBALS['user']->id));

        $min_time = explode(':', Config::get()->INSTITUTE_COURSE_PLAN_START_HOUR);
        $minbigtime = (int) $min_time[0];
        $max_time = explode(':', Config::get()->INSTITUTE_COURSE_PLAN_END_HOUR);
        $maxbigtime = (int) $max_time[0];

        $institut = Institute::find($institut_id);
        $inst_default_colors = self::getInstituteDefaultEventcolors($institut);

        Course::findAndMapMany(function ($course) use (
            $courses,
            &$events,
            $today,
            $minbigtime,
            $maxbigtime,
            $user_insts,
            $institut_id,
            $inst_default_colors,
            $semester,
            $specific_weekday
        ) {
            $semtype = $course->getSemType();

            $event_columns = self::getCourseEventcolumns($course);
            $event_colors = self::getCourseEventcolors($course);

            if (in_array($course->institut_id, $user_insts)) {
                $is_editable = true;
                $is_start_editable = !LockRules::Check($course->id, 'room_time');
                $is_duration_editable = false;
            } else {
                $is_editable = false;
                $is_start_editable = false;
                $is_duration_editable = false;
            }

            foreach (SeminarCycleDate::findBySeminar($course->seminar_id) as $cycle_date) {
                if ($semester) {
                    $has_date_in_semester = false;
                    foreach ($cycle_date->getAllDates() as $course_date) {
                        if ($course_date->date >= $semester->beginn && $course_date->date <= $semester->ende) {
                            $has_date_in_semester = true;
                            break;
                        }
                    }
                    if (!$has_date_in_semester) {
                        continue;
                    }
                }

                if (is_numeric($specific_weekday) && $specific_weekday != $cycle_date['weekday']) {
                    continue;
                }

                $conform = true;

                if ($cycle_date['weekday'] == 0) {
                    $day_offset = 7 - $today;
                } else {
                    $day_offset = $cycle_date['weekday'] - $today;
                }

                $start_time = explode(':', $cycle_date['start_time']);
                $bigtime = (int) $start_time[0];
                if ($bigtime > $maxbigtime || $bigtime < $minbigtime) {
                    $conform = false;
                } elseif ($bigtime % 2) {
                    $bigtime--;
                }
                $start_time = $bigtime . ':00:00';

                $end_time = explode(':', $start_time);
                $end_time[0] += 2;
                $end_time = implode(':', $end_time);

                $move_url = URLHelper::getURL('dispatch.php/admin/courseplanning/move_event');
                $name = $course->getFullname('number-name');

                if ($start_time != $cycle_date['start_time'] && $end_time != $cycle_date['end_time']) {
                    $start = self::iso8601date(strtotime($day_offset . ' days'), $cycle_date['start_time']);
                    $end = self::iso8601date(strtotime($day_offset . ' days'), $cycle_date['end_time']);
                    $backgroundcolor = '#6c737a';
                    $textcolor = '#ffffff';
                } else {
                    $start = self::iso8601date(strtotime($day_offset . ' days'), $start_time);
                    $end = self::iso8601date(strtotime($day_offset . ' days'), $end_time);

                    $backgroundcolor = null;
                    if (array_key_exists($cycle_date->id, $event_colors)) {
                        if (is_array($event_colors[$cycle_date->id]) && array_key_exists($institut_id, $event_colors[$cycle_date->id])) {
                            $backgroundcolor = $event_colors[$cycle_date->id][$institut_id];
                        }
                    }
                    if (!$backgroundcolor) {
                        $backgroundcolor = array_key_exists($semtype['name'], $inst_default_colors)
                                         ? $inst_default_colors[$semtype['name']]
                                         : self::DEFAULT_EVENT_COLOR;
                    }

                    $textcolor = '#ffffff';
                    if (self::calculateLuminosityRatio($backgroundcolor, $textcolor) < 3) {
                        $textcolor = '#000000';
                    }
                }
                if (!$is_editable) {
                    $backgroundcolor = '#c4c7c9';
                    $textcolor = '#000000';
                }

                $resource_column = '0';
                if (array_key_exists($cycle_date->id, $event_columns)) {
                    if (is_array($event_columns[$cycle_date->id]) && array_key_exists($institut_id, $event_columns[$cycle_date->id])) {
                        $resource_column = $event_columns[$cycle_date->id][$institut_id];
                    }
                }

                $events[] = [
                    'resourceId'       => $resource_column,
                    'id'               => $cycle_date->id,
                    'title'            => $name,
                    'start'            => $start,
                    'end'              => $end,
                    'textColor'        => $textcolor,
                    'backgroundColor'  => $backgroundcolor,
                    'borderColor'      => '#000',
                    'editable'         => $is_editable,
                    'startEditable'    => $is_start_editable,
                    'durationEditable' => $is_duration_editable,
                    'resourceEditable' => true,
                    'studip_api_urls'  => ['move' => $move_url],
                    'studip_view_urls' => ['edit' => URLHelper::getURL('dispatch.php/course/details/index/' . $cycle_date->seminar_id)],
                    'metadate_id'      => $cycle_date->metadate_id,
                    'course_id'        => $cycle_date->seminar_id,
                    'tooltip'          => self::getCycleInfos($course, $cycle_date),
                    'icon'             => $is_start_editable ? '' : 'lock-locked',
                    'conform'          => $conform,
                ];
            }
        }, array_keys($courses));

        return $events;
    }

    /**
     * Creates a fullcalendar event of given SeminarCycleDate
     *
     * @param SeminarCycleDate $cycle_date
     * @param string $institut_id
     *
     * @return string enriched course info string for tooltip
     */
    public static function getCycleEvent($cycle_date, $institut_id)
    {
        $course = Course::find($cycle_date->seminar_id);
        $semtype = $course->getSemType();
        $institut = Institute::find($institut_id);
        $inst_default_colors = self::getInstituteDefaultEventcolors($institut);

        $today = date('w');
        $user_insts = array_map(function ($arr) {
            return $arr['Institut_id'];
        }, Institute::getMyInstitutes($GLOBALS['user']->id));

        $min_time = explode(':', Config::get()->INSTITUTE_COURSE_PLAN_START_HOUR);
        $minbigtime = (int) $min_time[0];
        $max_time = explode(':', Config::get()->INSTITUTE_COURSE_PLAN_END_HOUR);
        $maxbigtime = (int) $max_time[0];

        $start_time = explode(':', $cycle_date['start_time']);
        $bigtime = (int) $start_time[0];

        if ($bigtime > $maxbigtime || $bigtime < $minbigtime) {
            return null;
        } elseif ($bigtime % 2) {
            $bigtime--;
        }
        $start_time = $bigtime . ':00:00';

        $end_time = explode(':', $start_time);
        $end_time[0] += 2;
        $end_time = implode(':', $end_time);

        if (in_array($course->institut_id, $user_insts)) {
            $is_editable = true;
            $is_start_editable = !LockRules::Check($cycle_date->seminar_id, 'room_time');
            $is_duration_editable = false;
        } else {
            $is_editable = false;
            $is_start_editable = false;
            $is_duration_editable = false;
        }

        if ($cycle_date['weekday'] == 0) {
            $day_offset = (7 - $today);
        } else {
            $day_offset = ($cycle_date['weekday'] - $today);
        }

        $event_columns = self::getCourseEventcolumns($course);
        $event_colors = self::getCourseEventcolors($course);

        $move_url = URLHelper::getURL('dispatch.php/admin/courseplanning/move_event');
        $name = $course->getFullname('number-name');

        if ($start_time != $cycle_date['start_time'] && $end_time != $cycle_date['end_time']) {
            $start = self::iso8601date(strtotime($day_offset . ' days'), $cycle_date['start_time']);
            $end = self::iso8601date(strtotime($day_offset . ' days'), $cycle_date['end_time']);
            $backgroundcolor = '#6c737a';
            $textcolor = '#ffffff';
        } else {
            $start = self::iso8601date(strtotime($day_offset . ' days'), $start_time);
            $end = self::iso8601date(strtotime($day_offset . ' days'), $end_time);

            $backgroundcolor = null;
            if (array_key_exists($cycle_date->id, $event_colors)) {
                if (is_array($event_colors[$cycle_date->id]) && array_key_exists($institut_id, $event_colors[$cycle_date->id])) {
                    $backgroundcolor = $event_colors[$cycle_date->id][$institut_id];
                }
            }
            if (!$backgroundcolor) {
                $backgroundcolor = array_key_exists($semtype['name'], $inst_default_colors)?$inst_default_colors[$semtype['name']]:self::DEFAULT_EVENT_COLOR;
            }

            $textcolor = '#ffffff';
            if (self::calculateLuminosityRatio($backgroundcolor, $textcolor) < 3) {
                $textcolor = '#000000';
            }
        }
        if (!$is_editable) {
            $backgroundcolor = '#c4c7c9';
            $textcolor = '#000000';
        }

        $resource_column = '0';
        if (array_key_exists($cycle_date->id, $event_columns)) {
            if (is_array($event_columns[$cycle_date->id]) && array_key_exists($institut_id, $event_columns[$cycle_date->id])) {
                $resource_column = $event_columns[$cycle_date->id][$institut_id];
            }
        }

        return [
            'resourceId'       => $resource_column,
            'id'               => $cycle_date->id,
            'title'            => $name,
            'start'            => $start,
            'end'              => $end,
            'textColor'        => $textcolor,
            'backgroundColor'  => $backgroundcolor,
            'borderColor'      => '#000',
            'editable'         => $is_editable,
            'startEditable'    => $is_start_editable,
            'durationEditable' => $is_duration_editable,
            'resourceEditable' => true,
            'studip_api_urls'  => ['move' => $move_url],
            'studip_view_urls' => ['edit' => URLHelper::getURL('dispatch.php/course/details/index/' . $cycle_date->seminar_id)],
            'metadate_id'      => $cycle_date->metadate_id,
            'course_id'        => $cycle_date->seminar_id,
            'tooltip'          => self::getCycleInfos($course, $cycle_date),
            'icon'             => $is_start_editable ? '' : 'lock-locked'
        ];
    }

    /**
     * Creates a string with course infos to be displayed as a tooltip in calendar events
     *
     * @param SeminarCycleDate $cycle_date
     *
     * @return string enriched course info string for tooltip
     */
    private static function getCycleInfos($course, $cycle_date)
    {
        $info_string = '';

        $info_string .= $course->getFullname('number-name') . "\n";

        $dozenten = [];
        foreach (CourseMember::findByCourseAndStatus($course->id, 'dozent') as $cmember) {
            $dozenten[$cmember->user->user_id] = $cmember->user->getFullname();
        }
        if ($dozenten) {
            $info_string .= implode(', ', $dozenten) . "\n";
        }

        $rooms = [];
        foreach ($cycle_date->getAllDates() as $course_date) {
            $room = $course_date->getRoom();
            if ($room->id) {
                $rooms[$room->id] = $room->name;
            }
        }
        if ($rooms) {
            $info_string .= implode(', ', $rooms) . "\n";
        }

        if ($course->getSemClass()->offsetGet('module')) {
            $mvv_pathes = [];
            $course_start = $course->start_time;
            $course_end = ($course->end_time < 0 || is_null($course->end_time))
                        ? PHP_INT_MAX
                        : $course->end_time;
            // set filter to show only pathes with valid semester data
            ModuleManagementModelTreeItem::setObjectFilter('Modul',
                function ($modul) use ($course_start, $course_end) {
                    // check for public status
                    if (!$GLOBALS['MVV_MODUL']['STATUS']['values'][$modul->stat]['public']) {
                        return false;
                    }
                    $modul_start = Semester::find($modul->start)->beginn ?: 0;
                    $modul_end = Semester::find($modul->end)->ende ?: PHP_INT_MAX;
                    return ($modul_start <= $course_end && $modul_end >= $course_start);
                }
            );

            ModuleManagementModelTreeItem::setObjectFilter('StgteilVersion',
                function ($version) {
                    return (bool) $GLOBALS['MVV_STGTEILVERSION']['STATUS']['values'][$version->stat]['public'];
                }
            );

            $trail_classes = ['Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion'];
            $mvv_object_pathes = MvvCourse::get($course->getId())->getTrails($trail_classes);
            if ($mvv_object_pathes) {
                if (Config::get()->COURSE_SEM_TREE_DISPLAY) {
                    $mvv_tree = [];
                    foreach ($mvv_object_pathes as $mvv_object_path) {
                        // show only complete pathes
                        if (count($mvv_object_path) == 4) {
                            // flatten the pathes to a linked list
                            $stg = reset($mvv_object_path);
                            $parent_id = 'root';
                            foreach ($mvv_object_path as $mvv_object) {
                                $mvv_object_id = $mvv_object instanceof StgteilabschnittModul
                                        ? $mvv_object->modul_id
                                        : $mvv_object->id;
                                $mvv_tree[$parent_id][$mvv_object_id] = [
                                    'id'    => $mvv_object_id,
                                    'name'  => $mvv_object->getDisplayName(),
                                    'class' => get_class($mvv_object),
                                 ];
                                $parent_id = $mvv_object_id;
                            }
                        }
                    }
                    if (count($mvv_tree)) {
                        // add the root node
                        $mvv_tree['start'][] = [
                            'id'    => 'root',
                            'name'  => Config::get()->UNI_NAME_CLEAN,
                            'class' => ''
                        ];
                    }
                } else {
                    foreach ($mvv_object_pathes as $mvv_object_path) {
                        // show only complete pathes
                        if (count($mvv_object_path) == 4) {
                            $mvv_object_names = [];
                            $modul_id = '';
                            foreach ($mvv_object_path as $mvv_object) {
                                if ($mvv_object instanceof StgteilabschnittModul) {
                                    $modul_id = $mvv_object->modul_id;
                                }
                                $mvv_object_names[] = $mvv_object->getDisplayName();
                            }
                            $mvv_pathes[] = [$modul_id => $mvv_object_names];
                        }
                    }
                }
                // to prevent collisions of object ids in the tree
                // in the case of same objects listed in more than one part
                // of the tree
                $id_sfx = new stdClass();
                $id_sfx->c = 1;
            }
            foreach ($mvv_pathes as $mvv_path) {
                foreach ($mvv_path as $mvv_path_content) {
                    $info_string .= implode(' > ', $mvv_path_content) . "\n";
                }
            }
        }

        return $info_string;
    }

    public static function getBackgroundEvents($start = null)
    {
        $datetime = new DateTime();
        $slot_duration = 1;
        $start_time = 8;
        $end_time = 16;
        $events = [];
        $day_interval = 1;

        if ($start == null) {
            $datetime->modify('monday this week');
            $datetime->add(new DateInterval('PT' . $start_time . 'H'));
            $day_interval = 7;
        }

        for ($i = 1; $i <= $day_interval; $i++) {
            for ($slot = $start_time; $slot < $end_time; $slot += $slot_duration) {
                if ($slot % 2) {
                    $datetime->setTime($slot, 0);
                    $events[] = [
                        'start'     => self::iso8601date($datetime, $slot),
                        'end'       => self::iso8601date($datetime, $slot + $slot_duration),
                        'rendering' => 'background',
                    ];

                }
            }
            $datetime->setTime($start_time, 0);
            $datetime->add(new DateInterval('P1D'));
        };
        return $events;
    }

    private static function iso8601date($date, $time = '00:00:00', $timezone = '+00:00')
    {
        // If only date parameter is passed and is a DateTimeInterface object or
        // unix timestamp, assume time from date
        if (func_num_args() === 1 && ($date instanceof DateTimeInterface || ctype_digit($date))) {
            $time = $date;
        }

        // Get time
        if ($time instanceof DateTimeInterface) {
            $time = $time->format('H:i:s');
        } elseif (ctype_digit($time)) {
            $time = date('H:i:s', $time);
        } elseif (sscanf($time, '%u:%u:%u', $hours, $minutes, $seconds)) {
            $time = sprintf('%02u:%02u:%02u', $hours, $minutes, $seconds);
        }

        // Get date
        if ($date instanceof DateTimeInterface) {
            $date = $date->format('Y-m-d');
        } elseif (ctype_digit($date)) {
            $date = date('Y-m-d', $date);
        }

        return "{$date}T{$time}{$timezone}";
    }


    // calculates the luminosity of an given RGB color
    // the color code must be in the format of RRGGBB
    // the luminosity equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#relativeluminancedef
    private static function calculateLuminosity($color)
    {
        $r = hexdec(substr($color, 0, 2)) / 255; // red value
        $g = hexdec(substr($color, 2, 2)) / 255; // green value
        $b = hexdec(substr($color, 4, 2)) / 255; // blue value
        if ($r <= 0.03928) {
            $r = $r / 12.92;
        } else {
            $r = pow(($r + 0.055) / 1.055, 2.4);
        }
        if ($g <= 0.03928) {
            $g = $g / 12.92;
        } else {
            $g = pow(($g + 0.055) / 1.055, 2.4);
        }
        if ($b <= 0.03928) {
            $b = $b / 12.92;
        } else {
            $b = pow(($b + 0.055) / 1.055, 2.4);
        }
        $luminosity = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        return $luminosity;
    }

    // calculates the luminosity ratio of two colors
    // the luminosity ratio equations are from the WCAG 2 requirements
    // http://www.w3.org/TR/WCAG20/#contrast-ratiodef
    private static function calculateLuminosityRatio($color1, $color2)
    {
        $c1 = ltrim($color1, '#');
        $c2 = ltrim($color2, '#');
        $l1 = self::calculateLuminosity($c1);
        $l2 = self::calculateLuminosity($c2);
        if ($l1 > $l2) {
            $ratio = ($l1 + 0.05) / ($l2 + 0.05);
        } else {
            $ratio = ($l2 + 0.05) / ($l1 + 0.05);
        }
        return $ratio;
    }
}
