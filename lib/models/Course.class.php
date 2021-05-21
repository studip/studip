<?php
/**
 * Course.class.php
 * model class for table seminare
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string seminar_id database column
 * @property string id alias column for seminar_id
 * @property string veranstaltungsnummer database column
 * @property string institut_id database column
 * @property string name database column
 * @property string untertitel database column
 * @property string status database column
 * @property string beschreibung database column
 * @property string ort database column
 * @property string sonstiges database column
 * @property string lesezugriff database column
 * @property string schreibzugriff database column
 * @property string start_time database column
 * @property string duration_time database column
 * @property string art database column
 * @property string teilnehmer database column
 * @property string vorrausetzungen database column
 * @property string lernorga database column
 * @property string leistungsnachweis database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string ects database column
 * @property string admission_turnout database column
 * @property string admission_binding database column
 * @property string admission_prelim database column
 * @property string admission_prelim_txt database column
 * @property string admission_disable_waitlist database column
 * @property string visible database column
 * @property string showscore database column
 * @property string modules database column
 * @property string aux_lock_rule database column
 * @property string aux_lock_rule_forced database column
 * @property string lock_rule database column
 * @property string admission_waitlist_max database column
 * @property string admission_disable_waitlist_move database column
 * @property string completion database column
 * @property string parent_course database column
 * @property string end_time computed column read/write
 * @property SimpleORMapCollection topics has_many CourseTopic
 * @property SimpleORMapCollection dates has_many CourseDate
 * @property SimpleORMapCollection ex_dates has_many CourseExDate
 * @property SimpleORMapCollection members has_many CourseMember
 * @property SimpleORMapCollection deputies has_many Deputy
 * @property SimpleORMapCollection statusgruppen has_many Statusgruppen
 * @property SimpleORMapCollection admission_applicants has_many AdmissionApplication
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property SimpleORMapCollection cycles has_many SeminarCycleDate
 * @property Semester start_semester belongs_to Semester
 * @property Semester end_semester belongs_to Semester
 * @property Institute home_institut belongs_to Institute
 * @property AuxLockRule aux belongs_to AuxLockRule
 * @property SimpleORMapCollection study_areas has_and_belongs_to_many StudipStudyArea
 * @property SimpleORMapCollection institutes has_and_belongs_to_many Institute
 * @property Course parent belongs_to Course
 * @property SimpleORMapCollection children has_many Course
 * @property CourseConfig config additional field
 */

class Course extends SimpleORMap implements Range, PrivacyObject, StudipItem, FeedbackRange
{

