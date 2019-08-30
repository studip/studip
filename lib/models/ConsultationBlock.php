<?php
/**
 * Representation of a block of consultation slots - defining metadata.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 * @property string block_id database column
 * @property string id alias column for block_id
 * @property string teacher_id database column
 * @property string start database column
 * @property string end database column
 * @property string room database column
 * @property string calendar_events database column
 * @property string note database column
 * @property string size database column
 * @property string course_id database column
 * @property bool has_bookings computed column
 * @property SimpleORMapCollection slots has_many ConsultationSlot
 * @property User teacher belongs_to User
 * @property Course course belongs_to Course
 */
class ConsultationBlock extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures the model.
     * @param array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_blocks';

        $config['belongs_to']['teacher'] = [
            'class_name'  => User::class,
            'foreign_key' => 'teacher_id',
        ];
        $config['has_many']['slots'] = [
            'class_name'        => ConsultationSlot::class,
            'assoc_foreign_key' => 'block_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'course_id',
        ];

        $config['additional_fields']['has_bookings']['get'] = function ($block) {
            return ConsultationBooking::countBySql(
                "JOIN consultation_slots USING(slot_id) WHERE block_id = ?",
                [$block->id]
            ) > 0;
        };
        $config['additional_fields']['is_expired']['get'] = function ($block) {
            $expired_slots = 0;
            foreach ($block->slots as $slot) {
                if ($slot->is_expired) {
                    $expired_slots += 1;
                }
            }

            return count($block->slots) === $expired_slots;
        };

        parent::configure($config);
    }

    /**
     * Find only visible blocks for user.
     *
     * @param string      $user_id    User id to check the visibility for
     * @param string      $teacher_id User id of the teacher
     * @param string|null $course_id  Optional course id to filter blocks by
     * @param string      $order      Optional order clause
     * @return array
     */
    public static function findVisibleForUserByTeacherId($user_id, $teacher_id, $course_id = null, $order = '')
    {
        list($condition, $parameters) = self::prepareConditionAndParametersForVisibleBlocks(
            $user_id,
            $teacher_id,
            $course_id
        );
        return self::findBySQL("{$condition} {$order}", $parameters);
    }

    /**
     * Count only visible blocks for user.
     *
     * @param string      $user_id    User id to check the visibility for
     * @param string      $teacher_id User id of the teacher
     * @param string|null $course_id  Optional course id to filter blocks by
     * @return int
     */
    public static function countVisibleForUserByTeacherId($user_id, $teacher_id, $course_id = null)
    {
        list($condition, $parameters) = self::prepareConditionAndParametersForVisibleBlocks(
            $user_id,
            $teacher_id,
            $course_id
        );
        return self::countBySQL($condition, $parameters);
    }

    /**
     * Returns all visible courses to the user that the teacher has created
     * blocks for.
     *
     * @param  string $user_id    User id to find the courses for
     * @param  string $teacher_id User id of the teacher
     * @return array
     */
    public static function findVisibleCoursesForUserByTeacherId($user_id, $teacher_id)
    {
        $condition = "JOIN consultation_blocks ON seminar_id = course_id
                      JOIN seminar_user USING (seminar_id)
                      WHERE course_id IS NOT NULL
                        AND teacher_id = :teacher_id
                        AND user_id = :user_id";

        $order = Config::get()->IMPORTANT_SEMNUMBER
               ? 'ORDER BY VeranstaltungsNummer, Name'
               : 'ORDER BY Name';

        return Course::findBySQL("{$condition} {$order}", [
            ':user_id'    => $user_id,
            ':teacher_id' => $teacher_id,
        ]);
    }

    private static function prepareConditionAndParametersForVisibleBlocks($user_id, $teacher_id, $course_id)
    {
        $conditions = [
            'course_id IS NULL OR seminar_user.user_id IS NOT NULL',
            'teacher_id = :teacher_id',
            'end > UNIX_TIMESTAMP()'
        ];
        $parameters = [
            ':user_id'    => $user_id,
            ':teacher_id' => $teacher_id,
        ];

        if ($course_id !== null) {
            $conditions[] = 'course_id = :course_id';
            $parameters[':course_id'] = $course_id;
        }

        $condition = "LEFT JOIN seminar_user
                        ON (course_id = seminar_id AND seminar_user.user_id = :user_id)
                      WHERE (" . implode(') AND (', $conditions) . ")
                      ORDER BY start ASC";

        return [$condition, $parameters];
    }

    /**
     * Returns whether any blocks of a teacher exist that should be visible for
     * the given user.
     *
     * @param string $teacher_id User id of the teacher
     * @param string $user_id    Id of the user
     * @return bool
     */
    public static function existForTeacherAndUser($teacher_id, $user_id)
    {
        $query = "SELECT 1
                  FROM `consultation_blocks` AS cb
                  LEFT JOIN `seminare` AS s ON cb.`course_id` = s.`Seminar_id`
                  LEFT JOIN `seminar_user` AS su USING (`Seminar_id`)
                  WHERE `teacher_id` = :teacher_id
                    AND (
                        cb.`course_id` IS NULL
                        OR su.`user_id` = :user_id
                   )";
        return (bool) DBManager::get()->fetchColumn($query, [
            ':teacher_id' => $teacher_id,
            ':user_id'    => $user_id,
        ]);
    }

    /**
     * Generate blocks according to the given data.
     *
     * Be aware, that this is an actual generator that yields the results. You
     * cannot count the generated blocks without iterating over them.
     *
     * @throws OverlapException
     * @param  string $user_id  Id of the user
     * @param  int    $start    Start of the time range as unix timestamp
     * @param  int    $end      End of the time range as unix timestamp
     * @param  int    $week_day Day of the week the blocks should be created
     *                          (0 = sunday, 1 = monday ...)
     * @param  int    $interval Week interval (skip $interval weeks between
     *                          blocks)
     */
    public static function generateBlocks($user_id, $start, $end, $week_day, $interval)
    {
        $start_time = date('H:i', $start);
        $end_time   = date('H:i', $end);

        // Adjust current date to match week of day
        $current = $start;
        while (date('w', $current) != $week_day) {
            $current = strtotime('+1 day', $current);
        }

        while ($current <= $end) {
            $temp    = holiday($current);
            $holiday = is_array($temp) && $temp['col'] === 3;

            if (!$holiday) {
                if ($overlaps = self::checkOverlaps($user_id, $start, $end)) {
                    $details = [];
                    foreach ($overlaps as $overlap) {
                        $details[] = sprintf(
                            _('%s bis %s von %s bis %s Uhr'),
                            strftime('%x', $overlap->start),
                            strftime('%x', $overlap->end),
                            date('H:i', $overlap->start),
                            date('H:i', $overlap->end)
                        );
                    }

                    throw new OverlapException(
                        _('Die Zeiten überschneiden sich mit anderen bereits von Ihnen definierten Sprechstunden'),
                        $details
                    );
                }

                $block = new self();
                $block->teacher_id = $user_id;
                $block->start      = strtotime("today {$start_time}", $current);
                $block->end        = strtotime("today {$end_time}", $current);

                yield $block;
            }

            $current = strtotime("+{$interval} weeks", $current);
        }
    }

    /**
     * Checks if there any consultation slots already exist in the given
     * time range for the given user.
     *
     * @param  string $user_id Id of the user
     * @param  int    $start   Start of the time range as unix timestamp
     * @param  int    $end     End of the time range as unix timestamp
     * @return array of overlapping consultation slots
     */
    protected static function checkOverlaps($user_id, $start, $end)
    {
        $query = "SELECT DISTINCT `block_id`
                  FROM `consultation_slots`
                  JOIN `consultation_blocks` USING (`block_id`)
                  WHERE `teacher_id` = :teacher_id
                    AND `start_time` <= :start
                    AND `end_time` >= :end";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':teacher_id', $user_id);
        $statement->bindValue(':start', $start);
        $statement->bindValue(':end', $end);
        $statement->execute();
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return self::findMany($ids);
    }

    /**
     * Creates individual slots according to the defined data and given
     * duration.
     *
     * @param  int $duration Duration of a slot in minutes
     */
    public function createSlots($duration)
    {
        $start = $this->start;
        while ($start < $this->end) {
            $slot = new ConsultationSlot();
            $slot->block_id   = $this->id;
            $slot->start_time = $start;
            $slot->end_time   = strtotime("+{$duration} minutes", $start);

            $this->slots[] = $slot;

            $start = $slot->end_time;
        }
    }

    /**
     * Returns whether this slot is visible for a user.
     *
     * @param  mixed $user_id Id of the user (optional, defaults to current user)
     * @return boolean defining whether the slot is visible
     */
    public function isVisibleForUser($user_id = null)
    {
        if ($user_id === null) {
            $user_id = $GLOBALS['user']->id;
        }

        return $this->teacher_id === $user_id
            || !$this->course_id
            || (bool) $this->course->members->findOneBy('user_id', $user_id);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $blocks = self::findByTeacher_id($storage->user_id);
        if ($blocks) {
            $storage->addTabularData(
                _('Sprechstundenblöcke'),
                'consultation_blocks',
                array_map(function ($block) {
                    return $block->toRawArray();
                }, $blocks)
            );

            $slots = [];
            foreach ($blocks as $block) {
                foreach ($block->slots as $slot) {
                    $slots[] = $slot->toRawArray();
                }
            }

            if ($slots) {
                $storage->addTabularData(_('Sprechstunden'), 'consultation_slots', $slots);
            }
        }
    }

    /**
     * Finds all blocks of a teacher. Specialized version of the sorm method
     * that excludes expired blocks by default and may be used to explicitely
     * select expired blocks.
     *
     * @param  string  $teacher_id Id of the teacher
     * @param  string  $order      Optional order
     * @param  boolean $expired    Select expired blocks
     * @return array
     */
    public static function findByTeacher_id($teacher_id, $order = '', $expired = false)
    {
        if ($expired) {
            return parent::findBySQL(
                "teacher_id = ? AND end <= UNIX_TIMESTAMP() {$order}",
                [$teacher_id]
            );
        }

        return parent::findBySQL(
            "teacher_id = ? AND end > UNIX_TIMESTAMP() {$order}",
            [$teacher_id]
        );
    }

    /**
     * Count all blocks of a teacher. Specialized version of the sorm method
     * that excludes expired blocks by default and may be used to explicitely
     * select expired blocks.
     *
     * @param  string  $teacher_id Id of the teacher
     * @param  boolean $expired    Select expired blocks
     * @return array
     */
    public static function countByTeacher_id($teacher_id, $expired = false)
    {
        if ($expired) {
            return parent::countBySQL(
                "teacher_id = ? AND end <= UNIX_TIMESTAMP()",
                [$teacher_id]
            );
        }

        return parent::countBySQL(
            "teacher_id = ? AND end > UNIX_TIMESTAMP()",
            [$teacher_id]
        );
    }
}
