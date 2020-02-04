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

        ConsultationSlot::findAndMapMany(
            function ($slot) {
                $slot->updateEvent();
            },
            $ids
        );
    }
}
