<?php

/**
 * ResourceManager.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2018
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


require_once 'lib/dates.inc.php';


/**
 * The ResourceManager class contains methods that simplify the use of
 * Resources.
 */
class ResourceManager
{
    // Factory methods

    /**
     * Simplifies the creation of a resource category.
     *
     * @param string $name The name of the new resource category.
     * @param string $description The description of the new resource category.
     * @param string $class_name The class (derived from Resource) which
     *     shall be used for resources created with the new category.
     * @param bool $is_system_category True, if the category shall be a system
     *     category, false otherwise.
     * @param string $iconnr The number of the icon for the resource category.
     * @param mixed[] $properties A two-dimensional array with the
     *     names and requestable, protected and system flags.
     *     The second dimension of the array must have the following structure:
     *     [
     *          property name (string),
     *          requestable flag (boolean),
     *          protected flag (boolean),
     *          system-flag (boolean)
     *     ].
     *
     * @throws ResourceCategoryException In case if no name is set or a resource
     *     property doesn't exist, if the category's name is ambigous
     *     or if the new category cannot be stored, a ResourceCategoryException
     *     is thrown.
     *
     * @return ResourceCategory A new ResourceCategory object.
     */
    public static function createCategory(
        $name = null,
        $description = null,
        $class_name = 'Resource',
        $is_system_category = false,
        $iconnr = '1',
        $properties = []
    )
    {
        if (!$name) {
            //A name must be set!
            throw new InvalidResourceCategoryException(
                _('Es wurde kein Name für die neue Ressourcenkategorie angegeben!')
            );
        }

        $property_data = [];

        //We must check, if all the properties exist:
        if ($properties && is_array($properties)) {
            foreach ($properties as $property) {
                $property_object = ResourcePropertyDefinition::findByName(
                    $property[0]
                );

                if (!$property_object) {
                    throw new ResourcePropertyException(
                        sprintf(
                            _('Die Ressourceneigenschaft %s ist nicht definiert!'),
                            $property[0]
                        )
                    );
                } elseif (count($property_object) > 1) {
                    throw new ResourcePropertyException(
                        sprintf(
                            _('Es gibt mehrere Ressourceneigenschaften mit dem Namen %s!'),
                            $property[0]
                        )
                    );
                }

                //$property_object is an array of ResourcePropertyDefinition objects:
                $property_data[] = [
                    'object' => $property_object[0],
                    'config' => $property
                ];
            }
        }

        //Ok, all properties exist: We can create the category:

        $new_category = new ResourceCategory();
        $new_category->name = $name;
        if ($description != null) {
            $new_category->description = $description;
        }
        $new_category->class_name = $class_name;
        $new_category->system = ($is_system_category ? '1' : '0');
        $new_category->iconnr = $iconnr;

        if ($new_category->store()) {
            //Add the properties:
            foreach ($property_data as $p) {
                $rcp = new ResourceCategoryProperty();
                $rcp->category_id = $new_category->id;
                $rcp->property_id = $p['object']->id;
                $rcp->requestable = $p['config'][1];
                $rcp->protected = $p['config'][2];
                $rcp->system = $p['config'][3];
                $rcp->store();
            }
            //die();
        } else {
            throw new InvalidResourceCategoryException(
                sprintf(
                    _('Die Ressourcenkategorie %s konnte nicht gespeichert werden!'),
                    $name
                )
            );
        }

        return $new_category;
    }

    /**
     * Simplifies the creation of a location resource category.
     *
     * @param string $name The name of the new resource category.
     * @param string $description The description of the new resource category.
     * @param string[] $additional_properties A two-dimensional array with the
     *     names and requestable, protected and system flags. See
     *     ResourceManager::createCategory for a description of the format
     *     of the second array dimension.
     *
     * @throws ResourceCategoryException In case if no name is set or a resource
     *     property doesn't exist, if the category's name is ambigous
     *     or if the new category cannot be stored, a ResourceCategoryException
     *     is thrown.
     *
     * @return ResourceCategory A new ResourceCategory object.
     */
    public static function createLocationCategory(
        $name = null,
        $description = null,
        $additional_properties = []
    )
    {
        $property_names = array_merge(
            [
                [
                    'geo_coordinates',
                    true,
                    true,
                    true
                ]
            ],
            $additional_properties
        );

        return self::createCategory(
            $name,
            $description,
            'Location',
            false,
            '1',
            $property_names
        );
    }


