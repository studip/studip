<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string termin_id database column
 * @property string id alias column for termin_id
 * @property string range_id database column
 * @property string autor_id database column
 * @property string content database column
 * @property string description database column
 * @property string date database column
 * @property string end_time database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string date_typ database column
 * @property string topic_id database column
 * @property string raum database column
 * @property string metadate_id database column
 * @property string resource_id database column
 * @property string topics computed column
 * @property string statusgruppen computed column
 * @property string dozenten computed column
 * @property User author belongs_to User
 * @property Course course belongs_to Course
 * @property SeminarCycleDate cycle belongs_to SeminarCycleDate
 */

class CourseExDate extends SimpleORMap implements PrivacyObject
{
    const FORMAT_DEFAULT = 'default';
    const FORMAT_VERBOSE = 'verbose';

    /**
     * Returns course dates by course id
     *
     * @param String $seminar_id Id of the course
     * @return array with the associated dates
     */
    public static function findBySeminar_id($seminar_id)
    {
        return self::findByRange_id($seminar_id);
    }

    /**
     * Return course dates by range id (which is in many cases the course id)
     *
     * @param String $seminar_id Id of the course
     * @param String $order_by   Optional order definition
     * @return array with the associated dates
     */
    public static function findByRange_id($seminar_id, $order_by = 'ORDER BY date')
    {
        return parent::findByRange_id($seminar_id, $order_by);
    }

    /**
     * Configures this model.
     *
     * @param Array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'ex_termine';
        $config['belongs_to']['author'] = [
            'class_name'  => 'User',
            'foreign_key' => 'autor_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'range_id'
        ];
        $config['belongs_to']['cycle'] = [
            'class_name'  => 'SeminarCycleDate',
            'foreign_key' => 'metadate_id'
        ];

        $dummy_relation = function () { return new SimpleCollection(); };
        $dummy_null = function () { return null; };
        $config['additional_fields']['topics']['get'] = $dummy_relation;
        $config['additional_fields']['statusgruppen']['get'] = $dummy_relation;
        $config['additional_fields']['dozenten']['get'] = $dummy_relation;
        $config['additional_fields']['room_assignment']['get'] = $dummy_null;
        $config['additional_fields']['room_request']['get'] = $dummy_null;
        $config['default_values']['date_typ'] = 1;
        parent::configure($config);
    }

    /**
     * Returns the name of the assigned room for this date.
     *
     * @return String that is always empty
     */
    public function getRoomName()
    {
        return '';
    }

    /**
     * Returns the assigned room for this date as an object.
     *
     * @return null. always. canceled dates need no room.
     */
    public function getRoom()
    {
        return null;
    }

    /**
     * Returns the name of the type of this date.
     *
     * @param String containing the type name
     */
    public function getTypeName()
    {
        return $GLOBALS['TERMIN_TYP'][$this->date_typ]['name'];
    }

    /**
     * Returns the full qualified name of this date.
     *
     * @param String $format Optional format type (only 'default' and
     *                       'verbose' are supported by now)
     * @return String containing the full name of this date.
     */
    public function getFullname($format = 'default')
    {
        if (!$this->date || !in_array($format, ['default', 'verbose'])) {
            return '';
        }

        $latter_template = $format === 'verbose'
                         ? _('%R Uhr')
                         : '%R';

        if (($this->end_time - $this->date) / 60 / 60 > 23) {
            return strftime('%a., %x' . ' (' . _('ganztägig') . ')' , $this->date) . " (" . _("fällt aus") . ")";
        }

        return strftime('%a., %x, %R', $this->date) . ' - '
             . strftime($latter_template, $this->end_time)
             . ' (' . _('fällt aus') . ')';
    }

    /**
     * Returns the full qualified name of this date
     * raumzeit_send_cancel_message needs the toString()-Method in this class
     *
     * @deprecated since version 3.4
     * @return String containing the full name of this date
     */#
    public function toString()
    {
        return $this->getFullname();
    }

    /**
     * Converts a CourseExDate Entry to a CourseDate Entry
     * returns instance of the new CourseDate or NULL
     * @return Object CourseDate
     */
    public function unCancelDate()
    {
        //NOTE: If you modify this method make sure the changes
        //are also inserted in SingleDateDB::storeSingleDate
        //and CourseDate::cancelDate to keep the behavior consistent
        //across Stud.IP!

        //These statements are used below to update the relations
        //of this ex-date.
        $db = DBManager::get();

        $groups_stmt = $db->prepare(
            "UPDATE termin_related_groups
            SET termin_id = :termin_id
            WHERE termin_id = :ex_termin_id;"
        );

        $persons_stmt = $db->prepare(
            "UPDATE termin_related_persons
            SET range_id = :termin_id
            WHERE range_id = :ex_termin_id;"
        );

        $ex_date = $this->toArray();

        //REMOVE content
        unset($ex_date['content']);

        $date = new CourseDate();
        $date->setData($ex_date);
        $date->setId($date->getNewId());

        if ($date->store()) {
            //Update the relations to the ex-date so that they
            //use the ID of the new date.

            $groups_stmt->execute(
                [
                    'termin_id' => $date->id,
                    'ex_termin_id' => $this->id
                ]
            );

            $persons_stmt->execute(
                [
                    'termin_id' => $date->id,
                    'ex_termin_id' => $this->id
                ]
            );

            //After we updated the relations so that they refer to the
            //new date we can delete this ex-date and return the date:

            StudipLog::log('SEM_UNDELETE_SINGLEDATE', $this->termin_id, $this->range_id, 'Cycle_id: ' . $this->metadate_id);
            $this->delete();
            return $date;
        }
        return null;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("autor_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('ausgefallende Termine'), 'ex_termine', $field_data);
            }
        }
    }
}
