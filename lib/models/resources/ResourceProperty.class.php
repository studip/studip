<?php

/**
 * ResourceProperty.class.php - model class for resource properties
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
 * @since       TODO
 *
 * @property string property_id database column
 * @property string resource_id database column
 * @property string state database column
 * @property string form_text database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property Resource resource belongs_to Resource
 * @property ResourcePropertyDefinition definition belongs_to
 *     ResourcePropertyDefinition
 */
class ResourceProperty extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_properties';

        $config['belongs_to']['definition'] = [
            'class_name' => 'ResourcePropertyDefinition',
            'foreign_key' => 'property_id',
            'assoc_func' => 'find'
        ];

        $config['belongs_to']['resource'] = [
            'class_name' => 'Resource',
            'foreign_key' => 'resource_id',
            'assoc_func' => 'find'
        ];

        $config['additional_fields']['name'] = ['definition', 'name'];
        $config['additional_fields']['fullname'] = ['get' => 'getFullname'];
        $config['additional_fields']['display_name'] = ['definition', 'display_name'];
        $config['additional_fields']['type'] = ['definition', 'type'];
        $config['additional_fields']['info_label'] = ['definition', 'info_label'];

        parent::configure($config);
    }
    
    /**
     * Determines whether this resource property is requestable
     * by checking the requestable flag of the corresponding
     * resource category property.
     *
     * @return bool True, if this property is requestable, false otherwise.
     */
    public function isRequestable()
    {
        //We must only check if there is a ResourceCategoryProperty
        //object in the database which has the same category-ID
        //as the Resource of this ResourceProperty object,
        //the same definition-ID as this ResourceProperty and which has
        //furthermore the requestable column set to '1'.
        //In this case we return true, otherwise false.

        return ResourceCategoryProperty::countBySql(
            "category_id = :category_id
            AND property_id = :definition_id
            AND requestable = '1'
            ",
            [
                'category_id' => $this->resource->category_id,
                'definition_id' => $this->definition->id
            ]
        ) > 0;
    }


    /**
     * Creates a string containing the appropriate value
     * of this resource property.
     *
     * @return string The string representation of this resource property.
     */
    public function __toString()
    {
        $string = '';

        if ($this->type == 'position') {
            $string .= ResourceManager::getPositionString($this);
        } elseif ($this->type == 'bool') {
            $string .= $this->state ? _('ja') : _('nein');
        } elseif ($this->type == 'num') {
            $string .= $this->state;
        } elseif ($this->type == 'user') {
            $user = User::find($this->state);
            $string .= $user->getFullName();
        } else {
            $string .= $this->state;
        }
        return $string;
    }

    public function getFullname()
    {
        $name = (string)$this->definition->display_name ?: $this->definition->name;
        $category = trim(strstr($name, ':', true));
        if ($category) {
            $name = trim(strstr($name, ':'), ' :');
        }
        $value = (string)$this;
        return $name . ': ' . $value;
    }
}