    /**
     * Simplifies the creation of a building resource category.
     *
     * @param string $name The name of the new resource category.
     * @param string $description The description of the new resource category.
     * @param string[] $additional_properties A two-dimensional array with the
     *     names and requestable, protected and system flags. See
     *     ResourceManager::createCategory for a description of the format
     *     of the second array dimension.
     *
     * @throws ResourceCategoryException In case if no name is set or a resource
     *     property doesn't exist, if the category's name is ambigous
     *     or if the new category cannot be stored, a ResourceCategoryException
     *     is thrown.
     *
     * @return ResourceCategory A new ResourceCategory object.
     */
    public static function createBuildingCategory(
        $name = null,
        $description = null,
        $additional_properties = []
    )
    {
        $property_names = array_merge(
            [
                [
                    'address',
                    false,
                    true,
                    true
                ],
                [
                    'accessible',
                    false,
                    true,
                    true
                ],
                [
                    'number',
                    false,
                    true,
                    true
                ],
                [
                    'geo_coordinates',
                    true,
                    true,
                    true
                ]
            ],
            $additional_properties
        );

        return self::createCategory(
            $name,
            $description,
            'Building',
            false,
            '2',
            $property_names
        );
    }


    /**
     * Simplifies the creation of a room resource category.
     *
     * @param string $name The name of the new resource category.
     * @param string $description The description of the new resource category.
     * @param string[] $additional_properties A two-dimensional array with the
     *     names and requestable, protected and system flags. See
     *     ResourceManager::createCategory for a description of the format
     *     of the second array dimension.
     *
     * @throws ResourceCategoryException In case if no name is set or a resource
     *     property doesn't exist, if the category's name is ambigous
     *     or if the new category cannot be stored, a ResourceCategoryException
     *     is thrown.
     *
     * @return ResourceCategory A new ResourceCategory object.
     */
    public static function createRoomCategory(
        $name = null,
        $description = null,
        $additional_properties = []
    )
    {
        $property_names = array_merge(
            [
                [
                    'room_type',
                    true,
                    true,
                    true
                ],
                [
                    'seats',
                    true,
                    true,
                    true
                ],
                [
                    'booking_plan_is_public',
                    true,
                    true,
                    true
                ]
            ],
            $additional_properties
        );

        return self::createCategory(
            $name,
            $description,
            'Room',
            false,
            '3',
            $property_names
        );
    }


    // Resource methods:

    /**
     * Creates a copy of a resource and stores the copy in the database.
     *
     * @param Resource $resource The resource which shall be copied.
     * @param bool $copy_hierarchy True, if the resource's children shall also
     *     be copied (default). False otherwise.
     * @param string $new_parent_id If this is set the original parent_id will
     *     be overwritten with the ID in $new_parent_id.
     *
     * @throws ResourceException If the copy cannot be stored.
     *
     * @returns Resource A copy of the resource.
     */
    public static function copyResource(
        Resource $resource,
        $copy_hierarchy = true,
        $new_parent_id = null
    )
    {
        //We can clone all the data but we must explicitly
        //create a new ID and set the new flag of the copy
        //to prevent updating the original object.
        $copy = clone $resource;
        $copy->id = $copy->getNewId();
        $copy->setNew(true);
        if ($new_parent_id) {
            $copy->parent_id = $new_parent_id;
        }
        if (!$copy->store()) {
            throw new ResourceException();
        }
        if ($copy_hierarchy) {
            //get all children of the original resource and clone them, too.
            //If $copy_hierarchy is set all children and their descendants etc.
            //are cloned recursively.
            $children = $resource->children;
            foreach ($children as $child) {
                $copied_child = self::copyResource(
                    $child,
                    $copy_hierarchy,
                    $copy->id //$copy is the new parent node.
                );
            }
        }
        return $copy;
    }

    /**
     * Moves a resource below another resource and does checks to prevent
     * resource hierarchies with misplaced resource objects.
     * This is just a convenience method which calls the addChild method
     * of the destination resource.
     *
     * @param Resource $target The resource which shall be moved.
     * @param Resource $destination The resource where $target shall be a new child.
     *
     * @throws InvalidArgumentException If $target cannot be placed below $destination.
     *
     * @returns bool True, if $target was successful placed below $destination.
     */
    public static function moveResource(Resource $target, Resource $destination)
    {
        return $destination->addChild($target);
    }


    //Resource retrieval methods:


    /**
     * Helper method that creates the identical SQL query for the
     * countUserResources and findUserResources methods.
     */
    protected static function getUserResourcesSqlData(
        User $user,
        $level = 'user',
        $time = null,
        $class_names = []
    )
    {
        $used_time = time();
        if ($time instanceof DateTime) {
            $used_time = $time->getTimestamp();
        } elseif ($time) {
            $used_time = $time;
        }

        $sql = '';

        if (count($class_names)) {
            //Make sure that all class names specify names
            //of classes derived from the Resource class.
            $valid_class_names = [];
            foreach ($class_names as $class_name) {
                if (is_a($class_name, 'Resource', true)) {
                    $valid_class_names[] = $class_name;
                }
            }
            $class_names = $valid_class_names;
        }
        if (count($class_names)) {
            $sql .= 'INNER JOIN resource_categories rc
                ON resources.category_id = rc.id
                WHERE ';
        }

        $user_is_resource_admin = self::userHasGlobalPermission(
            $user,
            'admin'
        ) || $GLOBALS['perm']->have_perm('root', $user->id);

        if (!$user_is_resource_admin) {
            $sql .= "resources.id IN (
                    SELECT resource_id FROM resource_permissions
                    WHERE user_id = :user_id
                    AND perms IN ( :perms )
                    UNION
                    SELECT resource_id FROM resource_temporary_permissions
                    WHERE user_id = :user_id
                    AND perms IN ( :perms )
                    AND (begin <= :time)
                    AND (end >= :time)
                ) ";
            $data = [
                'user_id' => $user->id,
                'time' => $used_time
            ];
        }

