<?php
/**
 * EventData.class.php - Model class for calendar events.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.2
 */

class EventData extends SimpleORMap implements PrivacyObject
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'event_data';

        $config['belongs_to']['author'] = [
            'class_name'  => User::class,
            'foreign_key' => 'author_id',
        ];
        $config['belongs_to']['editor'] = [
            'class_name'  => User::class,
            'foreign_key' => 'editor_id',
        ];
        $config['has_many']['calendars'] = [
            'class_name'  => CalendarEvent::class,
            'foreign_key' => 'event_id'
        ];

        $config['default_values']['linterval'] = 0;
        $config['default_values']['sinterval'] = 0;

        $config['registered_callbacks']['before_create'][] = 'cbDefaultValues';

        parent::configure($config);

    }

    public function delete()
    {
        // do not delete until one calendar is left
        if (sizeof($this->calendars) > 1) {
            return false;
        }
        $calendars = $this->calendars;
        $ret = parent::delete();
        // only one calendar is left
        if ($ret) {
            $calendars->each(function($c) { $c->delete(); });
        }
        return $ret;
    }

    public static function garbageCollect()
    {
        DBManager::get()->query('DELETE event_data '
                . 'FROM calendar_event LEFT JOIN event_data USING(event_id)'
                . 'WHERE range_id IS NULL');
    }

    public function getDefaultValue($field)
    {
        if ($field == 'start') {
            return time();
        }
        if ($field == 'end' && $this->content['start']) {
            return $this->content['start'] + 3600;
        }
        if ($field == 'ts' && $this->content['start']) {
            return mktime(12, 0, 0, date('n', $this->content['start']),
                date('j', $this->content['start']), date('Y', $this->content['start']));
        }
        return parent::getDefaultValue($field);
    }

    protected function cbDefaultValues()
    {
        if (empty($this->content['uid'])) {
            $this->content['uid'] = 'Stud.IP-' . $this->event_id . '@' . $_SERVER['SERVER_NAME'];
        }
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = EventData::findThru($storage->user_id, [
            'thru_table'        => 'calendar_event',
            'thru_key'          => 'range_id',
            'thru_assoc_key'    => 'event_id',
            'assoc_foreign_key' => 'event_id',
        ]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Kalender Einträge'), 'event_data', $field_data);
            }
        }
    }
}
