<?php
class FixMissingConsultationEvents extends Migration
{
    public function description()
    {
        return 'Adds missing consultation events for teachers (BIEST #9785).';
    }

    public function up()
    {
        $query = "SELECT slot_id
                  FROM consultation_bookings
                  JOIN consultation_slots USING (slot_id)
                  JOIN consultation_blocks USING (block_id)
                  JOIN auth_user_md5 ON teacher_id = auth_user_md5.user_id
                  WHERE teacher_event_id IS NULL";
        $ids = DBManager::get()->fetchFirst($query);

        LegacyConsultationSlot::findAndMapMany(
            function ($slot) {
                // This is wrapped in a try/catch block since we can only assure
                // that the LegacyConsultationSlot is used for updating the event itself.
                // In the subsequent procedure, the related bookings are stored as well
                // which will trigger another update of the event - this time on the
                // ConsultationSlot object itself, not on the legacy one. Since this
                // has code changes for Stud.IP 5.0 this will fail but we can neglect
                // that since the event is already updated.
                try {
                    $slot->updateEvent();
                } catch (Exception $e) {
                }
            },
            $ids
        );
    }
}

class LegacyConsultationSlot extends ConsultationSlot
{
    /**
     * Updates the teacher event that belongs to the slot. This will either be
     * set to be unoccupied, occupied by only one user or by a group of user.
     */
    public function updateEvent()
    {
        if (count($this->bookings) === 0 && !$this->block->calendar_events) {
            return $this->removeEvent();
        }

        $teacher = User::find($this->block->teacher_id);
        if (!$teacher) {
            return;
        }

        $event = $this->event;
        if (!$event) {
            $event = $this->createEvent($teacher);

            $this->teacher_event_id = $event->id;
            $this->store();
        }

        setTempLanguage($teacher->id);

        if (count($this->bookings) > 0) {
            $event->category_intern = 1;

            if (count($this->bookings) === 1) {
                $booking = $this->bookings->first();

                $event->summary = sprintf(
                    _('Sprechstundentermin mit %s'),
                    $booking->user->getFullName()
                );
                $event->description = $booking->reason;
            } else {
                $event->summary = sprintf(
                    _('Sprechstundentermin mit %u Personen'),
                    count($this->bookings)
                );
                $event->description = implode("\n\n----\n\n", $this->bookings->map(function ($booking) {
                    return "- {$booking->user->getFullName()}:\n{$booking->reason}";
                }));
            }
        } else {
            $event->category_intern = 9;
            $event->summary         = _('Freier Sprechstundentermin');
            $event->description     = _('Dieser Sprechstundentermin ist noch nicht belegt.');
        }

        restoreLanguage();

        $event->store();
    }
}