        if (count($class_names) && !$user_is_resource_admin) {
            $sql .= 'AND ';
        }

        if (count($class_names)) {
            $sql .= "rc.class_name IN ( :class_names ) ";
            $data['class_names'] = $class_names;
        }
        $sql .= "GROUP BY resources.id
            ORDER BY sort_position DESC, resources.name ASC";

        $perms = self::getHigherPermissionLevels($level);
        array_push($perms, $level);
        $data['perms'] = $perms;

        return [
            'query' => $sql,
            'data' => $data
        ];
    }


    /**
     * Counts all resources for which the specified user has permanent or
     * temporary permissions.
     *
     * @param User $user The user whose resources shall be retrieved.
     *
     * @param string $level The minimum permission level the user must have
     *     on a resource so that it will be included in the result set.
     *
     * @param DateTime|int|null $time The timestamp for the check on
     *     temporary permissions. If this parameter is not set
     *     the current timestamp will be used.
     *
     * @param string[] $class_names A list of resource classes that will
     *     be used to filter the result set so that only resources being
     *     a member of one of the specified resource classes will be retrieved.
     *
     */
    public static function countUserResources(
        User $user,
        $level = 'user',
        $time = null,
        $class_names = []
    )
    {
        $sql = self::getUserResourcesSqlData($user, $level, $time, $class_names);
        return Resource::countBySql($sql['query'], $sql['data']);
    }


    /**
     * Retrieves all resources for which the specified user has permanent or
     * temporary permissions.
     *
     * @param User $user The user whose resources shall be retrieved.
     *
     * @param string $level The minimum permission level the user must have
     *     on a resource so that it will be included in the result set.
     *
     * @param DateTime|int|null $time The timestamp for the check on
     *     temporary permissions. If this parameter is not set
     *     the current timestamp will be used.
     *
     * @param string[] $class_names A list of resource classes that will
     *     be used to filter the result set so that only resources being
     *     a member of one of the specified resource classes will be retrieved.
     *
     * @param bool $convert_objects If the resource objects
     *     in the result set shall be converted to objects of the derived
     *     resource classes set this to true, otherwise false.
     *     Defaults to true.
     *
     * @returns Resource[] An array of Resource objects
     *     or objects of derived resource classes.
     */
    public static function getUserResources(
        User $user,
        $level = 'user',
        $time = null,
        $class_names = [],
        $convert_objects = true
    )
    {
        $sql = self::getUserResourcesSqlData($user, $level, $time, $class_names);
        $resources = Resource::findBySql($sql['query'], $sql['data']);
        if ($convert_objects) {
            $result = [];
            foreach ($resources as $resource) {
                $result[] = $resource->getDerivedClassInstance();
            }
            return $result;
        } else {
            return $resources;
        }
    }


    // Static methods for position properties:

    public static function getPositionArray(ResourceProperty $property)
    {
        if (!$property->definition) {
            //An orphaned Resource property: we cannot generate an array for it!
            throw new ResourcePropertyDefinitionException(
                _('Die Positionsangabe kann nicht umgewandelt werden, da die angegebene Ressourceneigenschaft verwaist (ohne zugehörige Definition) ist!')
            );
        }

        if ($property->definition->type != 'position') {
            //We cannot generate an array for attributes other than the position type!
            throw new ResourcePropertyException(
                _("Die Positionsangabe kann nicht umgewandelt werden, da die angegebene Ressourceneigenschaft nicht vom Typ 'position' ist!")
            );
        }

        //Parse the ISO-6709 coordinates from $property->value:
        $coordinate_string = $property->state;


        //Check, if the coordinate string ends with "CRSWGS_84/"
        //and if all the numbers are in the appropriate format:
        //- latitude: up to 2 digits, decimal point, 1 to 10 digits for fraction
        //- longitude: up to 3 digits, decimal point, 1 to 10 digits for fraction
        //- altitude: up to 5 digits, decimal point, 1 to 10 digits for fraction
        //before the decimal point. After the decimal point,
        //In that case it is a coordinate format we can parse:

        if(!preg_match(
            ResourcePropertyDefinition::CRSWGS84_REGEX,
            $coordinate_string
        )) {
            throw new ResourcePropertyStateException(
                _('Die Positionsangabe kann nicht umgewandelt werden, da sie ungültige Daten enthält!')
            );
        }

        //With the first split we separate the numbers in the coordinate string.
        //The second split lets us retrieve the sign for each number.

        $coordinate_parts = preg_split(
            '/([+-]|(CRSWGS_84\/))+/',
            $coordinate_string,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        //We can simply split the coordinate string by each dot,
        //since there has to be a dot in every three coordinates!
        //This is because the coordinates are always stored
        //with decimal separators.

        $coordinate_signs = preg_split(
            '/\.[0-9]{1,10}/',
            $coordinate_string,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        //The array position of $coordinate_parts and $coordinate_signs
        //are the same! We can directly use the indexes.
        //If a sign index is less than zero we must invert the value
        //of the corresponding coordinate part.

        for ($i = 0; $i < 3; $i++) {
            if ($coordinate_signs[$i] < 0) {
                $coordinate_parts[$i] *= -1;
            }
        }

        return $coordinate_parts;
    }


    /**
     * This method allows locking the resource management globally.
     * The user who creates the lock must have admin permissions
     * and the time interval must not lie in another global resource lock
     * interval.
     *
     * @param User $user The user who wishes to lock the room and resource
     *      management globally.
     * @param DateTime $begin The begin timestamp of the lock.
     * @param DateTime $end The end timestamp of the lock.
     *
     * @throws InvalidArgumentException If $begin lies after $end or if
     *     $begin is equal to $end.
     * @throws ResourcePermissionException If the specified user does not
     *     have sufficient permissions to lock the resource management globally.
     * @throws GlobalResourceLockException If the resource lock could not be stored.
     *
     * @returns GlobalResourceLock object.
     */
    public function createGlobalLock(
        User $user,
        DateTime $begin,
        DateTime $end,
        $ignore_bookings = false
    )
    {
        if ($begin > $end) {
            throw new InvalidArgumentException(
                _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
            );
        }
        if ($begin == $end) {
            throw new InvalidArgumentException(
                _('Startzeitpunkt und Endzeitpunkt dürfen nicht identisch sein!')
            );
        }

        if (!self::userHasGlobalPermission($user, 'admin')) {
            throw new ResourcePermissionException(
                _('Unzureichende Berechtigungen zum globalen Sperren der Raumverwaltung!')
            );
        }

        if (GlobalResourceLock::existsInTimeRange($begin, $end)) {
            throw new GlobalResourceLockOverlapException(
                sprintf(
                    _('Im Zeitbereich vom %1$s bis %2$s gibt es bereits eine globale Sperrung der Raumverwaltung!'),
                    $begin->format('d.m.Y H:i'),
                    $end->format('d.m.Y H:i')
                )
            );
        }

        $lock = new GlobalResourceLock();
        $lock->begin = $begin->getTimestamp();
        $lock->end = $end->getTimestamp();
        $lock->user_id = $user->id;
        $lock->type = '0';
        if (!$lock->store()) {
            throw new GlobalResourceLockException(
                sprintf(
                    _('Fehler beim Speichern der globalen Sperre der Raumverwaltung im Zeitbereich vom %1$s bis %2$s!'),
                    $begin->format('d.m.Y H:i'),
                    $end->format('d.m.Y H:i')
                )
            );
        }
        return $lock;
    }


    //Special methods for attributes of type position:

    public static function getPositionString(
        ResourceProperty $property,
        $with_altitude = false
    )
    {
        $coordinate_parts = [];
        try {
            $coordinate_parts = self::getPositionArray($property);
        } catch (ResourcePropertyDefinitionException $e) {
            //An orphaned Resource property: we cannot generate a string for it!
            throw new ResourcePropertyDefinitionException(
                _('Die Positionsangabe kann nicht formatiert werden, da die angegebene Ressourceneigenschaft verwaist (ohne zugehörige Definition) ist!')
            );
        } catch (ResourcePropertyException $e) {
            //We cannot generate a string for attributes other than the position type!
            throw new ResourcePropertyException(
                _('Die Positionsangabe kann nicht formatiert werden, da die angegebene Ressourceneigenschaft nicht vom Typ "position" ist!')
            );
        } catch (ResourcePropertyStateException $e) {
            //We cannot generate a string from invalid data!
            return '';
        }

        $locale = localeconv();

        if ($coordinate_parts[0] < 0) {
            $string .= sprintf(
                _('%s°S'),
                number_format(
                    abs($coordinate_parts[0]),
                    7,
                    $locale['decimal_point'],
                    $locale['thousands_sep']
                )
            ) . ' ';
        } else {
            $string .= sprintf(
                _('%s°N'),
                number_format(
                    abs($coordinate_parts[0]),
                    7,
                    $locale['decimal_point'],
                    $locale['thousands_sep']
                )
            ) . ' ';
        }

        if ($coordinate_parts[1] < 0) {
            $string .= sprintf(
                _('%s°O'),
                number_format(
                    abs($coordinate_parts[1]),
                    7,
                    $locale['decimal_point'],
                    $locale['thousands_sep']
                )
            ) . ' ';
        } else {
            $string .= sprintf(
                _('%s°W'),
                number_format(
                    abs($coordinate_parts[1]),
                    7,
                    $locale['decimal_point'],
                    $locale['thousands_sep']
                )
            );
        }

        if ($with_altitude) {
            $string .=  ' ';
            if ($coordinate_parts[2] < 0) {
                $string .= sprintf(
                    _('%s m unter NHN'),
                    number_format(
                        abs($coordinate_parts[2]),
                        1,
                        $locale['decimal_point'],
                        $locale['thousands_sep']
                    )
                );
            } else {
                $string .= sprintf(
                    _('%s m über NHN'),
                    number_format(
                        abs($coordinate_parts[2]),
                        1,
                        $locale['decimal_point'],
                        $locale['thousands_sep']
                    )
                );
            }
        }

        return $string;
    }


    public static function getMapUrlForResourcePosition(
        ResourceProperty $property
    )
    {
        $coordinate_parts = [];
        try {
            $coordinate_parts = self::getPositionArray($property);
        } catch (ResourcePropertyDefinitionException $e) {
            //An orphaned Resource property: we cannot generate an URL for it!
            throw new InvalidArgumentException(
                _('Eine URL zur Straßenkarte kann nicht erzeugt werden, da die angegebene Ressourceneigenschaft verwaist (ohne zugehörige Definition) ist!')
            );
        } catch (ResourcePropertyException $e) {
            //We cannot generate an URL for attributes other than the position type!
            throw new InvalidArgumentException(
                _("Eine URL zur Straßenkarte kann nicht erzeugt werden, da die angegebene Ressourceneigenschaft nicht vom Typ 'position' ist!")
            );
        } catch (ResourcePropertyStateException $e) {
            //We cannot generate an URL from invalid data!
            return '';
        }

        $map_service_url = Config::get()->RESOURCES_MAP_SERVICE_URL;
        if ($map_service_url) {
            //Replace the strings LATITUDE and LONGITUDE in the URL
            //with the coordinates.
            return str_replace(
                [
                    'LATITUDE',
                    'LONGITUDE'
                ],
                [
                    $coordinate_parts[0],
                    $coordinate_parts[1]
                ],
                $map_service_url
            );
        } else {
            //Default to OpenStreepMap:
            return sprintf(
                'https://www.openstreetmap.org/#map=17/%1$s/%2$s',
                $coordinate_parts[0],
                $coordinate_parts[1]
            );
        }
    }


    // User permission methods:

    /**
     * Returns the resource management global permissions for a user,
     * determined by the assigned roles and by the user's global permissions.
     * This method does the mapping from the old resource management permissions
     * to the new resource management permissions.
     */
    public static function getGlobalResourcePermission(User $user)
    {
        global $perm;
        //First we check if the user is a root user:

        if ($perm->get_perm($user->id) == 'root') {
            return 'admin';
        }

        //The user is not a root user:
        //We must check if he has special permissions
        //for the virtual resource with id "global":

        $permission = ResourcePermission::findOneBySql(
            "user_id = :user_id AND resource_id = 'global'",
            [
                'user_id' => $user->id
            ]
        );

        if (!$permission) {
            //No global permissions in the resource management:
            return '';
        }

        if (GlobalResourceLock::currentlyLocked()) {
            //A global permission object exist. But since the
            //resource management is locked only 'user' permissions
            //are allowed, when the user does not have 'admin' permissions:
            return (
                $permission->perms == 'admin'
                ? 'admin'
                : 'user'
            );
        }
        return $permission->perms;
    }


    /**
     * Determines if the specified user has the specified permission level set
     * for at least one resource.
     *
     * @param User $user The users whose resource permissions shall be retrieved.
     * @param string $level The permission level the user should have
     *     on at least one resource.
     * @param string|int|DateTime|null $time The timestamp
     *     for the temporary permission level check.
     *     If this is not set the current timestamp will be used.
     */
    public static function userHasResourcePermissions(
        User $user,
        $level = 'admin',
        $time = null
    )
    {
        //Get all permissions and temporary permissions of the user:

        $permissions = ResourcePermission::findBySQL(
            "user_id = :user_id AND resource_id <> 'global'",
            [
                'user_id' => $user->id
            ]
        );

        if ($permissions) {
            foreach ($permissions as $permission) {
                if (self::comparePermissionLevels($permission->perms, $level) >= 0) {
                    //We have found a permission which is higher or equal
                    //to the requested permission level and can therefore
                    //return true:
                    return true;
                }
            }
        }

        //No (sufficient) permanent permissions exist for the user
        //for at least one resource. We must check the temporary permissions:

        $used_time = time();
        if ($time instanceof DateTime) {
            $used_time = $time->getTimestamp();
        } elseif ($time) {
            $used_time = $time;
        }

        $temp_permissions = ResourceTemporaryPermission::findBySql(
            "user_id = :user_id AND begin <= :time AND end >= :time
            AND resource_id != 'global'",
            [
                'user_id' => $user->id,
                'time' => $used_time
            ]
        );

        if ($temp_permissions) {
            foreach ($temp_permissions as $permission) {
                if (self::comparePermissionLevels($permission->perms, $level) >= 0) {
                    //We have found a temporary permission which is higher or
                    //equal to the requested permission level and can therefore
                    //return true:
                    return true;
                }
            }
        }

        //We haven't found any permanent or temporary permission for the user
        //that match the requested permission level on the specified timestamp.
        return false;
    }


    //Helper methods:


    /**
     * Returns all permission levels lower than the specified level.
     */
    public static function getLowerPermissionLevels($level = 'user')
    {
        $defined_levels = ['user', 'autor', 'tutor', 'admin'];
        if (!in_array($level, $defined_levels)) {
            return [];
        }

        if ($level == 'admin') {
            return array_slice($defined_levels, 0, 3);
        } elseif ($level == 'tutor') {
            return array_slice($defined_levels, 0, 2);
        } elseif ($level == 'autor') {
            return array_slice($defined_levels, 0, 1);
        } else {
            //There is no lower authority than user:
            return [];
        }
    }


    /**
     * Returns all permission levels higher than the specified level.
     */
    public static function getHigherPermissionLevels($level = 'user')
    {
        $defined_levels = ['user', 'autor', 'tutor', 'admin'];
        if (!in_array($level, $defined_levels)) {
            return [];
        }

        if ($level == 'admin') {
            //There is no higher authority than admin:
            return [];
        } elseif ($level == 'tutor') {
            return array_slice($defined_levels, -1, 1);
        } elseif ($level == 'autor') {
            return array_slice($defined_levels, -2, 2);
        } elseif ($level == 'user') {
            return array_slice($defined_levels, -3, 3);
        } else {
            //We haven't found what you're looking for:
            return [];
        }
    }


    /**
     * Compares two resource permission levels and returns an integer telling if
     * the first level is less than (-1), equal (0) or greater than (1) the second level.
     *
     * @param string $level The first permission level.
     * @param string $other_level The second permission level.
     *
     * @throws InvalidArgumentException if $level or $other_level are either not set
     *     or if they contain invalid permission level strings.
     * @returns integer -1 if $level is less than $other_level,
     *     0 if both are equal,
     *     1 if $level is greater than $other_level.
     */
    public static function comparePermissionLevels(
        $level = 'user',
        $other_level = 'user'
    )
    {
        if (!$level or !$other_level) {
            throw new InvalidArgumentException(
                _('Mindestens eine Rechtestufe fehlt zum Vergleich!')
            );
        }

        //The level list starts with the lowest permission level
        //and ends with the highest permission level.
        //The levels are compared by comparing the index values
        //of the list.
        $defined_levels = ['user', 'autor', 'tutor', 'admin'];

        if (!in_array($level, $defined_levels)) {
            throw new InvalidArgumentException(
                _('Die angegebene Rechtestufe ist ungültig!')
            );
        }
        if (!in_array($other_level, $defined_levels)) {
            throw new InvalidArgumentException(
                _('Die angegebene Rechtestufe ist ungültig!')
            );
        }

        $level_index = array_search($level, $defined_levels);
        $other_level_index = array_search($other_level, $defined_levels);

        $diff = $level_index - $other_level_index;

        if ($diff > 0) {
            //$level is a higher permission level than $other_level.
            return 1;
        } elseif ($diff < 0) {
            //$level is a lower permission level than $other_level.
            return -1;
        } else {
            //$level and $other_level are the same permission level.
            return 0;
        }
    }


    /**
     * Checks if the specified user has the specified permission level
     * for the resource management system.
     *
     * @param User $user The user whose global resource permissions
     *     shall be checked.
     * @param string $requested_permission The required permission level
     *     for the user.
     *
     * @returns bool True, if the user has the required permission level,
     *     false otherwise.
     */
    public static function userHasGlobalPermission(
        User $user,
        $requested_permission = 'user'
    )
    {
        //ResourceManager::getGlobalResourcePermission also checks
        //for global resource locks and returns 'admin' if the
        //user is 'admin' user or 'user' in all other cases
        //where the user has permissions on the resource management system.
        $existing_permission = self::getGlobalResourcePermission($user);

        if (!$existing_permission) {
            //No permissions in the resource management:
            return false;
        }

        return self::comparePermissionLevels(
            $existing_permission,
            $requested_permission
        ) > -1;
    }


    /**
     * Counts the resources where the specified user has explicit
     * permissions set, optionally limiting the result to permanent
     * permissions.
     *
     * @param User $user The user whose resource permissions
     *     shall be checked.
     * @param string $requested_permission The required minimum permission
     *     level for the user.
     *
     * @return int The amount of resources where the specified user has
     *     explicit permissions for.
     */
    public static function countResourcesWithPermissions(
        User $user,
        $requested_permission = 'user',
        $exclude_temporary_permissions = false
    )
    {
        $perms = self::getHigherPermissionLevels($requested_permission);
        array_push($perms, $requested_permission);
        $total = Resource::countBySql(
            "INNER JOIN resource_permissions rp
            USING (resource_id)
            WHERE
            rp.perms IN ( :perms )
            AND
            rp.user_id = :user_id",
            [
                'perms' => $perms,
                'user_id' => $user->id
            ]
        );

        if (!$exclude_temporary_permissions) {
            $now = time();
            $total += Resource::countBySql(
                "INNER JOIN resource_temporary_permissions rtp
                USING (resource_id)
                WHERE
                rtp.perms IN ( :perms )
                AND
                rtp.user_id = :user_id
                AND
                rtp.begin <= :now
                AND
                rtp.end >= :now",
                [
                    'perms' => $perms,
                    'user_id' => $user->id,
                    'now' => $now
                ]
            );
        }

        return $total;
    }


    /**
     * Checks if the specified user has the specified permission level
     * on at least one specific resource.
     *
     * @param User $user The user whose resource permissions
     *     shall be checked.
     * @param string $requested_permission The required permission level
     *     for the user.
     *
     * @returns bool True, if the user has the required permission level
     *     for at least one resource, false otherwise.
     */
    public static function userHasSpecialPermissions(
        User $user,
        $requested_permission = 'user'
    )
    {
        return self::countResourcesWithPermissions(
            $user,
            $requested_permission
        ) > 0;
    }


    /**
     * Get time ranges by looking at the object specified by its ID.
     * This method works with CourseDate, SeminarCycleDate
     * and Course objects.
     *
     * @param string $range_id The ID of a Stud.IP object.
     *
     * @returns Array An Array consisting of arrays of DateTime objects.
     *     The structure of the array is as follows:
     *     [
     *         [
     *             begin timestamp DateTime object
     *             end timestamp DateTime object
     *         ],
     *         ...
     *     ]
     */
    public static function getTimeRangesFromRangeId($range_id = null)
    {
        if (!$range_id) {
            return [];
        }

        //We try a course date first, then a cycle date and finally
        //a course as this is the standard order to check for dates.

        $time_ranges = [];

        $course_date = CourseDate::find($range_id);

        if ($course_date) {
            $begin = new DateTime();
            $begin->setTimestamp($course_date->date);
            $end = new DateTime();
            $end->setTimestamp($course_date->end_time);
            $time_ranges[] = [
                $begin,
                $end
            ];
        } else {
            //No course date, but maybe a cycle date?
            $cycle_date = SeminarCycleDate::find($range_id);

            if ($cycle_date) {
                if ($cycle_date->dates) {
                    foreach ($cycle_date->dates as $date) {
                        $begin = new DateTime();
                        $begin->setTimestamp($date->date);
                        $end = new DateTime();
                        $end->setTimestamp($date->end_time);
                        $time_ranges[] = [
                            $begin,
                            $end
                        ];
                    }
                }
            } else {
                //No cycle date. It must be a course then!
                $course = Course::find($range_id);

                if ($course) {
                    if ($course->dates) {
                        foreach ($course->dates as $date) {
                            $begin = new DateTime();
                            $begin->setTimestamp($date->date);
                            $end = new DateTime();
                            $end->setTimestamp($date->end_time);
                            $time_ranges[] = [
                                $begin,
                                $end
                            ];
                        }
                    }
                    if ($course->cycles) {
                        foreach ($course->cycles as $cycle) {
                            if ($cycle->dates) {
                                foreach ($cycle->dates as $date) {
                                    $begin = new DateTime();
                                    $begin->setTimestamp($date->date);
                                    $end = new DateTime();
                                    $end->setTimestamp($date->end_time);
                                    $time_ranges[] = [
                                        $begin,
                                        $end
                                    ];
                                }
                            }
                        }
                    }
                }
                //No else here: Enough is enough.
            }
        }

        return $time_ranges;
    }


    /**
     * Determines the last booking of the user and calculates the timespan
     * from the last booking until now.
     *
     * @returns DateInterval|null|false Either a date interval from the last
     *     activity to the provided DateTime object or null in case the user
     *     has never been active. False is returned in case the timestamp
     *     comparison fails. @see DateTime::diff in the PHP Documentation.
     */
    public static function getUserInactivityInterval(User $user, DateTime $time)
    {
        $db = DBManager::get();

        $stmt = $db->prepare(
            "SELECT MAX(mkdate) FROM resource_bookings
            WHERE range_id = :user_id"
        );

        $stmt->execute(['user_id' => $user->id]);

        $last_activity_timestamp = $stmt->fetchColumn();

        if (($last_activity_timestamp === false) or ($last_activity_timestamp === null)) {
            //No activity found
            return null;
        }

        $last_activity = new DateTime();
        $last_activity->setTimestamp($last_activity_timestamp);

        //Calculate the difference between the last activity and $time,
        //if $time is greater or equal than $last_activity.
        //If $time is less than $last_activity return null since the user
        //has not been active at the timestamp $time.

        if ($time < $last_activity) {
            //The user has not been active at $time.
            return null;
        }

        return $time->diff($last_activity);
    }


    /**
     * Retrieves booking plan objects like resource bookings and requests.
     *
     * @param string|null $included_request_types If this parameter is a string,
     *     it can have the values 'all' for retrieving all requests or the
     *     ID of a user so that only requests of that user are retrieved.
     *
     */
    public static function getBookingPlanObjects(
        Resource $resource,
        $time_ranges = [],
        $allowed_booking_types = [],
        $included_requests = null
    )
    {
        if (!count($time_ranges)) {
            return [];
        }

        $objects = [];

        $bookings = \ResourceBooking::findByResourceAndTimeRanges(
            $resource,
            $time_ranges,
            $allowed_booking_types
        );
        $objects = array_merge($objects, $bookings);

        if ($included_requests == 'all') {
            $requests = \ResourceRequest::findByResourceAndTimeRanges(
                $resource,
                $time_ranges,
                0
            );
            $objects = array_merge($objects, $requests);
        } elseif ($included_requests) {
            $requests = \ResourceRequest::findByResourceAndTimeRanges(
                $resource,
                $time_ranges,
                0,
                [],
                'user_id = :user_id',
                ['user_id' => $included_requests]
            );
            $objects = array_merge($objects, $requests);
        }

        return $objects;
    }


    public static function getAllResourceClassNames($excluded_classes = [])
    {
        $class_names = [];
        //We have to make the autoloader load all resource model classes,
        //otherwise the get_declared_classes() statement below won't find
        //any class derived from Resource!
        foreach (
            scandir($GLOBALS['STUDIP_BASE_PATH'] . '/lib/models/resources')
            as $resource_model_file) {
            $path = pathinfo($resource_model_file);

            if ($path['extension'] == 'php') {
                $class_name = explode('.class', $path['filename'])[0];
                class_exists($class_name);
            }
        }

        foreach (get_declared_classes() as $class_name) {
            if (is_a($class_name, 'Resource', true)) {
                foreach ($excluded_classes as $excl_class) {
                    //For the resource base class, we must not check
                    //derived classes.
                    if ($excl_class == 'Resource') {
                        if ($class_name == 'Resource') {
                            continue 2;
                        }
                    } else {
                        if (is_a($class_name, $excl_class, true)) {
                            //The class belongs to one of the
                            //excluded resource classes.
                            continue 2;
                        }
                    }
                }
                $class_names[] = $class_name;
            }
        }
        sort($class_names);

        return $class_names;
    }


    /**
     * Returns the names of all hierarchy elements from the root to the
     * specified resource.
     *
     * @param Resource $room The resource to start with.
     *
     * @returns string[] An array with the names of the hierarchy elements,
     *     starting with the top resource's name.
     */
    public static function getHierarchyNames(Resource $resource)
    {
        $names = [$resource->name];
        $current_node = $resource->parent;
        while ($current_node instanceof Resource) {
            $names[] = $current_node->name;
            $current_node = $current_node->parent;
        }
        return array_reverse($names);
    }


    /**
     * Returns the hierarchy elements from the root to the
     * specified resource.
     *
     * @param Resource $room The resource to start with.
     *
     * @returns Resource[] An array with the hierarchy elements,
     *     starting with the top resource.
     */
    public static function getHierarchy(Resource $resource)
    {
        $hierarchy_ids = [$resource->id];
        $items = [$resource];
        $current_node = $resource->parent;
        while ($current_node instanceof Resource) {
            if (in_array($current_node->id, $hierarchy_ids)) {
                //Circular hierarchy. That's a big error!
                throw new InvalidResourceException(
                    sprintf(
                        _('Zirkuläre Hierarchie: Die Ressource %1$s ist ein Elternknoten von sich selbst!'),
                        $current_node->name
                    )
                );
            }
            $items[] = $current_node;
            $hierarchy_ids[] = $current_node->id;
            $current_node = $current_node->parent;
        }
        return array_reverse($items);
    }
}
