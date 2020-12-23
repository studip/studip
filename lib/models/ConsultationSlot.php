<?php
/**
 * Representation of a consultation slot.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 * @property string slot_id database column
 * @property string id alias column for slot_id
 * @property string block_id database column
 * @property string start_time database column
 * @property string end_time database column
 * @property string note database column
 * @property string teacher_event_id database column
 * @property SimpleORMapCollection bookings has_many ConsultationBooking
 * @property ConsultationBlock block belongs_to ConsultationBlock
 * @property EventData event has_one EventData
 */
class ConsultationSlot extends SimpleORMap
{
    /**
     * Configures the model.
     * @param array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_slots';

        $config['belongs_to']['block'] = [
            'class_name'  => ConsultationBlock::class,
            'foreign_key' => 'block_id',
        ];
        $config['has_one']['event'] = [
            'class_name'        => EventData::class,
            'foreign_key'       => 'teacher_event_id',
            'assoc_foreign_key' => 'event_id',
            'on_delete'         => 'delete',
        ];
        $config['has_many']['bookings'] = [
            'class_name'        => ConsultationBooking::class,
            'assoc_foreign_key' => 'slot_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete',
        ];

        $config['registered_callbacks']['before_create'][] = function ($slot) {
            if ($slot->block->calendar_events && $slot->block->range_type === 'user') {
                $slot->teacher_event_id = $slot->createEvent($slot->block->range)->id;
                $slot->updateEvent();
            }
        };
        $config['registered_callbacks']['after_delete'][] = function ($slot) {
            $block = $slot->block;
            if (count($block->slots) === 0) {
                $block->delete();
            }
        };

        $config['additional_fields']['has_bookings']['get'] = function ($slot) {
            return count($slot->bookings) > 0;
        };
        $config['additional_fields']['is_expired']['get'] = function ($slot) {
            return $slot->end_time < time();
        };

        parent::configure($config);
    }

    /**
     * Counts all slots of the given range.
     *
     * @param  Range $range   Range
     * @param  bool  $expired
     * @return int
     */
    public static function countByRange(Range $range, $expired = false)
    {
        $expired_condition = $expired
                           ? "end <= UNIX_TIMESTAMP()"
                           : "end > UNIX_TIMESTAMP()";

        $condition = "JOIN `consultation_blocks` USING (`block_id`)
                      WHERE `range_id` = :range_id
                        AND `range_type` = :range_type
                        AND {$expired_condition}";
        return self::countBySQL($condition, [
            ':range_id'   => $range->getRangeId(),
            ':range_type' => $range->getRangeType(),
        ]);
    }

    /**
     * Finds slots of the given teacher.
     *
     * @param Range  $range   Range
     * @param string $order   Desired order of items
     * @param bool   $expired Show expired items?
     * @return array
     */
    public static function findByRange(Range $range, $order = '', $expired = false)
    {
        $expired_condition = $expired
                           ? "end <= UNIX_TIMESTAMP()"
                           : "end > UNIX_TIMESTAMP()";

        $condition = "JOIN consultation_blocks USING (block_id)
                      WHERE range_id = :range_id
                        AND range_type = :range_type
                        AND {$expired_condition}
                      {$order}";
        return self::findBySQL($condition, [
            ':range_id'   => $range->getRangeId(),
            ':range_type' => $range->getRangeType(),
        ]);
    }

    /**
     * Find all occupied slots for a given user and teacher combination.
     *
     * @param string $user_id Id of the user
     * @param Range  $range   Range
     * @return array
     */
    public static function findOccupiedSlotsByUserAndRange($user_id, Range $range)
    {
        $condition = "JOIN consultation_blocks USING (block_id)
                      JOIN consultation_bookings USING (slot_id)
                      WHERE user_id = :user_id
                        AND range_id = :range_id
                        AND range_type = :range_type
                        AND end > UNIX_TIMESTAMP()
                      ORDER BY start_time ASC";
        return self::findBySQL($condition, [
            ':user_id'    => $user_id,
            ':range_id'   => $range->getRangeId(),
            ':range_type' => $range->getRangeType(),
        ]);
    }

    /**
     * Returns whether this slot is occupied (by a given user).
     *
     * @param  mixed $user_id Id of the user (optional)
     * @return boolean indicating whether the slot is occupied (by the given
     *                 user)
     */
    public function isOccupied($user_id = null)
    {
        return $user_id === null
             ? count($this->bookings) >= $this->block->size
             : (bool) $this->bookings->findOneBy('user_id', $user_id);
    }

    /**
     * Creates a Stud.IP calendar event relating to the slot.
     *
     * @param  User $user User object to create the event for
     * @return EventData Created event
     */
    public function createEvent(User $user)
    {
        $event = new EventData();
        $event->uid = $this->createEventId($user);
        $event->author_id = $user->id;
        $event->editor_id = $user->id;
        $event->start     = $this->start_time;
        $event->end       = $this->end_time;
        $event->class     = 'PRIVATE';
        $event->priority  = 0;
        $event->location  = $this->block->room;
        $event->rtype     = 'SINGLE';
        $event->store();

        $calendar_event = new CalendarEvent();
        $calendar_event->range_id     = $user->id;
        $calendar_event->group_status = 0;
        $calendar_event->event_id     = $event->id;
        $calendar_event->store();

        return $event;
    }

    /**
     * Returns a unique event id.
     *
     * @param  User $user [description]
     * @return string unique event id
     */
    protected function createEventId(User $user)
    {
        $rand_id = md5(uniqid(self::class, true));
        return "Termin{$rand_id}-{$user->id}";
    }

    /**
     * Updates the teacher event that belongs to the slot. This will either be
     * set to be unoccupied, occupied by only one user or by a group of user.
     */
    public function updateEvent()
    {
        if ($this->block->range_type !== 'user') {
            return;
        }

        if (count($this->bookings) === 0 && !$this->block->calendar_events) {
            return $this->removeEvent();
        }

        $event = $this->event;
        if (!$event) {
            $event = $this->createEvent($this->block->range);

            $this->teacher_event_id = $event->id;
            $this->store();
        }

        setTempLanguage($this->block->range_id);

        if (count($this->bookings) > 0) {
            $event->category_intern = 1;

            if (count($this->bookings) === 1) {
                $booking = $this->bookings->first();

                $event->summary = sprintf(
                    _('Termin mit %s'),
                    $booking->user->getFullName()
                );
                $event->description = $booking->reason;
            } else {
                $event->summary = sprintf(
                    _('Termin mit %u Personen'),
                    count($this->bookings)
                );
                $event->description = implode("\n\n----\n\n", $this->bookings->map(function ($booking) {
                    return "- {$booking->user->getFullName()}:\n{$booking->reason}";
                }));
            }
        } else {
            $event->category_intern = 9;
            $event->summary         = _('Freier Termin');
            $event->description     = _('Dieser Termin ist noch nicht belegt.');
        }

        restoreLanguage();

        $event->store();
    }

    public function removeEvent()
    {
        if ($this->event) {
            $this->event->delete();

            $this->teacher_event_id = null;
            $this->store();
        }
    }
}
