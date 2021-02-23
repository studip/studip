<?php

/**
 * ResourceCategory.class.php - model class for resource categories
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.1
 *
 * @property string id database column
 * @property string name database column
 * @property string description database column
 * @property string class_name database column: The name of the SORM class
 *     that handles the resource object, defaults to Resource.
 * @property string system database column
 * @property string iconnr database column
 * @property string mkdate database column
 * @property string chdate database column
 */


/**
 * The ResourceCategory class can be used as a Factory for
 * Resource objects.
 */
class ResourceCategory extends SimpleORMap
{
    private static $cache;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_categories';

        $config['has_and_belongs_to_many']['property_definitions'] = [
            'class_name'        => 'ResourcePropertyDefinition',
            'assoc_foreign_key' => 'property_id',
            'thru_table'        => 'resource_category_properties',
            'thru_key'          => 'category_id',
            'order_by'          => 'ORDER BY name ASC'
        ];

        $config['has_many']['property_links'] = [
            'class_name'  => 'ResourceCategoryProperty',
            'assoc_func'  => 'findByCategory_id',
            'foreign_key' => 'id',
            'on_delete'   => 'delete'
        ];

        $config['registered_callbacks']['after_create'][] = function ($category) {
            self::$cache[$category->id] = $category;
        };
        $config['registered_callbacks']['after_store'][] = function ($category) {
            self::$cache[$category->id] = $category;
        };
        $config['registered_callbacks']['after_delete'][] = function ($category) {
            unset(self::$cache[$category->id]);
        };