    /**
     * Returns the currently active course or false if none is active.
     *
     * @return Course object of currently active course, null otherwise
     * @since 3.0
     */
    public static function findCurrent()
    {
        if (Context::isCourse()) {
            return Context::get();
        }

        return null;
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'seminare';
        $config['has_many']['topics'] = [
            'class_name' => 'CourseTopic',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['dates'] = [
            'class_name'        => 'CourseDate',
            'assoc_foreign_key' => 'range_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'order_by'          => 'ORDER BY date'
        ];
        $config['has_many']['ex_dates'] = [
            'class_name'        => 'CourseExDate',
            'assoc_foreign_key' => 'range_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];
        $config['has_many']['members'] = [
            'class_name' => 'CourseMember',
            'assoc_func' => 'findByCourse',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['deputies'] = [
            'class_name' => 'Deputy',
            'assoc_func' => 'findByRange_id',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['statusgruppen'] = [
            'class_name' => 'Statusgruppen',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['admission_applicants'] = [
            'class_name' => 'AdmissionApplication',
            'assoc_func' => 'findByCourse',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['datafields'] = [
            'class_name' => 'DatafieldEntryModel',
            'assoc_func' => 'findByModel',
            'assoc_foreign_key' => function ($model, $params) {
                $model->setValue('range_id', $params[0]->id);
            },
            'foreign_key' => function ($course) {
                return [$course];
            },
            'on_delete' => 'delete',
            'on_store'  => 'store',
        ];
        $config['has_many']['cycles'] = [
            'class_name' => 'SeminarCycleDate',
            'assoc_func' => 'findBySeminar',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['blubberthreads'] = [
            'class_name' => 'BlubberThread',
            'assoc_func' => 'findBySeminar',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];

        $config['has_and_belongs_to_many']['semesters'] = [
            'class_name'     => 'Semester',
            'thru_table'     => 'semester_courses',
            'thru_key'       => 'course_id',
            'thru_assoc_key' => 'semester_id',
            'order_by'       => 'ORDER BY beginn ASC',
            'on_delete'      => 'delete',
            'on_store'       => 'store',
        ];

        $config['belongs_to']['home_institut'] = [
            'class_name'  => 'Institute',
            'foreign_key' => 'institut_id',
            'assoc_func'  => 'find',
        ];
        $config['belongs_to']['aux'] = [
            'class_name'  => 'AuxLockRule',
            'foreign_key' => 'aux_lock_rule',
        ];
        $config['has_and_belongs_to_many']['study_areas'] = [
            'class_name' => 'StudipStudyArea',
            'thru_table' => 'seminar_sem_tree',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_and_belongs_to_many']['institutes'] = [
            'class_name' => 'Institute',
            'thru_table' => 'seminar_inst',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];

        $config['has_and_belongs_to_many']['domains'] = [
            'class_name'        => 'UserDomain',
            'thru_table'        => 'seminar_userdomains',
            'ondelete'          => 'delete',
            'onstore'           => 'store',
            'order_by'          => 'ORDER BY name',
        ];

        $config['has_many']['room_requests'] = [
            'class_name'        => 'RoomRequest',
            'assoc_foreign_key' => 'course_id',
            'on_delete'         => 'delete',
        ];
        $config['belongs_to']['parent'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'parent_course'
        ];
        $config['has_many']['children'] = [
            'class_name'        => 'Course',
            'assoc_foreign_key' => 'parent_course',
            'order_by'          => 'GROUP BY seminar_id ORDER BY VeranstaltungsNummer, Name'
        ];

        $config['has_one']['courseware'] = [
            'class_name' => \Courseware\StructuralElement::class,
            'assoc_func' => 'getCoursewareCourse',
        ];

        $config['default_values']['lesezugriff'] = 1;
        $config['default_values']['schreibzugriff'] = 1;
        $config['default_values']['duration_time'] = 0;

        $config['additional_fields']['end_time'] = true;

        $config['additional_fields']['start_semester'] = [
            'get' => 'getStartSemester'
        ];
        $config['additional_fields']['end_semester'] = [
            'get' => 'getEndSemester'
        ];
        $config['additional_fields']['semester_text'] = [
            'get' => 'getTextualSemester'
        ];

        $config['additional_fields']['config'] = [
            'get' => function (Course $course) {
                return $course->getConfiguration();
            }
        ];

        $config['notification_map']['after_create'] = 'CourseDidCreateOrUpdate';
        $config['notification_map']['after_store'] = 'CourseDidCreateOrUpdate';

        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['untertitel'] = true;
        $config['i18n_fields']['beschreibung'] = true;
        $config['i18n_fields']['art'] = true;
        $config['i18n_fields']['teilnehmer'] = true;
        $config['i18n_fields']['vorrausetzungen'] = true;
        $config['i18n_fields']['lernorga'] = true;
        $config['i18n_fields']['leistungsnachweis'] = true;
        $config['i18n_fields']['ort'] = true;

        $config['additional_fields']['config']['get'] = function ($course) {
            return CourseConfig::get($course->id);
        };

        $config['registered_callbacks']['before_update'][] = 'logStore';
        $config['registered_callbacks']['after_delete'][] = function ($course) {
            CourseAvatar::getAvatar($course->id)->reset();
            FeedbackElement::deleteBySQL('course_id = ?', [$course->id]);
            // Remove subcourse relations, leaving subcourses intact.
            DBManager::get()->execute(
                "UPDATE `seminare` SET `parent_course` = NULL WHERE `parent_course` = :course",
                ['course' => $course->id]
            );
        };

        parent::configure($config);
    }

    public function getEnd_Time()
    {
        if (!$this->semesters) {
            return -1;
        }

        return $this->semesters->last()->ende;
    }

    public function setEnd_Time($value)
    {
        throw new Exception("This function is unavailable.");
    }

    /**
     * Sets the start semester of the course.
     */
    public function setStartSemester(Semester $semester)
    {
        $this->start_semester = $semester;
    }

    /**
     * Sets the end semester of the course.
     */
    public function setEndSemester(Semester $semester)
    {
        $this->end_semester = $semester;
    }

    public function setSemesters($semesters)
    {
        $semester_ids = array_map(function ($s) {
            return $s->id;
        }, $semesters);

        if (count($semester_ids) > 0) {
            $delete = DBManager::get()->prepare("
                DELETE FROM semester_courses
                WHERE semester_id NOT IN (:semester_ids)
                    AND course_id = :course_id
            ");
            $delete->execute([
                'semester_ids' => $semester_ids,
                'course_id' => $this->id,
            ]);
        } else {
            $delete = DBManager::get()->prepare("
                DELETE FROM semester_courses
                WHERE course_id = :course_id
            ");
            $delete->execute([
                'course_id' => $this->id,
            ]);
        }
        $insert = DBManager::get()->prepare("
            INSERT IGNORE INTO semester_courses
            SET course_id = :course_id,
                semester_id = :semester_id,
                mkdate = UNIX_TIMESTAMP(),
                chdate = UNIX_TIMESTAMP()
        ");
        foreach ($semesters as $semester) {
            $insert->execute([
                'course_id' => $this->id,
                'semester_id' => $semester->id,
            ]);
        }
        $this->resetRelation('semesters');
    }

    /**
     * Retrieves the first semester of a course, if applicable.
     *
     * @returns Semester|null Either the first semester of the course
     *     or null, if no semester could be found.
     */
    public function getStartSemester()
    {
        if (count($this->semesters) > 0) {
            return $this->semesters->first();
        } else {
            return Semester::findByTimestamp($this['start_time']);
        }
    }

    /**
     * Retrieves the last semester of a course, if applicable.
     *
     * @returns Semester|null Either the last semester of the course
     *     or null, if no semester could be found.
     */
    public function getEndSemester()
    {
        if (count($this->semesters) > 0) {
            return $this->semesters->last();
        }
        return null;
    }

    /**
     * Returns the readable semester duration as as string
     * @return string : readable semester
     */
    public function getTextualSemester()
    {
        if (count($this->semesters) > 1) {
            return $this->start_semester->name . ' - ' . $this->end_semester->name;
        } elseif (count($this->semesters) === 1) {
            return $this->start_semester->name;
        } else {
            return $this->start_semester->name .' - ' . _('unbegrenzt');
        }
    }

    /**
     * Returns true if this course has no end-semester. Else false.
     * @return bool : true if there is no end-semester
     */
    public function isOpenEnded()
    {
        return count($this->semesters) === 0;
    }

    /**
     * Returns if this course is in the given semester
     * @param Semester $semester : instance of the given semester
     * @return bool : true if this course is part of this semester
     */
    public function isInSemester(Semester $semester)
    {
        if (count($this->semesters) > 0) {
            foreach ($this->semesters as $s) {
                if ($s->id === $semester->id) {
                    return true;
                }
            }
            return false;
        } else {
            return $this->start_time <= $semester->beginn;
        }
    }

    public function getFreeSeats()
    {
        $free_seats = $this->admission_turnout - $this->getNumParticipants();
        return max($free_seats, 0);;
    }

    public function isWaitlistAvailable()
    {
        if ($this->admission_disable_waitlist) {
            return false;
        }

        if ($this->admission_waitlist_max) {
            return $this->admission_waitlist_max - $this->getNumWaiting() > 0;
        }

        return true;
    }

    /**
     * Retrieves all members of a status
     *
     * @param String|Array $status        the status to filter with
     * @param bool         $as_collection return collection instead of array?
     * @return Array an array of all those members.
     */
    public function getMembersWithStatus($status, $as_collection = false)
    {
        $result = CourseMember::findByCourseAndStatus($this->id, $status);
        return $as_collection
             ? SimpleCollection::createFromArray($result)
             : $result;
    }

    /**
     * Retrieves the number of all members of a status
     *
     * @param String|Array $status  the status to filter with
     *
     * @return int the number of all those members.
     */
    public function countMembersWithStatus($status)
    {
        return CourseMember::countByCourseAndStatus($this->id, $status);
    }

    public function getNumParticipants()
    {
        return $this->countMembersWithStatus('user autor') + $this->getNumPrelimParticipants();
    }

    public function getNumPrelimParticipants()
    {
        return AdmissionApplication::countBySql(
            "seminar_id = ? AND status = 'accepted'",
            [$this->id]
        );
    }

    public function getNumWaiting()
    {
        return AdmissionApplication::countBySql(
            "seminar_id = ? AND status = 'awaiting'",
            [$this->id]
        );
    }

    public function getParticipantStatus($user_id)
    {
        $p_status = $this->members->findBy('user_id', $user_id)->val('status');
        if (!$p_status) {
            $p_status = $this->admission_applicants->findBy('user_id', $user_id)->val('status');
        }
        return $p_status;
    }

    /**
    * Returns the semType object that is defined for the course
    *
    * @return SemType The semTypeObject for the course
    */
    public function getSemType()
    {
        $semTypes = SemType::getTypes();
        if (isset($semTypes[$this->status])) {
            return $semTypes[$this->status];
        }

        Log::ERROR(sprintf('SemType not found id:%s status:%s', $this->id, $this->status));
        return new SemType(['name' => 'Fehlerhafter Veranstaltungstyp']);
    }

    /**
     * Returns the SemClass object that is defined for the course
     *
     * @return SemClass The SemClassObject for the course
     */
     public function getSemClass()
     {
         return $this->getSemType()->getClass();
     }

    /**
     * Returns the full name of a course. If the important course numbers
     * (IMPORTANT_SEMNUMBER) is set in global configs it will also display
     * the coursenumber
     *
     * @param string formatting template name
     * @return string Fullname
     */
    public function getFullname($format = 'default')
    {
        $template['type-name'] = '%2$s: %1$s';
        $template['number-type-name'] = '%3$s %2$s: %1$s';
        $template['type-number-name'] = '%2$s: %3$s %1$s';
        $template['number-name'] = '%3$s %1$s';
        $template['number-name-semester'] = '%3$s %1$s (%4$s)';
        $template['sem-duration-name'] = '%4$s';
        if ($format === 'default' || !isset($template[$format])) {
           $format = Config::get()->IMPORTANT_SEMNUMBER ? 'type-number-name' : 'type-name';
        }
        $sem_type = $this->getSemType();
        $data[0] = $this->name;
        $data[1] = $sem_type['name'];
        $data[2] = $this->veranstaltungsnummer;
        $data[3] = $this->start_semester->name;
        if ($this->start_semester !== $this->end_semester && !$this->isStudygroup()) {
            $data[3] .= ' - ' .  ($this->end_semester ? $this->end_semester->name : _('unbegrenzt'));
        }
        return trim(vsprintf($template[$format], array_map('trim', $data)));
    }


    /**
     * Retrieves the course dates including cancelled dates ("ex-dates").
     * The dates can be filtered by an optional time range. By default,
     * all dates are retrieved.
     *
     * @param $range_begin The begin timestamp of the time range.
     *
     * @param $range_end The end timestamp of the time range.
     *
     * @returns SimpleCollection A collection of all retrieved dates and
     *     cancelled dates.
     */
    public function getDatesWithExdates($range_begin = 0, $range_end = 0)
    {
        $dates = [];
        if (($range_begin > 0) && ($range_end > 0) && ($range_end > $range_begin)) {
            $ex_dates = $this->ex_dates->findBy('content', '', '<>')
                          ->findBy('date', $range_begin, '>=')
                          ->findBy('end_time', $range_end, '<=');
            $dates = $this->dates->findBy('date', $range_begin, '>=')
                          ->findBy('end_time', $range_end, '<=');
            $dates->merge($ex_dates);
        } else {
            $dates = $this->ex_dates->findBy('content', '', '<>');
            $dates->merge($this->dates);
        }
        $dates->uasort(function($a, $b) {
            return $a->date - $b->date
                ?: strnatcasecmp($a->getRoomName(), $b->getRoomName());
        });
        return $dates;
    }

    /**
     * Sets this courses study areas to the given values.
     *
     * @param $ids the new study areas
     * @return bool Changes successfully saved?
     */
    public function setStudyAreas($ids)
    {
        $old = $this->study_areas->pluck('sem_tree_id');
        $added = array_diff($ids, $old);
        $removed = array_diff($old, $ids);
        $success = false;
        if ($added || $removed) {

            $this->study_areas = SimpleCollection::createFromArray(StudipStudyArea::findMany($ids));

            if ($this->store()) {
                NotificationCenter::postNotification('CourseDidChangeStudyArea', $this);
                $success = true;

                foreach ($added as $one) {
                    StudipLog::log('SEM_ADD_STUDYAREA', $this->id, $one);

                    $area = $this->study_areas->find($one);
                    if ($area->isModule()) {
                        NotificationCenter::postNotification(
                            'CourseAddedToModule',
                            $area,
                            ['module_id' => $one, 'course_id' => $this->id]
                        );
                    }
                }

                foreach ($removed as $one) {
                    StudipLog::log('SEM_DELETE_STUDYAREA', $this->id, $one);

                    $area = StudipStudyArea::find($one);
                    if ($area->isModule()) {
                        NotificationCenter::postNotification(
                            'CourseRemovedFromModule',
                            $area,
                            ['module_id' => $one, 'course_id' => $this->id]
                        );
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Is the current course visible for the current user?
     * @param string $user_id
     * @return bool Visible?
     */
    public function isVisibleForUser($user_id = null)
    {
        return $this->visible
            || $GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM, $user_id)
            || $GLOBALS['perm']->have_studip_perm('user', $this->id, $user_id);
    }

    /**
     * Returns a descriptive text for the range type.
     *
     * @return string
     */
    public function describeRange()
    {
        return _('Veranstaltung');
    }

    /**
     * Returns a unique identificator for the range type.
     *
     * @return string
     */
    public function getRangeType()
    {
        return 'course';
    }

    /**
     * Returns the id of the current range
     *
     * @return string
     */
    public function getRangeId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return CourseConfig::get($this);
    }

    /**
     * Decides whether the user may access the range.
     *
     * @param string|null $user_id Optional id of a user, defaults to current user
     * @return bool
     * @todo Check permissions
     */
    public function isAccessibleToUser($user_id = null)
    {
        if ($user_id === null) {
            $user_id = $GLOBALS['user']->id;
        }
        return $GLOBALS['perm']->have_studip_perm('user', $this->id, $user_id);
    }

    /**
     * Decides whether the user may edit/alter the range.
     *
     * @param string|null $user_id Optional id of a user, defaults to current user
     * @return bool
     * @todo Check permissions
     */
    public function isEditableByUser($user_id = null)
    {
        if ($user_id === null) {
            $user_id = $GLOBALS['user']->id;
        }
        return $GLOBALS['perm']->have_studip_perm('tutor', $this->id, $user_id);
    }

    /**
     * Returns the appropriate icon for the completion status.
     *
     * Mapping (completion -> icon role):
     * - 0 => status-red
     * - 1 => status-yellow
     * - 2 => status-green
     *
     * @return Icon class
     */
    public function getCompletionIcon()
    {
        $role = Icon::ROLE_STATUS_RED;
        if ($this->completion == 1) {
            $role = Icon::ROLE_STATUS_YELLOW;
        } elseif ($this->completion == 2) {
            $role = Icon::ROLE_STATUS_GREEN;
        }
        return Icon::create('radiobutton-checked', $role);
    }

    /**
     * Generates a general log entry if the course were changed.
     * Furthermore, this method emits notifications when the
     * start and/or the end semester has/have changed.
     */
    protected function logStore()
    {
        if ($this->isFieldDirty('start_semester')) {
            //Log change of start semester:
            StudipLog::log('SEM_SET_STARTSEMESTER', $this->id, $this->start_semester->beginn);
            NotificationCenter::postNotification('CourseDidChangeSchedule', $this);
        }

        $log = [];
        if ($this->isFieldDirty('admission_prelim')) {
            $log[] = $this->admission_prelim ?  _('Neuer Anmeldemodus: Vorläufiger Eintrag') : _('Neuer Anmeldemodus: Direkter Eintrag');
        }

        if ($this->isFieldDirty('admission_binding')) {
            $log[] = $this->admission_binding? _('Anmeldung verbindlich') : _('Anmeldung unverbindlich');
        }

        if ($this->isFieldDirty('admission_turnout')) {
            $log[] = sprintf(_('Neue Teilnehmerzahl: %s'), (int)$this->admission_turnout);
        }

        if ($this->isFieldDirty('admission_disable_waitlist')) {
            $log[] = $this->admission_disable_waitlist ? _('Warteliste aktiviert') : _('Warteliste deaktiviert');
        }

        if ($this->isFieldDirty('admission_waitlist_max')) {
            $log[] = sprintf(_('Plätze auf der Warteliste geändert: %u'), (int)$this->admission_waitlist_max);
        }

        if ($this->isFieldDirty('admission_disable_waitlist_move')) {
            $log[] = $this->admission_disable_waitlist ? _('Nachrücken aktiviert') : _('Nachrücken deaktiviert');
        }

        if ($this->isFieldDirty('admission_prelim_txt')) {
            if ($this->admission_prelim_txt) {
                $log[] = sprintf(_('Neuer Hinweistext bei vorläufigen Eintragungen: %s'), strip_tags(kill_format($this->admission_prelim_txt)));
            } else {
                $log[] = _('Hinweistext bei vorläufigen Eintragungen wurde entfert');
            }
        }

        if (!empty($log)) {
            StudipLog::log(
                'SEM_CHANGED_ACCESS',
                $this->id,
                null,
                '',
                implode(' - ', $log)
            );
        }

        if ($this->isFieldDirty('visible')) {
            StudipLog::log($this->visible ? 'SEM_VISIBLE' : 'SEM_INVISIBLE', $this->id);
        }
    }


    //StudipItem interface implementation:

    public function getItemName($long_format = true)
    {
        if ($long_format) {
            return $this->getFullName();
        } else {
            return $this->name;
        }
    }

    public function getItemURL()
    {
        return URLHelper::getURL(
            'dispatch.php/course/details/index',
            [
                'cid' => $this->id
            ]
        );
    }

    public function getItemAvatarURL()
    {
        $avatar = CourseAvatar::getAvatar($this->id);
        if ($avatar) {
            return $avatar->getURL(Avatar::NORMAL);
        }
        return '';
    }


    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findThru($storage->user_id, [
            'thru_table'        => 'seminar_user',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'Seminar_id',
            'assoc_foreign_key' => 'Seminar_id',
        ]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Seminare'), 'seminare', $field_data);
            }
        }
    }
    public function getRangeName()
    {
        return $this->name;
    }

    public function getRangeIcon($role)
    {
        return Icon::create('seminar', $role);
    }

    public function getRangeUrl()
    {
        return 'course/overview';
    }

    public function getRangeCourseId()
    {
        return $this->Seminar_id;
    }

    public function isRangeAccessible(string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        return $GLOBALS['perm']->have_studip_perm('autor', $this->Seminar_id, $user_id);
    }


    public function getLink() : StudipLink
    {
        return new StudipLink($this->getItemURL(), $this->name, Icon::create('seminar'));
    }


    /**
     * Returns a list of courses for the specified user.
     * Permission levels may be supplied to limit the course list.
     *
     * @param string $user_id The ID of the user whose courses shall be retrieved.
     *
     * @param string[] $perms The permission levels of the user that shall be
     *     regarded when retrieving courses.
     *
     * @param bool $with_deputies Whether to include courses where the user is
     *     a deputy (true) or not (false). Defaults to true.
     *
     * @returns Course[] A list of courses.
     */
    public static function findByUser($user_id, $perms = [], $with_deputies = true)
    {
        if (!$user_id) {
            return [];
        }

        $db = DBManager::get();
        $sql = "SELECT `seminar_id`
                FROM `seminar_user`
                WHERE `user_id` = :user_id";
        $sql_params = ['user_id' => $user_id];
        if (is_array($perms) && count($perms)) {
            $sql .= ' AND `status` IN (:perms)';
            $sql_params['perms'] = $perms;
        }
        $seminar_ids = $db->fetchFirst($sql, $sql_params);
        if (Config::get()->DEPUTIES_ENABLE && $with_deputies) {
            $sql = 'SELECT range_id FROM `deputies` WHERE `deputies`.`user_id` = :user_id';
            $seminar_ids = array_merge($seminar_ids, $db->fetchFirst($sql, $sql_params));
        }
        return Course::findBySQL(
            "LEFT JOIN semester_courses ON (semester_courses.course_id = seminare.Seminar_id)
             WHERE Seminar_id IN (?)
             ORDER BY IF(semester_courses.semester_id IS NULL, 1, 0) DESC, start_time DESC, Name ASC",
            [$seminar_ids]
        );
    }

    /**
     * Returns whether this course is a studygroup
     * @return bool
     */
    public function isStudygroup()
    {
        return in_array($this->status, studygroup_sem_types());
    }
}
