<?php

/**
 * RoomSearch.class.php - A search type for rooms.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


class RoomSearch extends ResourceSearch
{
     //Setup and search configuration methods:


    public function __construct()
    {
        $this->accepted_permission_levels = [
            'user', 'autor', 'tutor', 'admin'
        ];
        $this->use_global_permissions = true;
        $this->with_seats = 0;
        $this->additional_display_properties = [];
        $this->additional_property_format = '[%s]';
    }


    //SearchType interface implementations:


    public function getTitle()
    {
        return _('Raumsuche');
    }

    /**
     * @param string $keyword The name of the room or a part of it.
     * @param array $contextual_data This parameter is not used at the moment.
     */
    public function getResults(
        $keyword,
        $contextual_data = [],
        $limit = PHP_INT_MAX,
        $offset = 0
    )
    {
        $user = User::findCurrent();
        if (!$user) {
            return [];
        }
        if ($this->with_seats) {
            $prop_id_seats = ResourcePropertyDefinition::findOneByName('seats')->id;
            $sql = "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            INNER JOIN resource_properties rp ON rp.resource_id = resources.id
            WHERE
            rc.class_name IN ( :room_class_names )
            AND
            rp.property_id = :prop_id_seats
            AND
            rp.state > :seats
            AND
            resources.name LIKE CONCAT('%', :keyword, '%') ";
            $sql_params = [
                'keyword' => $keyword,
                'seats'   => $this->with_seats,
                'prop_id_seats' => $prop_id_seats
            ];
        } else {
            $sql = "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE
            rc.class_name IN ( :room_class_names )
            AND
            resources.name LIKE CONCAT('%', :keyword, '%') ";
            $sql_params = [
                'keyword' => $keyword
            ];
        }
        $sql_params['room_class_names'] = RoomManager::getAllRoomClassNames();

        //Root users can access everything so that we don't need
        //to check for permissions in case the current user is root.
        if (!$GLOBALS['perm']->have_perm('root')) {
            $global_permission = null;
            if ($this->use_global_permissions) {
                //Get the global permission level:
                $global_permission = ResourceManager::getGlobalResourcePermission(
                    $user
                );
            }

            if (in_array($global_permission, $this->accepted_permission_levels)) {
                //Retrieve all resources where the user has sufficient permissions.
                //There may be resources where the user has explicitly lower
                //permissions and we must therefore exclude these resources.
                $lower_permission_levels = ResourceManager::getLowerPermissionLevels(
                    $global_permission
                );
                $sql .= "AND resources.id NOT IN (
                    SELECT resource_id
                    FROM
                    resource_permissions
                    WHERE
                    user_id = :user_id
                    AND
                    perms IN ( :lower_perms )
                    UNION
                    SELECT resource_id
                    FROM
                    resource_temporary_permissions
                    WHERE
                    user_id = :user_id
                    AND
                    perms IN ( :lower_perms )
                    AND
                    begin <= :now
                    AND
                    end >= :now
                    )";
                $sql_params = array_merge(
                    $sql_params,
                    [
                        'user_id' => $user->id,
                        'lower_perms' => $lower_permission_levels,
                        'now' => time()
                    ]
                );
            } else {
                //Only retrieve those resources where the user has direct
                //sufficient permissions.
                $sql .= "AND resources.id IN (
                    SELECT resource_id
                    FROM
                    resource_permissions
                    WHERE
                    user_id = :user_id
                    AND
                    perms IN ( :perms )
                    UNION
                    SELECT resource_id
                    FROM
                    resource_temporary_permissions
                    WHERE
                    user_id = :user_id
                    AND
                    perms IN ( :perms )
                    AND
                    begin <= :now
                    AND
                    end >= :now
                    )";
                $sql_params = array_merge(
                    $sql_params,
                    [
                    'user_id' => $user->id,
                    'perms' => $this->accepted_permission_levels,
                    'now' => time()
                    ]
                );
            }
        }

        $sql .= " ORDER BY resources.name ASC
                LIMIT :limit OFFSET :offset ";
        $sql_params['limit'] = $limit;
        $sql_params['offset'] = $offset;

        $rooms = Room::findBySql(
            $sql,
            $sql_params
        );

        $results = [];
        foreach ($rooms as $room) {
            $additional_text = '';
            if (count($this->additional_display_properties)) {
                $additional_values = [];
                foreach ($this->additional_display_properties as $ap) {
                    $additional_values[] = $room->getProperty($ap);
                }
                $additional_text = implode(', ', $additional_values);
            }
            $name = $room->name;
            if($additional_text) {
                $name .=  sprintf(
                    $this->additional_property_format,
                    $additional_text
                );
            }
            $results[] = [
                $room->id,
                $name
            ];
        }
        return $results;
    }
}