        parent::configure($config);
    }

    /**
     * Retrieves all resource categories from the database.
     * @param bool $force_reload
     * @return ResourceCategory[] An array of ResourceCategory objects
     *     or an empty array if no resource categories are defined.
     */
    public static function findAll($force_reload = false)
    {
        if (!is_array(self::$cache) || $force_reload) {
            self::$cache = [];
            foreach (self::findBySql('1 ORDER BY name') as $one) {
                self::$cache[$one->id] = $one;
            }
        }
        return self::$cache;
    }

    public static function find($id)
    {
        $all = self::findAll();
        return $all[$id] ?: null;
    }

    /**
     * "Converts" a category-ID to a class name by looking up the
     * class name of a specified category.
     *
     * @param string $category_id The category-ID of the specified category.
     *
     * @return string The class name field of the category which is specified
     *     by $category_id. In case no category could be found, an empty
     *     string is returned.
     */
    public static function getClassNameById($category_id)
    {
        $category = self::find($category_id);
        if ($category) {
            return $category->class_name;
        }
        return '';
    }

    public function hasResources()
    {
        $db   = DBManager::get();
        $stmt = $db->prepare(
            "SELECT 1 FROM resources WHERE category_id = :category_id LIMIT 1"
        );
        $stmt->execute(['category_id' => $this->id]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Retrieves the definitions of all properties that are available
     * for this resource category.
     *
     * @param string[] excluded_properties An array with the names
     *     of the properties that shall be excluded from the result set.
     *
     * @return ResourcePropertyDefinition[] An array of resource property
     *     definitions.
     */
    public function getPropertyDefinitions($excluded_properties = [])
    {
        if (is_array($excluded_properties) && count($excluded_properties)) {
            return ResourcePropertyDefinition::findBySql(
                "INNER JOIN resource_category_properties rcp
                ON resource_property_definitions.property_id = rcp.property_id
                WHERE
                rcp.category_id = :category_id
                AND resource_property_definitions.name NOT IN (
                    :excluded_properties
                )
                ORDER BY resource_property_definitions.name ASC",
                [
                    'category_id'         => $this->id,
                    'excluded_properties' => $excluded_properties
                ]
            );
        }

        //No excluded properties are specified.
        //We can return all property definitions.
        return ResourcePropertyDefinition::findBySql(
            "INNER JOIN resource_category_properties rcp
            ON resource_property_definitions.property_id = rcp.property_id
            WHERE
            rcp.category_id = :category_id
            ORDER BY resource_property_definitions.name ASC",
            [
                'category_id' => $this->id
            ]
        );
    }

    /**
     * This method returns the same properties as getPropertyDefinitions,
     * but grouped and ordered by the property groups and the position of the
     * property in that group.
     *
     * @return array An array with the group names as keys and the properties
     *     in the second array dimension. The structure of the array
     *     is as follows:
     *     [
     *          group1 name => [
     *              property1,
     *              property2,
     *              ...
     *          ],
     *          group2 name => [
     *              ...
     *          ]
     *     ]
     */
    public function getGroupedPropertyDefinitions($excluded_properties = [])
    {
        if (is_array($excluded_properties) && count($excluded_properties)) {
            $definitions = ResourcePropertyDefinition::findBySql(
                "INNER JOIN resource_category_properties rcp
                ON resource_property_definitions.property_id = rcp.property_id
                LEFT JOIN resource_property_groups rpg
                ON resource_property_definitions.property_group_id = rpg.id
                WHERE
                rcp.category_id = :category_id
                AND resource_property_definitions.name NOT IN (
                    :excluded_properties
                )
                ORDER BY
                type DESC, rpg.position ASC, rpg.name ASC,
                resource_property_definitions.property_group_pos,
                resource_property_definitions.name ASC",
                [
                    'category_id'         => $this->id,
                    'excluded_properties' => $excluded_properties
                ]
            );

            $empty_index          = _('Sonstige');
            $empty_property_group = [
                $empty_index => []
            ];
            $property_groups      = [];
            foreach ($definitions as $definition) {
                if ($definition->group->name) {
                    $group_name = $definition->group->name;
                    if (!is_array($property_groups[$group_name])) {
                        $property_groups[$group_name] = [];
                    }
                    $property_groups[$group_name][] = $definition;
                } else {
                    $empty_property_group[$empty_index][] = $definition;
                }
            }
            if ($empty_property_group[$empty_index]) {
                return array_merge($property_groups, $empty_property_group);
            } else {
                return $property_groups;
            }
        }

        //No excluded properties are specified.
        //We can return all property definitions.
        $definitions = ResourcePropertyDefinition::findBySql(
            "INNER JOIN resource_category_properties rcp
            ON resource_property_definitions.property_id = rcp.property_id
            LEFT JOIN resource_property_groups rpg
            ON resource_property_definitions.property_group_id = rpg.id
            WHERE
            rcp.category_id = :category_id
            ORDER BY
            rpg.position ASC, rpg.name ASC,
            resource_property_definitions.property_group_pos,
            resource_property_definitions.name ASC",
            [
                'category_id' => $this->id
            ]
        );

        $empty_index          = _('Sonstige');
        $empty_property_group = [
            $empty_index => []
        ];
        $property_groups      = [];
        foreach ($definitions as $definition) {
            if ($definition->group->name) {
                $group_name = $definition->group->name;
                if (!is_array($property_groups[$group_name])) {
                    $property_groups[$group_name] = [];
                }
                $property_groups[$group_name][] = $definition;
            } else {
                $empty_property_group[$empty_index][] = $definition;
            }
        }
        if ($empty_property_group[$empty_index]) {
            return array_merge($property_groups, $empty_property_group);
        } else {
            return $property_groups;
        }
    }

    /**
     * Adds a property to this category. If the property doesn't exist
     * it will be created.
     *
     * @param string $name The name of the property.
     * @param string $type The type of the property.
     * @param bool $requestable Whether the property is requestable or not.
     *     Defaults to false.
     * @param bool $protected Whether the property is protected or not.
     *     Defaults to false.
     * @param string $write_permission_level
     * @return ResourceCategoryProperty The created or updated
     *     resource category property.
     * @throws ResourcePropertyDefinitionException If the property definition
     *     cannot be created.
     *
     * @throws ResourcePropertyException If the property cannot be created.
     */
    public function addProperty(
        $name = '',
        $type = 'bool',
        $requestable = false,
        $protected = false,
        $write_permission_level = 'autor'
    )
    {
        if (!$name) {
            throw new ResourcePropertyException(
                _('Es wurde kein Name für die Eigenschaft angegeben!')
            );
        }

        $defined_types = ResourcePropertyDefinition::getDefinedTypes();

        if (!in_array($type, $defined_types)) {
            throw new ResourcePropertyException(
                sprintf(
                    _('Der Eigenschaftstyp %s ist ungültig!'),
                    $type
                )
            );
        }

        if (!in_array($write_permission_level, ['user', 'autor', 'tutor', 'admin'])) {
            throw new ResourcePropertyException(
                sprintf(
                    _('Die Rechtestufe %s ist ungültig!'),
                    $write_permission_level
                )
            );
        }

        $existing_property = ResourceCategoryProperty::findOneBySql(
            'INNER JOIN resource_property_definitions rpd
            ON resource_category_properties.property_id = rpd.property_id
            WHERE
            resource_category_properties.category_id = :category_id
            AND
            rpd.name = :name
            AND
            rpd.type = :type',
            [
                'category_id' => $this->id,
                'name'        => $name,
                'type'        => $type
            ]
        );

        if ($existing_property) {
            $existing_property->requestable = $requestable ? '1' : '0';
            $existing_property->protected   = $protected ? '1' : '0';
            if ($existing_property->isDirty()) {
                if ($existing_property->store()) {
                    return $existing_property;
                } else {
                    throw new ResourcePropertyException(
                        sprintf(
                            _('Fehler beim Aktualisieren der Eigenschaft %1$s (vom Typ %2$s)!'),
                            $name,
                            $type
                        )
                    );
                }
            }
        } else {
            $definition = ResourcePropertyDefinition::findOneBySql(
                'name = :name AND type = :type',
                [
                    'name' => $name,
                    'type' => $type
                ]
            );

            if (!$definition) {
                $definition       = new ResourcePropertyDefinition();
                $definition->name = $name;
                $definition->type = $type;
                if (!$definition->store()) {
                    throw new ResourcePropertyDefinitionException(
                        sprintf(
                            _('Fehler beim Speichern der Definition der Eigenschaft %1$s (vom Typ %2$s)!'),
                            $name,
                            $type
                        )
                    );
                }
            }

            $property              = new ResourceCategoryProperty();
            $property->property_id = $definition->id;
            $property->category_id = $this->id;
            $property->requestable = $requestable ? '1' : '0';
            $property->protected   = $protected ? '1' : '0';

            if ($property->store()) {
                return $property;
            } else {
                throw new ResourcePropertyException(
                    sprintf(
                        _('Fehler beim Speichern der neuen Eigenschaft %1$s (vom Typ %2$s)!'),
                        $name,
                        $type
                    )
                );
            }
        }
    }

    /**
     * Creates a resource object which belongs to this category.
     * All properties which are mandatory for resources of this
     * category are set to default values unless they are specified in the
     * properties array.
     *
     * @param string $name The name of the new resource.
     * @param string $description The description of the new resource.
     * @param string $parent_id The parent resource's ID (if any).
     * @param array $properties An associative array in the form [$key] = $value
     *     containing the defined properties which can be set to this resource.
     * @param bool $ignore_invalid If set to true, invalid values or invalid
     *     property names are ignored and no exception is thrown if an invalid
     *     value or property name occurs. Instead an invalid valud will be replaced
     *     with a default value and an invalid property name will not result
     *     in a set property.
     *
     * @return Resource New Resource object which is a member of this resource category.
     * @throws InvalidResourceException if the resource cannot be stored.
     * @throws ResourcePropertyException If the name of the resource property
     *     is not defined for this resource category.
     * @throws InvalidResourceCategoryException if the class_name attribute of
     *     the resource category contains a class name of a class which is not
     *     derived from the Resource class.
     */
    public function createResource(
        $name = '',
        $description = '',
        $parent_id = '',
        $properties = [],
        $ignore_invalid = false
    )
    {
        if (($this->class_name != 'Resource') and
            !is_subclass_of($this->class_name, 'Resource')) {
            //Invalid resource category specification:
            //All class names for a resource category must be derived from the
            //resource class!
            throw new InvalidResourceCategoryException(
                sprintf(
                    _('Die Ressourcenkategorie %1$s ist ungültig, da die dort angegebene Klasse %2$s nicht von der Klasse Resource abgeleitet ist!'),
                    $this->name,
                    $this->class_name
                )
            );
        }

        $resource              = new $this->class_name;
        $resource->parent_id   = $parent_id;
        $resource->category_id = $this->id;
        $resource->name        = $name;
        $resource->description = $description;

        if (!$resource->store()) {
            throw new InvalidResourceException(
                sprintf(
                    _('Fehler beim Speichern der Resource %1$s!'),
                    $resource->name
                )
            );
        }

        //The resource is stored. We can now store its attributes:
        if ($properties) {
            foreach ($properties as $name => $state) {
                try {
                    $resource->setProperty($name, $state);
                } catch (ResourcePropertyException $e) {
                    if (!$ignore_invalid) {
                        throw $e;
                    }
                }
            }
        }

        return $resource;
    }

    /**
     * Returns the default state for a resource property.
     * Depending on the type of the resource property
     * different state is returned.
     *
     * @param ResourcePropertyDefinition $definition The definition of a
     *     resource property whose default state shall be returned.
     *
     * @return mixed The default state for the property type,
     *     specified by the given property definition.
     */
    protected function setPropertyDefaultState(
        ResourcePropertyDefinition $definition
    )
    {
        switch ($definition->type) {
            case 'bool':
                return false;
            case 'num':
                return 0;
            case 'select':
                //Set the first option as default.
                //For that, we have to split the option list first,
                //since it containts semicolon separated values:
                $options = explode(';', $definition->options);
                return $options[0];
            case 'user':
                //Return the ID of the current user:
                return User::findCurrent()->id;
            case 'position':
                return '+0.0+0.0+0.0CRSWGS_84/';
            case 'institute':
            case 'fileref':
            case 'url':
            case 'text':
                return '';
        }
    }

    /**
     * Creates a ResourceProperty object for a specified Resource object.
     *
     * @param Resource $resource The resource for which the ResourceProperty
     *     object shall be built.
     * @param string $name The name of the property.
     * @param string $state The value of the property.
     *
     * @return ResourceProperty A ResourceProperty object
     *     for the given Resource object.
     * @throws ResourcePropertyException If the name of the resource property
     *     is not defined for this resource category.
     *
     * @throws InvalidResourceCategoryException If this resource category
     *     doesn't match the category of the resource object.
     */
    public function createDefinedResourceProperty(Resource $resource, $name, $state = null)
    {
        if ($resource->category_id != $this->id) {
            throw new InvalidResourceCategoryException(
                sprintf(
                    _('Die Ressource %1$s ist kein Mitglied der Ressourcenkategorie %2$s!'),
                    $resource->name,
                    $this->name
                )
            );
        }

        if (!$resource->id) {
            //The resource has no ID: probably it is a new resource.
            if ($resource->isNew()) {
                //We need an ID so we have to create one:
                $resource->getNewId();
            } else {
                throw new InvalidResourceException(
                    sprintf(
                        _('Die Ressource %1$s besitzt keine ID!'),
                        $resource->name
                    )
                );
            }
        }


        //get property definition:
        $definition = ResourcePropertyDefinition::findOneBySql(
            'INNER JOIN resource_category_properties rcp
                ON rcp.property_id = resource_property_definitions.property_id
            WHERE category_id = :category_id
            AND name = :name
            LIMIT 1',
            [
                'category_id' => $this->id,
                'name'        => $name
            ]
        );

        if (!$definition) {
            //Property is undefined for this resource category:
            throw new ResourcePropertyException(
                sprintf(
                    _('Die Eigenschaft %1$s ist für die Ressourcenkategorie %2$s nicht definiert!'),
                    $name,
                    $this->name
                )
            );
        }

        $property              = new ResourceProperty();
        $property->resource_id = $resource->id;
        $property->property_id = $definition->id;

        //if state is not set we can set it to defined default values
        //for some state types:
        if ($state === null) {
            $property->state = self::setPropertyDefaultState($definition);
        } else {
            $property->state = $state;
        }

        return $property;
    }

    /**
     * Creates a ResourceRequestProperty object
     * for a specified ResourceRequest object.
     *
     * @param ResourceRequest $request The resource request for which the
     *     ResourceRequestProperty object shall be built.
     * @param string $name The name of the property.
     * @param string $state The value of the property.
     *
     * @return ResourceRequestProperty A ResourceProperty object for the
     *     given Resource object.
     * @throws ResourcePropertyException If the name of the resource property is
     *     not defined for the resource category of the resource request.
     *
     * @throws InvalidResourceCategoryException If this resource category
     *     doesn't match the resource category of the resource request object.
     */
    public function createDefinedResourceRequestProperty(
        ResourceRequest $request,
        $name,
        $state = null
    )
    {
        if ($request->category_id != $this->id) {
            throw new InvalidResourceCategoryException(
                sprintf(
                    _('Die Resourcenanfrage %1$s ist kein Mitglied der Ressourcenkategorie %2$s!'),
                    $request->name,
                    $this->name
                )
            );
        }

        if (!$request->id) {
            //The request has no ID: probably it is a new resource request.
            if ($request->isNew()) {
                //We need an ID so we have to create one:
                $request->id = $request->getNewId();
            } else {
                throw new InvalidResourceRequestException(
                    sprintf(
                        _('Die Ressourcenanfrage %1$s besitzt keine ID!'),
                        $request->name
                    )
                );
            }
        }

        //get property definition:
        $definition = ResourcePropertyDefinition::findOneBySql(
            'INNER JOIN resource_category_properties rcp
                ON rcp.property_id = resource_property_definitions.property_id
            WHERE category_id = :category_id
            AND name = :name
            LIMIT 1',
            [
                'category_id' => $this->id,
                'name'        => $name
            ]
        );

        if (!$definition) {
            //Property is undefined for this resource category:
            throw new ResourcePropertyException(
                sprintf(
                    _('Die Eigenschaft %1$s ist für die Ressourcenkategorie %2$s nicht definiert!'),
                    $name,
                    $this->name
                )
            );
        }

        $property              = new ResourceRequestProperty();
        $property->request_id  = $request->id;
        $property->property_id = $definition->id;

        //if state is not set we can set it to defined default values
        //for some state types:
        if ($state === null) {
            $property->state = self::setPropertyDefaultState($definition);
        } else {
            $property->state = $state;
        }

        return $property;
    }

    public function getRequestableProperties()
    {
        return ResourcePropertyDefinition::findBySql(
            "INNER JOIN resource_category_properties
            USING (property_id)
            WHERE
            resource_category_properties.category_id = :category_id
            AND
            resource_category_properties.requestable = '1'
            ORDER BY type DESC, name ASC",
            [
                'category_id' => $this->id
            ]
        );
    }

    /**
     * Determines if this resource category has a property with the
     * specified name and type.
     *
     * @param string $name The requested property name.
     * @param string $type The requested property type (optional).
     *
     * @return bool True, if a property with the specified name and type
     *     exists, false otherwise.
     */
    public function hasProperty($name = '', $type = null)
    {
        if ($type) {
            return ResourceCategoryProperty::countBySql(
                    'INNER JOIN resource_property_definitions
                USING (property_id)
                WHERE
                name = :name
                AND
                type = :type
                AND
                category_id = :category_id',
                    [
                        'name'        => $name,
                        'type'        => $type,
                        'category_id' => $this->id
                    ]
                ) > 0;
        } else {
            return ResourceCategoryProperty::countBySql(
                    'INNER JOIN resource_property_definitions
                USING (property_id)
                WHERE
                name = :name
                AND
                category_id = :category_id',
                    [
                        'name'        => $name,
                        'category_id' => $this->id
                    ]
                ) > 0;
        }
    }

    /**
     * Determines if the user has write permissions for the
     * resource property specified by its name.
     *
     * @param string The name of the resource property definition.
     * @param User $user The user whose permissions shall be checked.
     * @param Resource|null $resource An optional resource that shall be used
     *     to check for non-global permissions.
     *
     * @return bool True, if the user has write permissions, false otherwise.
     * @throws ResourcePropertyDefinitionException If no property is found.
     *
     */
    public function userHasPropertyWritePermissions(string $name, User $user, $resource = null)
    {
        $property = ResourcePropertyDefinition::findOneBySql(
            'name = :name',
            [
                'name' => $name
            ]
        );

        if (!$property) {
            throw new ResourcePropertyDefinitionException(
                sprintf(
                    _('Die Ressourceneigenschaft %s existiert nicht!'),
                    $name
                )
            );
        }

        if ($property->write_permission_level == 'admin-global') {
            return ResourceManager::userHasGlobalPermission(
                $user,
                'admin'
            );
        } elseif ($resource instanceof Resource) {
            //It must be a permission for the specified resource.
            return $resource->userHasPermission(
                $user,
                $property->write_permission_level
            );
        } else {
            //We cannot check permissions.
            return false;
        }
    }

    /**
     * Get the icon of a category
     * @param string $role
     * @return Icon
     */
    public function getIcon($role = Icon::ROLE_INFO)
    {
        if ($this->iconnr == 0) {
            //No special icon
            return Icon::create('resources', $role);
        } elseif ($this->iconnr == 1) {
            return Icon::create('home', $role);
        } else {
            //No known icon
            return Icon::create('resources', $role);
        }
    }
}
