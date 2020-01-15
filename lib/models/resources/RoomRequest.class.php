<?php

/**
 * RoomRequest.class.php - model class for table resource_requests
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @param Room room Link to the Room resource (same as the resource attribute).
 * @param string seats additional field (from ResourceRequestProperty)
 * @param string booking_plan_is_public additional field
 *     (from ResourceRequestProperty)
 *
 * All other properties are inherited from the parent class (ResourceRequest).
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Noack <noack@data-quest.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 *
 */
class RoomRequest extends ResourceRequest
{
    protected static function configure($config = [])
    {
        $config['belongs_to']['room'] = [
            'class_name'  => 'Room',
            'foreign_key' => 'resource_id',
            'assoc_func'  => 'find'
        ];
        
        $required_properties = [
            'seats',
            'room_type',
            'booking_plan_is_public'
        ];
        
        $config['additional_fields'] = [];
        foreach ($required_properties as $property) {
            $config['additional_fields'][$property] = [
                'get' => 'getProperty',
                'set' => 'setProperty'
            ];
        }
        
        parent::configure($config);
        
    }
    
    public function checkOpen($also_change = false)
    {
        $db              = DBManager::get();
        $existing_assign = false;
        //a request for a date is easy...
        if ($this->termin_id) {
            $query           = sprintf("SELECT id FROM resource_bookings WHERE range_id = %s ", $db->quote($this->termin_id));
            $existing_assign = $db->query($query)->fetchColumn();
            //metadate request
        } elseif ($this->metadate_id) {
            $query = sprintf("SELECT count(termin_id)=count(resource_bookings.id) FROM termine LEFT JOIN resource_bookings ON(termin_id=resource_bookings.range_id)
                    WHERE metadate_id=%s", $db->quote($this->metadate_id));
            //seminar request
        } else {
            $query = sprintf("SELECT count(termin_id)=count(resource_bookings.id) FROM termine LEFT JOIN resource_bookings ON(termin_id=resource_bookings.range_id)
                    WHERE range_id='%s' AND date_typ IN" . getPresenceTypeClause(), $this->course_id);
        }
        if ($query) {
            $existing_assign = $db->query($query)->fetchColumn();
        }
        
        if ($existing_assign && $also_change) {
            $this->closed = 1;
            $this->store();
        }
        return (bool)$existing_assign;
    }
}
