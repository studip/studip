<?php

/**
 * SeparableRoom.class.php - model class for a separable room
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 *
 * @property string id database column
 * @property string building_id database column
 * @property string name database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection building belongs_to Building
 * @property SimpleORMapCollection parts has_many SeparableRoomPart
 */

class SeparableRoom extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'separable_rooms';
        
        $config['belongs_to']['building'] = [
            'class_name'  => 'Building',
            'foreign_key' => 'building_id',
            'assoc_func'  => 'find'
        ];
        $config['has_many']['parts']      = [
            'class_name'        => 'SeparableRoomPart',
            'assoc_foreign_key' => 'separable_room_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete'
        ];
        
        parent::configure($config);
    }
    
    
    /**
     *  Creates a SeparableRoom object from a list of room objects.
     * @param string $building_id
     * @param array $rooms
     * @param string $name
     * @return SeparableRoom|null
     */
    public static function createFromRooms(string $building_id, array $rooms = [], $name = '')
    {
        if (count($rooms) === 0) {
            return null;
        }
        
        //Check if all array items are room objects:
        $room_names = [];
        foreach ($rooms as $room) {
            if (!($room instanceof Room)) {
                throw new InvalidArgumentException(
                    _('Teilbare Räume dürfen nur aus Raum-Objekten bestehen!')
                );
            }
            $room_names[] = $room->name;
        }
        
        //Now we can create the separable room:
        if (!$name) {
            //First we create a common name from all the room names.
            $common_name = '';
            if (count($room_names) > 1) {
                $name_position     = 0;
                $current_character = '';
                //Loop over all rooms until $common_name doesn't grow anymore
                while ($current_character !== null) {
                    $current_character = $room_names[0][$name_position];
                    foreach ($room_names as $room_name) {
                        if ($room_name[$name_position] != $current_character) {
                            //At this point the name is diverting.
                            break 2;
                        }
                    }
                    //The current character is the same for all room names.
                    $common_name .= $current_character;
                    $name_position++;
                }
            } else {
                $common_name = $room_names[0];
            }
            $name = $common_name;
        }
        
        //At this point we have either found the common name
        //or $common_name is empty.
        
        $separable_room              = new SeparableRoom();
        $separable_room->building_id = $building_id;
        if ($name) {
            $separable_room->name = $name;
        } else {
            $separable_room->name = _('Teilbarer Raum');
        }
        if (!$separable_room->store()) {
            throw new SeparableRoomException(
                _('Fehler beim Speichern des teilbaren Raumes!')
            );
        }
        
        $successfully_stored = 0;
        foreach ($rooms as $room) {
            $part                    = new SeparableRoomPart();
            $part->separable_room_id = $separable_room->id;
            $part->room_id           = $room->id;
            if ($part->store()) {
                $successfully_stored++;
            }
        }
        
        if ($successfully_stored < count($rooms)) {
            throw new SeparableRoomException(
                sprintf(
                    _('Nur %1$d von %2$d Räumen konnten dem teilbaren Raum zugeordnet werden!'),
                    $successfully_stored,
                    count($rooms)
                )
            );
        }
        
        return $separable_room;
    }
    
    
    public static function findByRoomPart(Room $room)
    {
        return self::findOneBySql(
            'INNER JOIN separable_room_parts
            ON separable_room_parts.separable_room_id = separable_rooms.id
            WHERE
            separable_room_parts.room_id = :room_id',
            [
                'room_id' => $room->id
            ]
        );
    }
    
    
    public function findOtherRoomParts(array $known_parts = [])
    {
        $known_part_ids = [];
        foreach ($known_parts as $known_part) {
            if ($known_part instanceof Room) {
                $known_part_ids[] = $known_part->id;
            }
        }
        
        $resources = Resource::findBySql(
            'INNER JOIN separable_room_parts
            ON resources.id = separable_room_parts.room_id
            WHERE
            room_id NOT IN ( :known_part_ids )
            AND
            separable_room_id = :separable_room_id',
            [
                'known_part_ids'    => $known_part_ids,
                'separable_room_id' => $this->id
            ]
        );
        
        if (!$resources) {
            //There are no other room parts.
            return [];
        }
        
        $room_parts = [];
        foreach ($resources as $resource) {
            $resource = $resource->getDerivedClassInstance();
            if ($resource instanceof Room) {
                $room_parts[] = $resource;
            }
        }
        
        return $room_parts;
    }
}
