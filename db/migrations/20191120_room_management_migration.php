<?php


class RoomManagementMigration extends Migration
{
    public function description()
    {
        return 'Adds and alters tables and their fields for the new room and resource management module (former resources management). Furthermore new resource types and their attributes are defined.';
    }


    public function loadDefaultConfiguration()
    {
        //See config/resource_migration.php.dist for an explaination
        //of the variables.
        $GLOBALS['RESOURCE_CATEGORY_CLASS_MAPPING'] = [
            'Gebäude' => 'Building',
            'Hörsaal' => 'Room',
            'Übungsraum' => 'Room',
            'Gerät' => 'Resource',
        ];
        $GLOBALS['RESOURCE_ADMINISTRATION_PERSON_URL'] = 'https://example.org/person/%s';
        $GLOBALS['RESOURCE_MIGRATION_RESOURCE_TREES_TO_BE_DELETED'] = [];
        $GLOBALS['RESOURCE_MIGRATION_MIGRATE_COURSE_PERMISSIONS'] = true;
        $GLOBALS['RESOURCE_PROPERTIES_TO_BE_DELETED'] = [];
        $GLOBALS['RESOURCE_MIGRATION_SPECIALRESOURCESPLUGIN'] = false;
        $GLOBALS['RESOURCE_PROPERTIES_TO_BE_MODIFIED'] = [
            'Adresse' => [
                'name' => 'address',
                'old_type' => 'text'
            ],
            'Audio-Anlage' => [
                'name' => 'has_loudspeakers',
                'old_type' => 'bool',
                'requestable' => true,
                'searchable' => true
            ],
            'Beamer' => [
                'name' => 'has_projector',
                'old_type' => 'bool',
                'requestable' => true,
                'searchable' => true
            ],
            'behindertengerecht' => [
                'name' => 'accessible',
                'old_type' => 'bool',
                'requestable' => true,
                'searchable' => true
            ],
            'Dozentenrechner' => [
                'name' => 'has_computer',
                'old_type' => 'bool',
                'requestable' => true,
                'searchable' => true
            ],
            'Hersteller' => [
                'name' => 'manufacturer',
                'old_type' => 'select',
                'requestable' => true,
                'searchable' => true
            ],
            'Inventarnummer' => [
                'name' => 'inventory_number',
                'old_type' => 'num',
                'requestable' => false,
                'searchable' => true
            ],
            'Seriennummer' => [
                'name' => 'serial_number',
                'old_type' => 'num',
                'requestable' => false,
                'searchable' => true
            ],
            'Sitzplätze' => [
                'name' => 'seats',
                'old_type' => 'num',
                'requestable' => true,
                'searchable' => true
            ],
            'Tageslichtprojektor' => [
                'name' => 'has_overhead_projector',
                'old_type' => 'bool',
                'requestable' => true,
                'searchable' => true
            ],
            'Verdunklung' => [
                'name' => 'is_dimmable',
                'old_type' => 'bool',
                'requestable' => true,
                'searchable' => true
            ],
            'Raumverantwortung' => [
                'name' => 'responsible_person',
                'display_name' => 'Raumverantwortung',
                'old_type' => 'text',
                'new_type' => 'user',
                'requestable' => false,
                'searchable' => false,
                'info_label' => true
            ]
        ];
        $GLOBALS['RESOURCE_PROPERTIES_TO_BE_MERGED'] = [];
    }


