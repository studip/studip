<?php
/**
 * Representation of a user's booking of a consultation slots.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 * @property string booking_id database column
 * @property string id alias column for booking_id
 * @property string slot_id database column
 * @property string user_id database column
 * @property string reason database column
 * @property string student_event_id database column
 * @property ConsultationSlot slot belongs_to ConsultationSlot
 * @property User user belongs_to User
 * @property EventData event has_one EventData
 */
class ConsultationBooking extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures the model.
     * @param array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_bookings';

        $config['belongs_to']['slot'] = [
            'class_name'  => ConsultationSlot::class,
            'foreign_key' => 'slot_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];
        $config['has_one']['event'] = [
            'class_name'        => EventData::class,
            'foreign_key'       => 'student_event_id',
            'assoc_foreign_key' => 'event_id',
            'on_delete'         => 'delete',
        ];

        $config['registered_callbacks']['before_create'][] = function ($booking) {
            setTempLanguage($booking->user_id);

            $event = $booking->slot->createEvent($booking->user);
            $event->category_intern = 1;
            $event->summary = sprintf(
                _('Sprechstundentermin bei %s'),
                $booking->slot->block->teacher->getFullName()
            );
            $event->description = $booking->reason;
            $event->store();

            restoreLanguage();

            $booking->student_event_id = $event->id;
        };

        $config['registered_callbacks']['after_store'][] = function ($booking) {
            if ($booking->event) {
                $booking->event->description = $booking->reason;
                $booking->event->store();
            }

            $booking->slot->updateEvent();
        };

        $config['registered_callbacks']['after_delete'][] = function ($booking) {
            $booking->slot->updateEvent();
        };

        parent::configure($config);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $bookings = self::findByUser_id($storage->user_id);
        if ($bookings) {
            $storage->addTabularData(
                _('Sprechstundenbelegungen'),
                'consultation_bookings',
                array_map(function ($booking) {
                    return $booking->toRawArray();
                }, $bookings)
            );
        }
    }
}
