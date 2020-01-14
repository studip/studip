<?php

/**
 * ResourceSearch.class.php - A search type for resources.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @author      Timo Hartge <hartge@data-quest.de>
 * @copyright   2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


class ResourceSearch extends SearchType
{
    //Attributes:


    /**
     * string[]
     * Limits the search results to resources where a person has at least
     * the permission levels specified in this attribute.
     * Defaults to ['user', 'autor', 'tutor', 'admin'].
     */
    protected $accepted_permission_levels;

    /**
     * bool
     * Whether to use global permissions when searching for resources
     * where a person has permissions on (true) or not. Defaults to true.
     */
    protected $use_global_permissions;


    /**
     * string[]
     * Additional properties that shall be appended to the name of the resource.
     * The array must be one-dimensional.
     */
    protected $additional_display_properties;


    /**
     * string
     * A format specification for additional properties in the "sprintf syntax".
     * This format specification must have only one placeholder named '%s'
     * in which the additional properties will be inserted, separated by
     * the string ", ".
     * This attribute defaults to '[%s]'.
     */
    protected $additional_property_format;


    /**
     * array
     * Array of class_names of Resource classes that will be included in the search.
     * An empty array will include all resource classes.
     */
    protected $searchable_resource_classes;


    //Setup and search configuration methods:


    public function __construct()
    {
        $this->accepted_permission_levels = [
            'user', 'autor', 'tutor', 'admin'
        ];
        $this->use_global_permissions = true;
        $this->additional_display_properties = [];
        $this->additional_property_format = '[%s]';
        $this->searchable_resource_classes = [];
    }


    /**
     * The setter method for the $accepted_permission_levels attribute.
     *
     * @param string[] $accepted_permission_levels The new value for the attribute.
     */
    public function setAcceptedPermissionLevels($levels = [])
    {
        //Check the array:
        if (!$levels) {
            throw new InvalidArgumentException(
                _('Es wurde keine gültige Berechtigungsstufe übergeben!')
            );
        }

        if (!is_array($levels)) {
            $levels = [$levels];
        }

        foreach ($levels as $level) {
            if (!in_array($level, ['user', 'autor', 'tutor', 'admin'])) {
                throw new InvalidArgumentException(
                    sprintf(
                        _('Die Berechtigungsstufe %s ist ungültig!'),
                        $level
                    )
                );
            }
        }

        //The checks are finished: set the new levels:
        $this->accepted_permission_levels = array_unique($levels);
    }


    /**
     * The getter method for the $accepted_permission_levels attribute.
     *
     * @returns string[]
     */
    public function getAcceptedPermissionLevels()
    {
        return $this->accepted_permission_levels;
    }


    /**
     * The setter method for the $use_global_permission attribute.
     *
     * @param bool $use_global_permissions The new value for the attribute.
     */
    public function setUseOfGlobalPermissions($use_global_permissions = true)
    {
        $this->use_global_permissions = (bool)$use_global_permissions;
    }


    /**
     * The getter method for the $use_global_permission attribute.
     *
     * @returns bool
     */
    public function getUseOfGlobalPermissions()
    {
        return $this->use_global_permissions;
    }


    /**
     * The setter method for the $additional_display_properties attribute.
     *
     * @param array $properties The new value for the attribute.
     */
    public function setAdditionalDisplayProperties($properties = [])
    {
        $this->additional_display_properties = $properties;
    }


    /**
     * The getter method for the $additional_display_properties attribute.
     *
     * @returns array @see $additional_display_properties for the array format.
     */
    public function getAdditionalDisplayProperties()
    {
        return $this->additional_display_properties;
    }


    /**
     * The setter method for the $additional_property_format attribute.
     *
     * @param string $format The new value for the attribute.
     */
    public function setAdditionalPropertyFormat($format = '[%s]')
    {
        $this->additional_property_format = $format;
    }


    /**
     * The getter method for the $additional_property_format attribute.
     *
     * @returns string
     */
    public function getAdditionalPropertyFormat()
    {
        return $this->additional_property_format;
    }

    /**
     * The setter method for the $searchable_resource_classes attribute.
     *
     * @param array $categories The new value for the attribute.
     */
    public function setSearchableResourceClasses($classes = [])
    {
        $this->searchable_resource_classes = $classes;
    }


    /**
     * The getter method for the $searchable_resource_classes attribute.
     *
     * @returns array
     */
    public function getSearchableResourceClasses()
    {
        return $this->searchable_resource_classes;
    }


    //SearchType interface implementations:


    public function getTitle()
    {
        return _('Ressourcensuche');
    }

    /**
     * @param string $keyword The name of the resource or a part of it.
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

        $sql = "INNER JOIN resource_categories rc
            ON resources.category_id = rc.id
            WHERE
            resources.name LIKE CONCAT('%', :keyword, '%') ";
        $sql_params = [
            'keyword' => $keyword
        ];

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

        $resources = Resource::findBySql(
            $sql,
            $sql_params
        );

        $results = [];
        foreach ($resources as $resource) {

            if (!empty($this->searchable_resource_classes)) {
                if(!in_array($resource->class_name, $this->searchable_resource_classes)) {
                    continue;
                }
            }

            $additional_text = '';
            if (count($this->additional_display_properties)) {
                $additional_values = [];
                foreach ($this->additional_display_properties as $ap) {
                    $additional_values[] = $resource->getProperty($ap);
                }
                $additional_text = implode(', ', $additional_values);
            }

            $complete_text = $resource->name;
            if ($additional_text) {
                $complete_text .= ' ' . sprintf(
                    $this->additional_property_format,
                    $additional_text
                );
            }
            $results[] = [
                $resource->id,
                $complete_text
            ];
        }

        return $results;
    }


    public function includePath()
    {
        return __FILE__;
    }
}