    //Assign orphaned resources (resources without category_id)
    //to a new category:
    public function assignOrphanedResources(PDO $db)
    {
        $orphaned_resources = $db->query(
            "SELECT id FROM resources
            WHERE (category_id IS NULL) OR (category_id = '')
            OR category_id NOT IN (SELECT id from resource_categories);"
        )->fetchAll(PDO::FETCH_COLUMN, 0);

        if ($orphaned_resources) {
            //Create a resource category for those resources:

            $md5 = md5('OrphanedResourcesCategory' . rand());

            $db->execute(
                "INSERT INTO resource_categories
                 (id, name, class_name, description, mkdate, chdate)
                 VALUES
                 (?, 'Verwaiste Ressourcen', 'Resource', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                [$md5]
            );

            //Now we assign all orphaned resources to that category:
            $db->execute(
                "UPDATE resources SET category_id = ?
                 WHERE (category_id IS NULL) OR (category_id = '')",
                [$md5]
            );
        }
    }


    public function deleteUnfinishedResources(PDO $db)
    {
        $db->exec(
            "DELETE from resources
            WHERE name = 'Neues Objekt';"
        );
    }


    public function migrateBookingRepeatIntervals(PDO $db)
    {
        //Buchungen unbekannter Räume entfernen
        $db->exec("DELETE resource_bookings FROM resource_bookings LEFT JOIN resources ON resources.id=resource_id WHERE resources.id IS NULL");

        //mehrtägige korrigieren
        $db->exec("UPDATE `resource_bookings` SET end=repeat_end WHERE repeat_end > end AND IFNULL(old_rep_interval,0) = 0");


        //Get all resource_bookings rows that have repetition intervals set:
        $booking_rows = $db->query(
            "SELECT *
            FROM resource_bookings
            WHERE repeat_end > end AND IFNULL(old_rep_interval,0) > 0"
        )->fetchAll();

        $update_stmt = $db->prepare(
            "UPDATE resource_bookings
            SET repetition_interval = :repetition_interval
            WHERE id = :id;"
        );

        foreach ($booking_rows as $row)
        {
            $date_interval = '';
            if ($row['old_rep_week_of_month']) {
                $date_interval = 'P' . $row['old_rep_interval'] . 'M';
            } elseif ($row['old_rep_interval']) {
                if ($row['old_rep_month_of_year']) {
                    $date_interval = 'P' . $row['old_rep_interval'] . 'Y';
                } elseif ($row['old_rep_day_of_month']) {
                    $date_interval = 'P' . $row['old_rep_interval'] . 'M';
                } elseif ($row['old_rep_day_of_week']) {
                    $date_interval = 'P' . ($row['old_rep_interval'] * 7) . 'D';
                } else {
                    $date_interval = 'P' . $row['old_rep_interval'] . 'D';
                }
            }

            $update_stmt->execute(
                [
                    'id' => $row['id'],
                    'repetition_interval' => $date_interval
                ]
            );
        }

        //Delete the old columns:
        $db->exec(
            "ALTER TABLE resource_bookings
            DROP COLUMN old_rep_interval,
            DROP COLUMN old_rep_month_of_year,
            DROP COLUMN old_rep_day_of_month,
            DROP COLUMN old_rep_week_of_month,
            DROP COLUMN old_rep_day_of_week;"
        );

        //set booking_user_id for all bookings made by 'autor'
        $db->exec("UPDATE resource_bookings rb INNER JOIN auth_user_md5 aum ON range_id=user_id INNER JOIN resource_permissions rp ON rp.user_id=aum.user_id AND rb.resource_id = rp.resource_id SET booking_user_id = range_id  WHERE booking_user_id='' AND rp.perms='autor'");
    }


    public function migrateCourseBoundPermissions(PDO $db)
    {
        if ($GLOBALS['RESOURCE_MIGRATION_MIGRATE_COURSE_PERMISSIONS']) {
            //Get all permissions where the range_id
            //represents a course-ID or an institute-ID:

            $permissions = $db->query(
                "SELECT user_id, resource_id, perms, 'course' AS 'range'
                FROM resource_permissions
                WHERE
                user_id IN (
                    SELECT seminar_id FROM seminare
                )
                UNION
                SELECT user_id, resource_id, perms, 'institute' AS 'range'
                FROM resource_permissions
                WHERE
                user_id IN (
                    SELECT Institut_id FROM Institute
                )"
            )->fetchAll();

            $course_participant_stmt = $db->prepare(
                "SELECT user_id
                FROM seminar_user
                WHERE
                Seminar_id = :course_id"
            );

            $institute_participant_stmt = $db->prepare(
                "SELECT user_id, inst_perms
                FROM user_inst
                WHERE
                Institut_id = :institute_id"
            );

            $get_user_permission_stmt = $db->prepare(
                "SELECT perms
                FROM resource_permissions
                WHERE user_id = :user_id AND resource_id = :resource_id"
            );

            $update_user_permission_stmt = $db->prepare(
                "UPDATE resource_permissions
                SET perms = :perms
                WHERE user_id = :user_id AND resource_id = :resource_id"
            );

            $create_user_permission_stmt = $db->prepare(
                "INSERT INTO resource_permissions
                (user_id, resource_id, perms, mkdate, chdate)
                VALUES
                (:user_id, :resource_id, :perms, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());"
            );
            $create_specialresources_entry = $db->prepare(
                "INSERT INTO specialresourcesplugin_course_resources
                (course_id, resource_id, mkdate, chdate)
                VALUES
                (:course_id, :resource_id, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());"
            );

            foreach ($permissions as $permission) {
                //Get all participants of the course/institute:
                $participants = [];
                if ($permission['range'] == 'course') {
                    $course_participant_stmt->execute(
                        [
                            'course_id' => $permission['user_id']
                        ]
                    );
                    $participants = $course_participant_stmt->fetchAll();
                } elseif ($permission['range'] == 'institute') {
                    $institute_participant_stmt->execute(
                        [
                            'institute_id' => $permission['user_id']
                        ]
                    );
                    $participants = $institute_participant_stmt->fetchAll();
                }

                foreach ($participants as $participant) {
                    if (($permission['range'] == 'institute') &&
                        !in_array($participant['inst_perms'], ['autor', 'tutor', 'dozent', 'admin'])) {
                        //The permission level in the institute is too low.
                        continue;
                    }
                    //Check if a permission exists for that participant:
                    $get_user_permission_stmt->execute(
                        [
                            'user_id' => $participant['user_id'],
                            'resource_id' => $permission['resource_id']
                        ]
                    );
                    $existing_perm = $get_user_permission_stmt->fetch(PDO::FETCH_COLUMN);

                    if ($existing_perm) {
                        if (!in_array($existing_perm, ['autor', 'tutor', 'admin'])) {
                            //Update the permission and set it to "autor":
                            $update_user_permission_stmt->execute(
                                [
                                    'user_id' => $participant['user_id'],
                                    'resource_id' => $permission['resource_id'],
                                    'perms' => 'autor'
                                ]
                            );
                        }
                    } else {
                        //Create a new permission: Give every participant
                        //'autor' permissions for the resource
                        //that was specified in the course permission:
                        $create_user_permission_stmt->execute(
                            [
                                'user_id' => $participant['user_id'],
                                'resource_id' => $permission['resource_id'],
                                'perms' => 'autor'
                            ]
                        );
                    }
                }

                if ($GLOBALS['RESOURCE_MIGRATION_SPECIALRESOURCESPLUGIN']) {
                    $create_specialresources_entry->execute(
                        [
                            'course_id' => $permission['user_id'],
                            'resource_id' => $permission['resource_id']
                        ]
                    );
                }
            }
        }
    }

    public function migrateOwner()
    {
        $db = DBManager::get();
        $db->exec("INSERT IGNORE INTO resource_permissions
                    (user_id, resource_id, perms, mkdate, chdate)
                    SELECT r.owner_id, r.id, 'admin', r.mkdate, r.chdate
                    FROM resources r INNER JOIN resource_categories rc
                    ON r.category_id = rc.id AND class_name='Room'
                    WHERE EXISTS
                        (SELECT * FROM auth_user_md5 WHERE owner_id = user_id)");
        $responsible_property_id =
            $db->fetchColumn("SELECT property_id FROM resource_property_definitions WHERE name = 'responsible_person'");
        if ($responsible_property_id) {
            $db->exec("INSERT IGNORE INTO resource_properties
                    (resource_id, property_id,state,mkdate,chdate)
                    SELECT r.id, '$responsible_property_id', r.owner_id, r.mkdate, r.chdate
                    FROM resources r INNER JOIN resource_categories rc
                    ON r.category_id = rc.id AND class_name='Room'
                    WHERE EXISTS
                        (SELECT * FROM auth_user_md5 WHERE owner_id = user_id)");
        }
        $db->exec("ALTER TABLE resources DROP COLUMN owner_id");
    }

    public function deleteOldPermissions(PDO $db) {
        //Delete all permissions that aren't permissions of existing users:
        $db->exec(
            "DELETE FROM resource_permissions
            WHERE user_id NOT IN (
                SELECT user_id FROM auth_user_md5
            );"
        );
    }


    public function deleteResources()
    {
        $resource_trees_to_be_deleted =
            $GLOBALS['RESOURCE_MIGRATION_RESOURCE_TREES_TO_BE_DELETED'];
        if (count($resource_trees_to_be_deleted)) {
            foreach ($resource_trees_to_be_deleted as $resource_id) {
                $resource = Resource::find($resource_id);
                if ($resource instanceof Resource) {
                    $resource->delete();
                }
            }
        }
    }


    public function migrateLocations(PDO $db, $location_cat_id = null)
    {
        if (!$location_cat_id) {
            //This should not happen!
            throw new Exception('Internal error!');
        }

        //First we create one Location data set for orphaned buildings:
        $orphan_location_stmt = $db->prepare(
            "INSERT INTO resources
                (id, category_id, name)
                VALUES (:id, :category_id, :name);"
        );

        $orphan_location_id = md5('SonstigeGebäudeResourceLocation' . rand());

        $orphan_location_stmt->execute(
            [
                'id' => $orphan_location_id,
                'category_id' => $location_cat_id,
                'name' => 'Sonstige Gebäude'
            ]
        );

        //We must get all IDs of resources which are parents
        //of building resources. Such resources and their categories
        //are Locations.
        //Buildings without a parent are attached to a new location
        //which is built for this case.

        $building_resources = $db->query(
            "SELECT resources.id as id, parent_id FROM resources
            INNER JOIN resource_categories
                ON resources.category_id = resource_categories.id
            WHERE
                resource_categories.class_name = 'Building';"
        )->fetchAll();

        //We have the ID and the parent-ID of all building resources.
        //Now we get all categories of the building's parents
        //and set their class_name to 'Location'.
        //Furthermore we remove the parent's parents since Location
        //resources must not have parent resources.

        $get_parent_stmt = $db->prepare(
            "SELECT id, category_id FROM resources
            WHERE id = :parent_id"
        );

        $update_location_category_stmt = $db->prepare(
            "UPDATE resource_categories SET class_name = 'Location'
            WHERE id = :category_id;"
        );

        $update_parent_stmt = $db->prepare(
            "UPDATE resources SET parent_id = :parent_id
            WHERE id = :id;"
        );

        $update_category_stmt = $db->prepare(
            "UPDATE resources SET category_id = :category_id
            WHERE id = :id;"
        );

        //Ok, now we can look for all buildings and give them a parent_id
        //if they don't have one and we can remove their parent's parent_id,
        //if they have one.

        foreach ($building_resources as $building) {
            if ($building['parent_id']) {
                //parent_id is set: get the parent:
                $get_parent_stmt->execute(
                    ['parent_id' => $building['parent_id']]
                );

                $parents = $get_parent_stmt->fetchAll();

                foreach ($parents as $parent) {
                    if ($parent['category_id']) {
                        //Update the parent's category since it is a location:
                        $update_location_category_stmt->execute(
                            [
                                'category_id' => $parent['category_id']
                            ]
                        );
                    } else {
                        //Parent does not belong to a category:
                        //set the category to our orphaned location category:
                        $update_category_stmt->execute(
                            [
                                'category_id' => $location_cat_id,
                                'id' => $parent['id']
                            ]
                        );
                    }

                    if ($parent['parent_id']) {
                        //If the parent's parent_id is set we must delete it:
                        $update_parent_stmt->execute(
                            [
                                'parent_id' => '',
                                'id' => $parent['id']
                            ]
                        );
                    }
                }
            } else {
                //The building has no parent_id: We must add it to our
                //orphan building location:
                $update_parent_stmt->execute(
                    [
                        'parent_id' => $orphan_location_id,
                        'id' => $building['id']
                    ]
                );
            }
        }

        //Now all buildings should be connected to locations and all locations
        //should be without a parent.
    }


    public function migrateBuildings(PDO $db)
    {
        //We must set the class name to 'Building' for all
        //resource categories whose name is like 'gebäude'.

        $db->exec(
            "UPDATE resource_categories SET class_name = 'Building'
            WHERE name LIKE '%gebäude%';"
        );

    }


    public function migrateRooms(PDO $db)
    {
        //Set the category's class name to 'Room' for all categories
        //which have a name similar to 'Raum' or 'Saal':

        $db->exec(
            "UPDATE resource_categories
            SET class_name = 'Room'
            WHERE name LIKE '%raum%' OR name LIKE '%saal%';"
        );


    }


    public function deleteExistingProperties(PDO $db)
    {
        $properties_to_be_deleted = $GLOBALS['RESOURCE_PROPERTIES_TO_BE_DELETED'];
        if (!count($properties_to_be_deleted)) {
            //Nothing to do.
            return;
        }

        $delete_definition_stmt = $db->prepare(
            'DELETE FROM resource_property_definitions
            WHERE property_id IN ( :property_ids )'
        );
        $delete_resource_property_stmt = $db->prepare(
            'DELETE FROM resource_properties
            WHERE property_id IN ( :property_ids )'
        );
        $delete_resource_request_property_stmt = $db->prepare(
            'DELETE FROM resource_request_properties
            WHERE property_id IN ( :property_ids )'
        );

        $delete_resource_request_property_stmt->execute(
            ['property_ids' => $properties_to_be_deleted]
        );
        $delete_resource_property_stmt->execute(
            ['property_ids' => $properties_to_be_deleted]
        );
        $delete_definition_stmt->execute(
            ['property_ids' => $properties_to_be_deleted]
        );
    }


    public function migrateExistingProperties(PDO $db)
    {
        $this->write(
            '# Migrating existing properties.'
        );

        //rename resource properties:
        $get_property_stmt = $db->prepare(
            "SELECT property_id
            FROM resource_property_definitions
            WHERE
            name = :name AND type = :type;"
        );

        $update_property_stmt = $db->prepare(
            "UPDATE resource_property_definitions
            SET name = :name,
            `system` = 1,
            searchable = :searchable,
            range_search = :range_search
            WHERE property_id = :property_id;"
        );

        $insert_new_prop_stmt = $db->prepare(
            "INSERT INTO resource_property_definitions
            (`property_id`, `name`, `description`, `type`, `options`,
            `info_label`, `display_name`, `searchable`, `range_search`,
            `write_permission_level`, `system`, `mkdate`, `chdate`)
            VALUES
            (:property_id, :name, :description, :type, :options,
            :info_label, :display_name, :searchable, :range_search,
            :write_permission_level, :system, :mkdate, :chdate);"
        );

        $delete_duplicates_stmt = $db->prepare(
            'DELETE FROM resource_property_definitions
            WHERE property_id IN ( :duplicate_ids );'
        );

        $requestable_attr_stmt = $db->prepare(
            "UPDATE resource_category_properties
            SET requestable = :requestable
            WHERE property_id = :property_id;"
        );

        $properties_to_be_modified = $GLOBALS['RESOURCE_PROPERTIES_TO_BE_MODIFIED'];
        foreach ($properties_to_be_modified as $old_name => $data) {
            //Check if the old property exists:
            $get_property_stmt->execute(
                [
                    'name' => $old_name,
                    'type' => $data['old_type']
                ]
            );
            $old_property_ids = $get_property_stmt->fetchAll(
                PDO::FETCH_COLUMN,
                0
            );
            if ($data['name'] === 'seats' && !count($old_property_ids)) {
                $old_property_ids = $db->fetchFirst(
                    "SELECT `property_id`
                    FROM `resource_property_definitions`
                    WHERE `system` = 2");
            }

            //Check if the new property already exists:
            $get_property_stmt->execute(
                [
                    'name' => $data['name'],
                    'type' => $data['old_type']
                ]
            );

            $final_property_id = null;
            $duplicate_ids = [];

            $new_property_id = $get_property_stmt->fetchColumn();
            if ($new_property_id) {
                //The new property already exists. We must set all values
                //from the old property to the new property.
                $final_property_id = $new_property_id;
                $duplicate_ids = $old_property_ids;
            }

            if (!$final_property_id) {
                //The new property doesn't exist yet. We must either
                //take one of the old properties and rename it
                //or we must create a new property with that name.
                if ((count($old_property_ids) > 0) && !$final_property_id) {
                    $this->write(
                        sprintf(
                            'Property-IDs for "%1$s" = [%2$s]',
                            $old_name,
                            implode(', ', $old_property_ids)
                        )
                    );
                    if (count($old_property_ids) > 1) {
                        //Remove the first item of the $old_property_list array,
                        //store it in $final_property and use the other IDs
                        //in the duplicates array.
                        $final_property_id = array_shift($old_property_ids);
                        $duplicate_ids[] = $old_property_ids;
                    } else {
                        //The only property ID is the first item in the
                        //$old_property_ids array.
                        $final_property_id = $old_property_ids[0];
                    }
                    $this->write(
                        sprintf(
                            'Renaming property "%1$s" to "%2$s".',
                            $old_name,
                            $data['name']
                        )
                    );
                    //Update the property:
                    $update_property_stmt->execute(
                        [
                            'name' => $data['name'],
                            'searchable' => ($data['searchable'] ? '1' : '0'),
                            'range_search' => ($data['range_search'] ? '1' : '0'),
                            'property_id' => $final_property_id
                        ]
                    );
                } else {
                    $this->write(
                        sprintf(
                            'No property with the name "%1$s" and the type "%2$s" could be found! Creating a new property.',
                            $old_name,
                            $data['old_type']
                        )
                    );
                    //There is no old property defined. We must define one.
                    $now = time();
                    $property_id = md5('RRV2NewProperty' . $data['name'] . rand());
                    $this->write(
                        'INFO: The property-ID of the new property "'
                      . $data['name'] . '" is: ' . $property_id
                    );
                    $insert_new_prop_stmt->execute(
                        [
                            'property_id' => $property_id,
                            'name' => $data['name'],
                            'description' => (
                                $data['description']
                                ? $data['description']
                                : ''
                            ),
                            'system' => 1,
                            'type' => $data['new_type'] ? $data['new_type'] : $data['old_type'],
                            'options' => ($data['options'] ? $data['options'] : ''),
                            'info_label' => $data['info_label'] ? '1' : '0',
                            'display_name' => (
                                $data['display_name']
                                ? $data['display_name']
                                : $data['name']
                            ),
                            'searchable' => ($data['searchable'] ? '1' : '0'),
                            'range_search' => ($data['range_search'] ? '1' : '0'),
                            'write_permission_level' => 'admin-global',
                            'mkdate' => $now,
                            'chdate' => $now
                        ]
                    );
                    $final_property_id = $property_id;
                }
            }

            if ($duplicate_ids) {
                $this->write(
                    sprintf(
                        'Moving old property values to the new property "%s".',
                        $data['name']
                    )
                );
                //Now we must "redirect" all links to the duplicates
                //to the property that is left
                //and then remove the duplicates.

                $tables = [
                    'resource_category_properties',
                    'resource_properties',
                    'resource_request_properties'
                ];
                foreach ($tables as $table) {
                    $update_tables_stmt = $db->prepare(
                        sprintf(
                            "UPDATE IGNORE %s
                            SET property_id = :property_id
                            WHERE property_id IN ( :duplicate_ids );",
                            $table
                        )
                    );
                    $update_tables_stmt->execute(
                        [
                            'property_id' => $final_property_id,
                            'duplicate_ids' => $duplicate_ids
                        ]
                    );
                }

                $this->write(
                    sprintf(
                        'Deleting property values for property-IDs [%s].',
                        implode(', ', $duplicate_ids)
                    )
                );

                //Now we delete the duplicates:
                $delete_duplicates_stmt->execute(
                    [
                        'duplicate_ids' => $duplicate_ids
                    ]
                );

                //After that we have to delete those property relations
                //whose property_id couldn't be set above
                //to avoid duplicate primary keys.
                $delete_relations_stmt = $db->prepare(
                    sprintf(
                        "DELETE FROM %s
                    WHERE property_id IN ( :duplicate_ids );",
                        $table
                    )
                );
                foreach ($tables as $table) {
                    $delete_relations_stmt->execute(
                        [
                            'duplicate_ids' => $duplicate_ids
                        ]
                    );
                }
            }

            //Finally we make the property requestable, if configured:
            $requestable_attr_stmt->execute(
                [
                    'property_id' => $final_property_id,
                    'requestable' => ($data['requestable'] ? '1' : '0')
                ]
            );
        }

        $this->write(
            'Finished migrating existing properties.'
        );
    }


    public function mergeProperties(PDO $db)
    {
        $this->write(
            '# Merging properties.'
        );

        $get_prop_stmt = $db->prepare(
            "SELECT property_id, name FROM resource_property_definitions
            WHERE name = :name AND type = :type"
        );

        $insert_prop_stmt = $db->prepare(
            "INSERT INTO resource_property_definitions
            (`property_id`, `name`, `description`, `type`, `options`,
            `info_label`, `display_name`, `searchable`, `range_search`,
            `write_permission_level`, `system`, `mkdate`, `chdate`)
            VALUES
            (:property_id, :name, :description, :type, :options,
            :info_label, :display_name, :searchable, :range_search,
            :write_permission_level, :system, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());"
        );

        $get_property_values_stmt = $db->prepare(
            "SELECT property_id, resource_id, state FROM resource_properties
            WHERE property_id = :property_id;"
        );

        $insert_new_value_stmt = $db->prepare(
            "INSERT IGNORE INTO resource_properties
            (property_id, resource_id, state)
            VALUES
            (:property_id, :resource_id, :state);"
        );

        $get_category_id_stmt = $db->prepare(
            "SELECT DISTINCT category_id FROM resource_category_properties
            WHERE property_id IN ( :property_ids );"
        );

        $prop_cat_assign_stmt = $db->prepare(
            "INSERT INTO resource_category_properties
            (`category_id`, `property_id`, `requestable`, `protected`,
            `system`, `form_text`, `mkdate`, `chdate`)
            VALUES
            (:category_id, :property_id, :requestable, :protected,
            :system, :form_text, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
            ON DUPLICATE KEY UPDATE
            `requestable` = :requestable,
            `protected` = :protected,
            `system` = :system,
            `form_text` = :form_text;"
        );

        $delete_old_prop_stmt = $db->prepare(
            "DELETE FROM resource_property_definitions
            WHERE property_id IN ( :property_ids );"
        );

        $delete_resource_request_property_values_stmt = $db->prepare(
            "DELETE FROM resource_request_properties
            WHERE property_id IN ( :property_ids );"
        );

        $delete_resource_property_values_stmt = $db->prepare(
            "DELETE FROM resource_properties
            WHERE property_id IN ( :property_ids );"
        );

        $properties_to_be_merged = $GLOBALS['RESOURCE_PROPERTIES_TO_BE_MERGED'];
        foreach ($properties_to_be_merged as $merged_prop) {
            $get_prop_stmt->execute(
                [
                    'name' => $merged_prop['name'],
                    'type' => $merged_prop['type']
                ]
            );

            $merged_property_id = $get_prop_stmt->fetchColumn();

            if (!$merged_property_id) {
                $this->write(
                    sprintf(
                        'Creating new property from the type "%1$s", named "%2$s"!',
                        $merged_prop['type'],
                        $merged_prop['name']
                    )
                );

                //Create a new property:
                $merged_property_id = md5(
                    'RRV2NewMergedProperty'
                  . $merged_prop['name'] . rand()
                );

                $this->write(
                    'INFO: The property-ID of the new property "'
                  . $merged_prop['name'] . '" is: ' . $merged_property_id
                );
                $insert_prop_stmt->execute(
                    [
                        'property_id' => $merged_property_id,
                        'name' => $merged_prop['name'],
                        'description' => (
                            $merged_prop['description']
                            ? $merged_prop['description']
                            : ''
                        ),
                        'type' => $merged_prop['type'],
                        'options' => (
                            $merged_prop['options']
                            ? $merged_prop['options']
                            : ''
                        ),
                        'info_label' => 0,
                        'display_name' => (
                            $merged_prop['display_name']
                            ? $merged_prop['display_name']
                            : $merged_prop['name']
                        ),
                        'searchable' => ($merged_prop['searchable'] ? '1' : '0'),
                        'range_search' => ($merged_prop['range_search'] ? '1' : '0'),
                        'write_permission_level' => 'admin-global',
                        'system' => 1
                    ]
                );
            }

            //Get the old properties and collect their values:
            $old_values = [];
            $old_property_ids = [];
            foreach ($merged_prop['sources'] as $source_prop) {
                //Get the property whose values shall be merged:
                $get_prop_stmt->execute(
                    [
                        'name' => $source_prop['name'],
                        'type' => $source_prop['type']
                    ]
                );
                $old_prop = $get_prop_stmt->fetch();
                if (!$old_prop) {
                    $this->write(
                        sprintf(
                            'The old property with the name "%1$s" and the type "%2$s" doesn\'t exist!',
                            $source_prop['name'],
                            $source_prop['type']
                        )
                    );
                    //The old property doesn't exist.
                    continue;
                }

                $old_property_ids[] = $old_prop['property_id'];

                //Get all property values and convert them:
                $get_property_values_stmt->execute(
                    [
                        'property_id' => $old_prop['property_id']
                    ]
                );

                $all_values = $get_property_values_stmt->fetchAll();

                foreach ($all_values as $value) {
                    if (!is_array($old_values[$value['resource_id']])) {
                        $old_values[$value['resource_id']] = [];
                    }
                    $old_values[$value['resource_id']][$source_prop['name']] =
                        $value['state'];
                }
            }

            //Merge the old property values for each resource:
            foreach ($old_values as $resource_id => $o_values) {
                $new_value = '';
                if ($merged_prop['value_conversion'] instanceof Closure) {
                    //Use a closure for the value conversion:
                    $new_value = $merged_prop['value_conversion']($o_values);
                } else {
                    //Just append the old values:
                    foreach ($o_values as $old_value) {
                        $new_value .= $old_value;
                    }
                }

                $this->write(
                    sprintf(
                        'Merged property value(s) [%1$s] to new value "%2$s."',
                        implode(', ', $o_values),
                        $new_value
                    )
                );

                //Insert the property value for the new property:
                $insert_new_value_stmt->execute(
                    [
                        'property_id' => $merged_property_id,
                        'resource_id' => $resource_id,
                        'state' => $new_value
                    ]
                );
            }

            //Assign the new property to all categories,
            //resources and resource requests where the old properties
            //are assigned to:
            $get_category_id_stmt->execute(
                ['property_ids' => $old_property_ids]
            );
            $relevant_category_ids = $get_category_id_stmt->fetchAll(
                PDO::FETCH_COLUMN,
                0
            );

            if (!$relevant_category_ids) {
                //The old properties aren't used anywhere.
                //We can just delete them.
                $delete_old_prop_stmt->execute(
                    ['property_ids' => $old_property_ids]
                );
                return;
            }

            foreach ($relevant_category_ids as $category_id) {
                $prop_cat_assign_stmt->execute(
                    [
                        'category_id' => $category_id,
                        'property_id' => $merged_property_id,
                        'requestable' => $merged_prop['requestable'] ? '1' : '0',
                        'protected' => '0',
                        'system' => '1',
                        'form_text' => ''
                    ]
                );
            }

            $this->write('Deleting old merged properties!');

            //We can delete the old properties here:
            $delete_old_prop_stmt->execute(
                [
                    'property_ids' => $old_property_ids
                ]
            );

            //Delete entries from resource requests:
            //Since the values have changed, the converted property values
            //may be inadequate for the resource request and thereby
            //the request property value for the property is deleted.
            $delete_resource_request_property_values_stmt->execute(
                [
                    'property_ids' => $old_property_ids
                ]
            );

            //Delete all resource property values from the old property:
            $delete_resource_property_values_stmt->execute(
                [
                    'property_ids' => $old_property_ids
                ]
            );
        }

        $this->write(
            'Finished merging properties.'
        );
    }


    public function removeObsoleteConfigEntries(PDO $db)
    {
        $entries = [
            'RESOURCES_ALLOW_CREATE_ROOMS',
            'RESOURCES_ALLOW_CREATE_TOP_LEVEL',
            'RESOURCES_ALLOW_DELETE_REQUESTS',
            'RESOURCES_ALLOW_REQUESTABLE_ROOM_REQUESTS',
            'RESOURCES_ALLOW_ROOM_REQUESTS_ALL_ROOMS',
            'RESOURCES_ENABLE_GROUPING',
            'RESOURCES_ENABLE_ORGA_CLASSIFY',
            'RESOURCES_ENABLE_SEM_SCHEDULE',
            'RESOURCES_ENABLE_VIRTUAL_ROOM_GROUPS',
            'RESOURCES_HIDE_PAST_SINGLE_DATES',
            'RESOURCES_INHERITANCE_PERMS',
            'RESOURCES_INHERITANCE_PERMS_ROOMS',
            'RESOURCES_LOCKING_ACTIVE',
            'RESOURCES_ROOM_REQUEST_DEFAULT_ACTION',
            'RESOURCES_SCHEDULE_EXPLAIN_USER_NAME',
            'RESOURCES_SEARCH_ONLY_REQUESTABLE_PROPERTY',
            'RESOURCES_SHOW_ROOM_NOT_BOOKED_HINT',
            'RESOURCES_ENABLE_ORGA_ADMIN_NOTICE'
        ];

        $stmt = $db->prepare(
            'DELETE FROM config WHERE field IN ( :entries );'
        );

        $stmt->execute(['entries' => $entries]);
    }


    public function createPropertyGroups(PDO $db)
    {
        $get_group_id_stmt = $db->prepare(
            "SELECT id FROM resource_property_groups WHERE name = :name"
        );
        $create_group_stmt = $db->prepare(
            "INSERT INTO resource_property_groups
            (`name`, `mkdate`, `chdate`)
            VALUES
            (:name, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())"
        );

        $property_groups = [];

        if ($GLOBALS['RESOURCE_MIGRATION_PROPERTY_GROUP_PROPERTY_NAME_REGEX']) {
            //A regular expression is set to split property names
            //into a group part and a proprty name part.

            $group_name_regex = $GLOBALS['RESOURCE_MIGRATION_PROPERTY_GROUP_PROPERTY_NAME_REGEX'];

            $all_properties = $db->query(
                "SELECT property_id, name FROM resource_property_definitions
                ORDER BY name ASC"
            )->fetchAll();

            foreach ($all_properties as $property) {
                $splitted_name = preg_split($group_name_regex, $property['name'], 2);
                $group_name = trim($splitted_name[0]);
                $new_property_name = trim($splitted_name[1]);

                if ($group_name && $new_property_name) {
                    //A group name could be extracted.
                    //Check if a group with that name already exists:
                    $get_group_id_stmt->execute(
                        [
                            'name' => $group_name
                        ]
                    );
                    $group_id = $get_group_id_stmt->fetchColumn();
                    if (!$group_id) {
                        //Create a new group:
                        $create_group_stmt->execute(
                            [
                                'name' => $group_name
                            ]
                        );
                        //Get the ID of the group:
                        $get_group_id_stmt->execute(['name' => $group_name]);
                        $group_id = $get_group_id_stmt->fetchColumn();

                        if (!$group_id) {
                            //No such group. We can only move to the next property.
                            echo "no group-id!\n";
                            continue;
                        }
                    }

                    //Add the property to the list of properties
                    //to be added to the group.
                    if (!is_array($property_groups[$group_id])) {
                        $property_groups[$group_id] = [];
                    }
                    $property_groups[$group_id][] = [
                        'property_id' => $property['property_id'],
                        'new_property_name' => $new_property_name
                    ];
                }
            }

            $update_property_stmt = $db->prepare(
                "UPDATE resource_property_definitions
                SET property_group_id = :group_id
                WHERE property_id = :property_id"
            );

            $update_property_with_name_stmt = $db->prepare(
                "UPDATE resource_property_definitions
                SET property_group_id = :group_id,
                name = :new_name,
                    display_name = :new_name
                WHERE property_id = :property_id"
            );

            foreach ($property_groups as $group_id => $property_list) {
                foreach ($property_list as $property_data) {
                    if ($property_data['new_property_name']) {
                        $update_property_with_name_stmt->execute(
                            [
                                'group_id' => $group_id,
                                'new_name' => $property_data['new_property_name'],
                                'property_id' => $property_data['property_id']
                            ]
                        );
                    } else {
                        $update_property_stmt->execute(
                            [
                                'group_id' => $group_id,
                                'property_id' => $property_data['property_id']
                            ]
                        );
                    }
                }
            }
        }

        //At this point, some property groups may already have been created
        //using the property group regex. But they can also be created
        //by explicitly defining a name and the properties that are included
        //in the group.
        if (count($GLOBALS['RESOURCE_MIGRATION_NEW_PROPERTY_GROUP_LIST'])) {
            $property_group_list = $GLOBALS['RESOURCE_MIGRATION_NEW_PROPERTY_GROUP_LIST'];

            $add_properties_stmt = $db->prepare(
                "UPDATE resource_property_definitions
                SET property_group_id = :group_id
                WHERE name IN ( :names )"
            );

            foreach ($property_group_list as $group_name => $property_names) {
                $get_group_id_stmt->execute(['name' => $group_name]);
                $group_id = $get_group_id_stmt->fetchColumn();

                if ($group_id) {
                    $add_properties_stmt->execute(
                        [
                            'group_id' => $group_id,
                            'names' => $property_names
                        ]
                    );
                } else {
                    //Create a group with the specified group name first:
                    $create_group_stmt->execute(['name' => $group_name]);

                    $get_group_id_stmt->execute(['name' => $group_name]);
                    $group_id = $get_group_id_stmt->fetchColumn();

                    if (!$group_id) {
                        //There is nothing we can do about it here.
                        continue;
                    }

                    $add_properties_stmt->execute(
                        [
                            'group_id' => $group_id,
                            'names' => $property_names
                        ]
                    );
                }
            }
        }
    }


    public function createMissingLocation(PDO $db)
    {
        //Check if there is a location category:
        $location_category_id = $db->query(
            "SELECT id FROM resource_categories
            WHERE class_name = 'Location'
            ORDER BY name LIMIT 1"
        )->fetchAll(PDO::FETCH_COLUMN, 0);

        $location_resource_id = null;
        if (!$location_category_id) {
            //Create a location category:
            $location_category_id = md5('LocationCategory_' . uniqid());

            $create_category_stmt = $db->prepare(
                "INSERT INTO resource_categories
                (`id`, `name`, `system`, `class_name`, `mkdate`, `chdate`)
                VALUES
                (:id, :name, '1', 'Location', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());"
            );

            $create_category_stmt->execute(
                [
                    'id' => $location_category_id,
                    'name' => _('Standort')
                ]
            );
        }

        //Check if all buildings have a location resource
        //in their "parent chain":
        $building_ids = [];
        $get_buildings_stmt = $db->prepare(
            "SELECT resources.id FROM resources
            INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE rc.class_name = 'Building'"
        );
        $get_buildings_stmt->execute();
        $building_ids = $get_buildings_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $get_parent_stmt = $db->prepare(
            "SELECT r1.parent_id as parent_id, rc.class_name as class_name
            FROM resources r1
            LEFT JOIN resources r2 ON r2.id=r1.parent_id
            INNER JOIN resource_categories rc ON r2.category_id = rc.id
            WHERE r1.id = :resource_id"
        );

        $orphaned_top_level_resource_ids = [];
        foreach ($building_ids as $building_id) {
            //Traverse the resource tree upwards until
            //a location resource is found or a resource
            //with no parent is reached.
            $building_location_id = null;

            $last_id = null;
            $current_id = $building_id;
            while($current_id && !$building_location_id) {
                $get_parent_stmt->execute(
                    [
                        'resource_id' => $current_id
                    ]
                );

                $data = $get_parent_stmt->fetchOne();
                if ($data['class_name'] == 'Location') {
                    //We have found the location.
                    $building_location_id = $data['parent_id'];
                    $last_id = null;
                } else {
                    //No location found. Go one layer up:
                    $last_id = $current_id;
                    $current_id = $data['parent_id'];
                }
            }

            //At this point, we have either found a location resource
            //or a top level resource that is not a location but a
            //parent resource of a building.
            if (!$building_location_id && $last_id) {
                $orphaned_top_level_resource_ids[] = $last_id;
            }
        }

        $location_id = $db->query(
            "SELECT resources.id FROM resources
            INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE rc.class_name = 'Location'
            ORDER BY resources.name LIMIT 1"
        )->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!$location_id) {
            //No location exists. In that case, we have to
            //create a location resource.

            $location_name = Config::get()->UNI_NAME_CLEAN;
            if (!$location_name) {
                $location_name = _('Standort');
            }

            $create_location_stmt = $db->prepare(
                "INSERT INTO resources
                (`id`, `parent_id`, `category_id`, `name`, `requestable`,
                 `mkdate`, `chdate`)
                VALUES
                (:id, '', :category_id, :name, '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());"
            );

            $location_id = md5('Location_' . $location_name . uniqid());
            $create_location_stmt->execute(
                [
                    'id' => $location_id,
                    'category_id' => $location_category_id,
                    'name' => $location_name
                ]
            );
        }

        $assign_to_location_stmt = $db->prepare(
            "UPDATE resources SET parent_id = :parent_id WHERE id = :id;"
        );

        foreach ($orphaned_top_level_resource_ids as $resource_id) {
            $assign_to_location_stmt->execute(
                [
                    'parent_id' => $location_id,
                    'id' => $resource_id
                ]
            );
        }
    }


    public function fillResourceBookingIntervals(PDO $db)
    {
        $chunk_size = 100;

        //Delete everything from resource booking intervals first:
        $db->exec('DELETE FROM resource_booking_intervals;');

        $query = "SELECT COUNT(*)
                  FROM resource_bookings";
        $booking_count = $db->query($query)->fetchColumn();

        for ($loop = 0; $loop < $booking_count; $loop += $chunk_size) {
            $query = "SELECT id, resource_id, range_id, begin, end, booking_type
                             preparation_time, repeat_end, repeat_quantity, repetition_interval
                      FROM resource_bookings
                      LIMIT {$loop}, {$chunk_size}";
            $bookings = $db->query($query);

            $add_interval_stmt = $db->prepare(
                "INSERT INTO resource_booking_intervals
                (interval_id, booking_id, resource_id, begin, end, takes_place,
                mkdate, chdate)
                VALUES
                (:interval_id, :booking_id, :resource_id, :begin, :end, 1,
                UNIX_TIMESTAMP(), UNIX_TIMESTAMP());"
            );

            $bookings->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($bookings as $booking) {
                //Calculate the time intervals the same way as in
                //ResourceBooking::calculateTimeIntervals:
                $add_interval = function ($begin, $end) use ($booking, $add_interval_stmt) {
                    $interval_id = md5(
                        'ResourceBookingInterval' .
                        $booking['booking_id'] .
                        $booking['begin'] .
                        $booking['end'] .
                        uniqid()
                    );
                    $add_interval_stmt->execute([
                        ':interval_id' => $interval_id,
                        ':booking_id'  => $booking['id'],
                        ':resource_id' => $booking['resource_id'],
                        ':begin'       => $begin,
                        ':end'         => $end,
                    ]);
                };

                $booking_begin = new DateTime();
                $booking_begin->setTimestamp($booking['begin']);
                if ($booking['preparation_time']) {
                    $booking_begin->setTimestamp(
                        $booking['begin'] - $booking['preparation_time']
                    );
                }
                $booking_end = new DateTime();
                $booking_end->setTimestamp($booking['end']);

                //use begin and end to create the first interval:
                $add_interval(
                    $booking_begin->getTimestamp(),
                    $booking_end->getTimestamp()
                );

                if (($booking['repeat_quantity'] > 0) || $booking['repeat_end']) {
                    //Repetition: we must check which repetition interval has been
                    //selected and then create entries for each repetition.
                    //Repetition starts with the begin date and ends with the
                    //"repeat_end" date.

                    $repetition_end = new DateTime();
                    $repetition_end->setTimestamp($booking['repeat_end']);
                    //The DateInterval constructor will throw an exception,
                    //if it cannot parse the string stored in $this->repetition_interval.
                    $repetition_interval = null;
                    if ($booking['repetition_interval']) {
                        try {
                            $repetition_interval = new DateInterval($booking['repetition_interval']);
                        } catch (Exception $e) {
                            //Invalid repetition interval string.
                            //Skip this booking since its repetition interval is invalid.
                            continue;
                        }
                    }

                    if ($repetition_interval instanceof DateInterval) {
                        $duration = $booking_begin->diff($booking_end);

                        //Check if end is later than begin to avoid
                        //infinite loops.
                        if ($repetition_end > $booking_begin) {
                            $current_begin = clone $booking_begin;
                            $current_begin->add($repetition_interval);
                            while ($current_begin < $repetition_end) {
                                $current_end = clone $current_begin;
                                $current_end->add($duration);

                                $add_interval(
                                    $current_begin->getTimestamp(),
                                    $current_end->getTimestamp()
                                );

                                $current_begin->add($repetition_interval);
                            }
                        }
                    }
                }
            }
        }
    }


    public function up()
    {
        //Load the special configuration first:
        if (file_exists(__DIR__ . '/../../config/resource_migration.php')) {
            require(__DIR__ . '/../../config/resource_migration.php');
        } else {
            //The special configuration doesn't exist so that we have to load
            //the default configuration for the demo data.
            $this->loadDefaultConfiguration();
        }
        $db = DBManager::get();

        //add new configuration variables:

        $db->exec(
            "INSERT IGNORE INTO config
            (
                `field`,
                `value`,
                `type`,
                `range`,
                `section`,
                `mkdate`,
                `chdate`,
                `description`
            )
            VALUES
            (
                'RESOURCES_DIRECT_ROOM_REQUESTS_ONLY',
                '0',
                'boolean',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'Restricts room requests so that only specific rooms can be requested.'
            ),
            (
                'RESOURCES_MAP_SERVICE_URL',
                'https://www.openstreetmap.org/#map=19/LATITUDE/LONGITUDE',
                'string',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The URL for a map service if you wish to use another service instead of OpenStreetMap. The default is: https://www.openstreetmap.org/#map=17/LATITUDE/LONGITUDE (LATITUDE and LONGITUDE are placeholders!)'
            ),
            (
                'RESOURCES_MAX_PREPARATION_TIME',
                '120',
                'integer',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The maximum amount of time that can be used for preparation before the actual booking begins. The value represents minutes, not hours!'
            ),
            (
                'RESOURCES_MIN_BOOKING_TIME',
                '15',
                'integer',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The minimum amount of minutes for the booking of a resource.'
            ),
            (
                'RESOURCES_BOOKING_PLAN_START_HOUR',
                '08:00',
                'string',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The start hour for the default view of the booking plan.'
            ),
            (
                'RESOURCES_BOOKING_PLAN_END_HOUR',
                '20:00',
                'string',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The start hour for the default view of the booking plan.'
            ),
            (
                'RESOURCES_MIN_BOOKING_PERMS',
                'autor',
                'string',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The minimum permission level for global booking rights on a resource.'
            ),
            (
                'RESOURCES_MIN_REQUEST_PERMISSION',
                '',
                'string',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'The minimum permission level for creating \"free\" requests that are not bound to a course.'
            ),
            (
                'RESOURCES_DISPLAY_CURRENT_REQUESTS_IN_OVERVIEW',
                '1',
                'boolean',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'Whether to display the list with current requests in the room management overview (true) or not (false).'
            ),
            (
                'RESOURCES_SHOW_PUBLIC_ROOM_PLANS',
                '0',
                'boolean',
                'global',
                'resources',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'Whether to display the list of available public room plans.'
            );"
        );

        //Convert configuration options that may already exist so that
        //the format is identical:

        $db->exec(
            "UPDATE config SET type = 'boolean', section = 'resources'
            WHERE field = 'RESOURCES_DIRECT_ROOM_REQUESTS_ONLY';"
        );

        //Enable API routes (and the API itself).
        //The new room and resource management system requires a few routes
        //to be activated to work properly.

        $db->exec(
            "UPDATE config SET value = '1'
            WHERE field = 'API_ENABLED';"
        );

        $enable_api_route_stmt = $db->prepare(
            "INSERT INTO api_consumer_permissions
            (route_id, consumer_id, method, granted)
            VALUES
            (:route, 'global', :method, '1')
            ON DUPLICATE KEY UPDATE
            granted = '1';"
        );
        $api_routes_to_enable = [
            '/user/:user_id' => 'get',
            '/resources/booking_interval/:interval_id/toggle_takes_place' => 'post',
            '/resources/booking/:booking_id/move' => 'post',
            '/resources/booking/:booking_id/intervals' => 'get',
            '/resources/resource/:resource_id/booking_plan' => 'get',
            '/resources/resource/:resource_id/semester_plan' => 'get',
            '/resources/request/:request_id/move' => 'post',
            '/resources/request/:request_id/edit_reply_comment' => 'post',
            '/resources/request/:request_id/toggle_marked' => 'post',
            '/room_clipboard/:clipboard_id/booking_plan' => 'get',
            '/room_clipboard/:clipboard_id/semester_plan' => 'get',
            '/clipboard/:clipboard_id' => ['delete', 'put'],
            '/clipboard/:clipboard_id/item' => 'post',
            '/clipboard/:clipboard_id/item/:range_id' => 'delete',
            '/clipboard/add' => 'post',
            '/course/:course_id/members' => 'get',
            '/semesters' => 'get'
        ];

        foreach ($api_routes_to_enable as $route => $methods) {
            if (is_array($methods)) {
                foreach ($methods as $method) {
                    $enable_api_route_stmt->execute(
                        [
                            'route' => md5($route),
                            'method' => $method
                        ]
                    );
                }
            } else {
                $enable_api_route_stmt->execute(
                    [
                        'route' => md5($route),
                        'method' => $methods
                    ]
                );
            }
        }


        //add new tables:

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `resource_temporary_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `begin` int(11) unsigned NOT NULL DEFAULT '0',
  `end` int(11) unsigned NOT NULL DEFAULT '0',
  `perms` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `resource_request_appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `appointment_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `colour_values` (
  `colour_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `description` varchar(256) NOT NULL DEFAULT '',
  `value` varchar(8) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'ffffffff',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`colour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `separable_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `building_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(256) NOT NULL DEFAULT '',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `separable_room_parts` (
  `separable_room_id` int(10) NOT NULL,
  `room_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`separable_room_id`,`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `clipboards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(256) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `handler` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Clipboard',
  `allowed_item_class` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'StudipItem',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        //The range_id in clipboard_items is extra large
        //so that primary keys consisting of three other
        //keys can also be used.
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `clipboard_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clipboard_id` int(11) NOT NULL,
  `range_id` varchar(98) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'SimpleORMap',
  `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
  `chdate` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        //The property group table defines groups of resource properties
        //that shall be displayed together.
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `resource_property_groups` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `position` TINYINT(4) NOT NULL DEFAULT '0',
                `mkdate` INT(11) NOT NULL DEFAULT '0',
                `chdate` INT(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
        );

        if ($GLOBALS['RESOURCE_MIGRATION_SPECIALRESOURCESPLUGIN']) {
            $db->exec(
                "CREATE TABLE IF NOT EXISTS specialresourcesplugin_course_resources(
                    course_id VARCHAR(32) NOT NULL,
                    resource_id VARCHAR(32) NOT NULL,
                    mkdate INT(10) NOT NULL DEFAULT '0',
                    chdate INT(10) NOT NULL DEFAULT '0',
                    PRIMARY KEY (course_id, resource_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC"
            );

        }

        //Add colors:
        $colours = [
            'Resources.BookingPlan.Booking.Fg' => [
                'ffffffff',
                'Die Textfarbe im Belegungsplan für gewöhnliche Buchungen.'
            ],
            'Resources.BookingPlan.Booking.Bg' => [
                '129c94ff',
                'Die Farbe im Belegungsplan für gewöhnliche Buchungen.'
            ],
            'Resources.BookingPlan.CourseBooking.Fg' => [
                'ffffffff',
                'Die Textfarbe im Belegungsplan für veranstaltungsbezogene Buchungen.'
            ],
            'Resources.BookingPlan.CourseBooking.Bg' => [
                '682c8bff',
                'Die Farbe im Belegungsplan für veranstaltungsbezogene Buchungen.'
            ],
            'Resources.BookingPlan.CourseBookingWithExceptions.Fg' => [
                'ffffffff',
                'Die Textfarbe im Belegungsplan für veranstaltungsbezogene Buchungen mit Ausfallterminen.'
            ],
            'Resources.BookingPlan.CourseBookingWithExceptions.Bg' => [
                'a480b9ff',
                'Die Farbe im Belegungsplan für veranstaltungsbezogene Buchungen mit Ausfallterminen.'
            ],
            'Resources.BookingPlan.SimpleBookingWithExceptions.Fg' => [
                'ffffffff',
                'Die Textfarbe im Belegungsplan für einfache Buchungen mit Wiederholungen, bei denen es Ausfalltermine gibt.'
            ],
            'Resources.BookingPlan.SimpleBookingWithExceptions.Bg' => [
                '70c3bfff',
                'Die Farbe im Belegungsplan für einfache Buchungen mit Wiederholungen, bei denen es Ausfalltermine gibt.'
            ],
            'Resources.BookingPlan.Reservation.Fg' => [
                'ffffffff',
                'Die Textfarbe im Belegungsplan für Reservierungen.'
            ],
            'Resources.BookingPlan.Reservation.Bg' => [
                '6ead10ff',
                'Die Farbe im Belegungsplan für Reservierungen.'
            ],
            'Resources.BookingPlan.Lock.Fg' => [
                'ffffffff',
                'Die Textfarbe im Belegungsplan für Sperrbuchungen.'
            ],
            'Resources.BookingPlan.Lock.Bg' => [
                'd60000ff',
                'Die Farbe im Belegungsplan für Sperrbuchungen.'
            ],
            'Resources.BookingPlan.PlannedBooking.Fg' => [
                '000000ff',
                'Die Textfarbe im Belegungsplan für geplante Buchungen.'
            ],
            'Resources.BookingPlan.PlannedBooking.Bg' => [
                'f26e00ff',
                'Die Farbe im Belegungsplan für geplante Buchungen.'
            ],
            'Resources.BookingPlan.PreparationTime.Fg' => [
                '000000ff',
                'Die Textfarbe im Belegungsplan für Rüstzeiten.'
            ],
            'Resources.BookingPlan.PreparationTime.Bg' => [
                'cf81b0ff',
                'Die Farbe im Belegungsplan für Rüstzeiten.'
            ],
            'Resources.BookingPlan.Request.Fg' => [
                '000000ff',
                'Die Textfarbe im Belegungsplan für Anfragen.'
            ],
            'Resources.BookingPlan.Request.Bg' => [
                'ffbd33ff',
                'Die Farbe im Belegungsplan für Anfragen.'
            ]
        ];

        $colour_stmt = $db->prepare(
            'INSERT IGNORE INTO colour_values
            (colour_id, value, description, mkdate, chdate)
            VALUES
            (:colour_id, :value, :description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
            );'
        );

        foreach ($colours as $colour_id => $data) {
            $colour_stmt->execute(
                [
                    'colour_id' => $colour_id,
                    'value' => $data[0],
                    'description' => $data[1]
                ]
            );
        }

        //alter tables:

        $db->exec("RENAME TABLE resources_objects TO resources;");
        $db->exec("ALTER TABLE resources
            CHANGE COLUMN resource_id id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN parent_id parent_id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN category_id category_id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN description description text NULL DEFAULT NULL,
            CHANGE COLUMN requestable requestable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN sort_position TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            DROP COLUMN institut_id,
            DROP COLUMN lockable,
            DROP COLUMN multiple_assign,
            DROP COLUMN root_id");

        $db->exec("RENAME TABLE resources_categories TO resource_categories;");
        $db->exec("ALTER TABLE resource_categories
            CHANGE COLUMN category_id id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN `system` `system` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN class_name VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Resource',
            ADD COLUMN mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            DROP COLUMN is_room");

        $db->exec("RENAME TABLE resources_categories_properties
            TO resource_category_properties;");
        $db->exec("ALTER TABLE resource_category_properties
            CHANGE COLUMN requestable requestable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN protected protected TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN `system` `system` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN form_text text NULL,
            ADD COLUMN mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) UNSIGNED NOT NULL DEFAULT 0");

        $db->exec("RENAME TABLE resources_properties
            TO resource_property_definitions;");
        $db->exec("ALTER TABLE resource_property_definitions
            CHANGE COLUMN type type SET (
                'bool', 'text', 'num', 'select', 'user', 'institute',
                'position', 'fileref', 'url', 'resource_ref_list'
                ) CHARACTER SET latin1 COLLATE latin1_bin,
            CHANGE COLUMN description description text NULL DEFAULT NULL,
            CHANGE COLUMN `system` `system` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN display_name VARCHAR(512) NOT NULL DEFAULT '',
            ADD COLUMN searchable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN range_search TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN write_permission_level VARCHAR(16) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'admin-global',
            ADD COLUMN property_group_id INT(11) NULL,
            ADD COLUMN property_group_pos TINYINT(4) NULL,
            ADD COLUMN mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) UNSIGNED NOT NULL DEFAULT 0");

        //Set the display_name to the property name as default:

        $db->exec(
            "UPDATE resource_property_definitions
            SET display_name = name;"
        );

        $db->exec("RENAME TABLE resources_objects_properties
            TO resource_properties;");
        $db->exec("ALTER TABLE resource_properties
            ADD COLUMN mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) UNSIGNED NOT NULL DEFAULT 0");

        $db->exec("RENAME TABLE resources_requests
            TO resource_requests;");
        $db->exec("ALTER TABLE resource_requests
            CHANGE COLUMN request_id id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN seminar_id course_id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN closed closed TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN category_id category_id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT '',
            ADD COLUMN begin INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN end INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN preparation_time INT(4) NOT NULL DEFAULT 0,
            ADD COLUMN marked TINYINT(1) UNSIGNED NOT NULL DEFAULT 0"
        );

        $db->exec("RENAME TABLE resources_requests_properties
            TO resource_request_properties;");

        $db->exec("RENAME TABLE resources_assign TO resource_bookings;");
        $db->exec("ALTER TABLE resource_bookings
            CHANGE COLUMN assign_id id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN user_free_name description TEXT NULL,
            CHANGE COLUMN assign_user_id range_id CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN repeat_interval old_rep_interval INT(2) NULL DEFAULT NULL,
            CHANGE COLUMN repeat_month_of_year old_rep_month_of_year INT(2) NULL DEFAULT NULL,
            CHANGE COLUMN repeat_day_of_month old_rep_day_of_month INT(2) NULL DEFAULT NULL,
            CHANGE COLUMN repeat_week_of_month old_rep_week_of_month INT(2) NULL DEFAULT NULL,
            CHANGE COLUMN repeat_day_of_week old_rep_day_of_week INT(2) NULL DEFAULT NULL,
            CHANGE COLUMN comment_internal internal_comment text NULL DEFAULT NULL,
            ADD COLUMN preparation_time INT(4) NOT NULL DEFAULT 0,
            ADD COLUMN booking_type TINYINT(2) NOT NULL DEFAULT 0,
            ADD COLUMN booking_user_id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            ADD COLUMN repetition_interval VARCHAR(24) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '';"
        );
        $db->exec("ALTER TABLE `resource_bookings` DROP INDEX `resource_id`, ADD INDEX `resource_id` (`resource_id`, `booking_type`)");

        $db->exec("RENAME TABLE resources_locks
            TO global_resource_locks;");
        $db->exec("ALTER TABLE global_resource_locks
            CHANGE COLUMN lock_begin begin INT(11) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN lock_end end INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN user_id VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            ADD COLUMN mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) UNSIGNED NOT NULL DEFAULT 0");

        $db->exec("RENAME TABLE resources_user_resources TO resource_permissions;");
        $db->exec("ALTER TABLE resource_permissions
            ADD COLUMN mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) UNSIGNED NOT NULL DEFAULT 0");
        $db->exec("ALTER TABLE resource_permissions ADD INDEX (resource_id)");
        $db->exec("UPDATE resource_permissions SET resource_id = 'global'
            WHERE resource_id = 'all';");

        $db->exec("DROP TABLE resources_temporary_events;");
        $db->exec(
            "CREATE TABLE `resource_booking_intervals` (
            `interval_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `booking_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `begin` int(20) NOT NULL DEFAULT 0,
            `end` int(20) NOT NULL DEFAULT 0,
            `mkdate` int(11) unsigned NOT NULL DEFAULT 0,
            `chdate` int(11) unsigned NOT NULL DEFAULT 0,
            `takes_place` tinyint(1) unsigned NOT NULL DEFAULT 1,
            PRIMARY KEY (`interval_id`),
            INDEX `resource_id` (`resource_id`,`takes_place`,`end`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");

        //Delete old tables:

        $db->exec("DROP TABLE resources_requests_user_status;");

        //Add other nice things and modify stuff:

        $new_log_action_stmt = $db->prepare(
            "INSERT IGNORE INTO log_actions (action_id, name, description)
            VALUES
            (:action_id, :name, :description);"
        );

        $action_id = md5(uniqid('RES_PERM_CHANGE'));
        $new_log_action_stmt->execute(
            [
                'action_id' => $action_id,
                'name' => 'RES_PERM_CHANGE',
                'description' => 'Änderung der Berechtigungsstufe an einer Ressource.'
            ]
        );

        //Set parent_id = '' where parent_id = 'root' or '0' in
        //resources so that root resources always
        //have an empty parent_id field:
        $db->exec("UPDATE resources
            SET parent_id = ''
            WHERE parent_id = 'root' OR parent_id = '0';"
        );

        //We can delete resources now that are not needed anymore:
        $this->deleteResources();

        //First we remove those properties that are not needed anymore:
        $this->deleteExistingProperties($db);

        //Now we have to migrate existing properties
        //that are system properties with a different name:
        $this->migrateExistingProperties($db);
        //And we have to merge some properties, too:
        $this->mergeProperties($db);

        //Now we convert all boolean property values to '0' and '1'
        //instead of '' and 'on':
        $db->exec(
            "UPDATE resource_properties
            INNER JOIN resource_property_definitions rpd
            USING (property_id)
            SET state = '1'
            WHERE state = 'on' AND rpd.type = 'bool';"
        );
        $db->exec(
            "UPDATE resource_properties
            INNER JOIN resource_property_definitions rpd
            USING (property_id)
            SET state = '0'
            WHERE state = '' AND rpd.type = 'bool';"
        );

        //Repeat the same for resource request properties:

        $db->exec(
            "UPDATE resource_request_properties
            INNER JOIN resource_property_definitions rpd
            USING (property_id)
            SET state = '1'
            WHERE state = 'on' AND rpd.type = 'bool';"
        );
        $db->exec(
            "UPDATE resource_request_properties
            INNER JOIN resource_property_definitions rpd
            USING (property_id)
            SET state = '0'
            WHERE state = '' AND rpd.type = 'bool';"
        );

        //rename and update resource categories:

        $db->exec("UPDATE resource_categories
            SET class_name = 'Room', `system` = 1
            WHERE
            name LIKE '%raum%' OR name LIKE '%Raum%'
            OR name LIKE '%saal%' OR name LIKE '%Saal%';");

        $db->exec("UPDATE resource_categories
            SET class_name = 'Building', `system` = 1
            WHERE
            name LIKE '%gebäude%' OR name LIKE '%Gebäude%';");

        //add new resource categories and new properties:

        $add_category_statement = $db->prepare(
            "INSERT INTO `resource_categories`
            (`id`,`name`, `class_name`, `description`, `system`, `iconnr`)
            VALUES
            (:id, :name, :class_name, '', 1, 0);"
        );

        $location_cat_id = md5('StandortLocationResourceCategory100');

        $building_cat_id_rows = $db->query("SELECT id
            FROM resource_categories
            WHERE name LIKE '%gebäude%';")->fetchAll();

        $room_cat_id_rows = $db->query("SELECT id
            FROM resource_categories
            WHERE name LIKE '%raum%' OR name LIKE '%saal%';")->fetchAll();

        $building_cat_ids = [];
        foreach ($building_cat_id_rows as $row) {
            $building_cat_ids[] = $row[0];
        }

        $room_cat_ids = [];
        foreach ($room_cat_id_rows as $row) {
            $room_cat_ids[] = $row[0];
        }

        $add_category_statement->execute(
            [
                'id' => $location_cat_id,
                'name' => 'Standort',
                'class_name' => 'Location'
            ]
        );

        //Assign orphaned resources (resources without category-id):
        $this->assignOrphanedResources($db);
        $this->deleteUnfinishedResources($db);

        if (is_array($GLOBALS['RESOURCE_CATEGORY_CLASS_MAPPING'])) {
            //Use the class mapping rules from that configuration setting:
            $mapping_stmt = $db->prepare(
                'UPDATE resource_categories
                SET class_name = :class_name
                WHERE name = :name'
            );
            foreach ($GLOBALS['RESOURCE_CATEGORY_CLASS_MAPPING'] as $name => $class_name) {
                $mapping_stmt->execute(
                    [
                        'class_name' => $class_name,
                        'name' => $name
                    ]
                );
            }
        } else {
            //Migrate rooms, buildings and locations, based on best guess.
            $this->migrateRooms($db);
            $this->migrateBuildings($db);
            $this->migrateLocations($db, $location_cat_id);
        }

        if (count($GLOBALS['RESOURCE_CATEGORY_RENAME'])) {
            $rename_stmt = $db->prepare(
                'UPDATE resource_categories SET name = :name
                WHERE name = :old_name'
            );

            foreach ($GLOBALS['RESOURCE_CATEGORY_RENAME'] as $old_name => $name) {
                $rename_stmt->execute(
                    [
                        'old_name' => $old_name,
                        'name' => $name
                    ]
                );
            }
        }

        //Get all location categories.
        //migrateLocations has set the Location class name
        //for all location categories.
        $location_cat_id_rows = $db->query(
            "SELECT id
            FROM resource_categories
            WHERE class_name = 'Location';"
        )->fetchAll();
        $location_cat_ids = [];
        foreach ($location_cat_id_rows as $row) {
            $location_cat_ids[] = $row[0];
        }

        //Add or create missing default properties:

        $property_exists_statement = $db->prepare(
            "SELECT property_id
            FROM resource_property_definitions
            WHERE
            name = :name AND type = :type;"
        );

        $add_property_statement = $db->prepare(
            "INSERT INTO `resource_property_definitions`
            (`property_id`, `name`, `type`, `options`, `system`)
            VALUES
            (:id, :name, :type, '', 1);"
        );

        $property_link_exists_statement = $db->prepare(
            "SELECT 1
            FROM resource_category_properties
            WHERE
            category_id = :category_id AND property_id = :property_id;"
        );

        $update_property_link_statement = $db->prepare(
            "UPDATE resource_category_properties
            SET `system` = 1
            WHERE
            category_id = :category_id AND property_id = :property_id;"
        );

        $link_property_statement = $db->prepare(
            "INSERT INTO resource_category_properties
            (`category_id`, `property_id`, `system`)
            VALUES
            (:category_id, :property_id, 1);"
        );

        $all_mandatory_properties = [
            'location' => [
                //location properties:
                'geo_coordinates' => 'position'
            ],

            'building' => [
                //building properties:
                'geo_coordinates' => 'position',
                'number' => 'text',
                'address' => 'text'
            ],

            'room' => [
                //room properties:
                'booking_plan_is_public' => 'bool',
                'room_type' => 'select',
                'seats' => 'num',
                'responsible_person' => 'user'
            ]
        ];

        foreach ($all_mandatory_properties as $area_name => $area) {
            $area_ids = [''];
            if ($area_name == 'location') {
                $area_ids = $location_cat_ids;
            } elseif ($area_name == 'building') {
                $area_ids = $building_cat_ids;
            } elseif ($area_name == 'room') {
                $area_ids = $room_cat_ids;
            }
            foreach ($area as $name => $type) {
                //Check if the property exists. If so, link it only.
                //Otherwise create it.
                $property_exists_statement->execute(
                    [
                        'name' => $name,
                        'type' => $type
                    ]
                );

                $property_id = $property_exists_statement->fetchColumn(0);
                if (!$property_id) {
                    //property doesn't exist: create it
                    $property_id = md5($name . $type . rand());
                    $add_property_statement->execute(
                        [
                            'id' => $property_id,
                            'name' => $name,
                            'type' => $type
                        ]
                    );
                }

                foreach ($area_ids as $area_id) {
                    //Check if the property is linked to the category.
                    //If so, we must make sure the system attribute
                    //is set. Otherwise we must create the link.
                    $property_link_exists_statement->execute(
                        [
                            'category_id' => $area_id,
                            'property_id' => $property_id
                        ]
                    );

                    $link_exists = $property_link_exists_statement->fetchColumn(0);

                    if ($link_exists) {
                        $update_property_link_statement->execute(
                            [
                                'category_id' => $area_id,
                                'property_id' => $property_id
                            ]
                        );
                    } else {
                        $link_property_statement->execute(
                            [
                                'category_id' => $area_id,
                                'property_id' => $property_id
                            ]
                        );
                    }
                    //make plans visible
                    if ($area_name === 'room' && $name === 'booking_plan_is_public') {
                        $db->execute("INSERT IGNORE INTO `resource_properties` (`resource_id`, `property_id`, `state`, `mkdate`, `chdate`) SELECT `id` , ?, '1', UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM `resources` WHERE `category_id` = ?", [$property_id, $area_id]);
                    }
                }
            }
        }

        //At this point, we can create the property groups (if any):
        $this->createPropertyGroups($db);

        //Migrate resource booking interval data:
        $this->migrateBookingRepeatIntervals($db);

        $this->migrateOwner();
        //Migrate course permissions on resources:
        $this->migrateCourseBoundPermissions($db);
        $this->deleteOldPermissions($db);

        //In case no location resource is in the database,
        //one such resource will be created along with the
        //corresponding category.
        $this->createMissingLocation($db);

        //Create entries in resource_booking_intervals for each booking:
        $this->fillResourceBookingIntervals($db);

        SimpleORMap::expireTableScheme();
    }


    public function down()
    {
        //I see nothing! I hear nothing! Nothing!!
    }
}
