<?php

/**
 * ResourcePropertyGroup.class.php - model class for resource property groups
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       TODO
 *
 * @property string id database column
 * @property string name database column
 * @property string position database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class ResourcePropertyGroup extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_property_groups';

        $config['has_many']['properties'] = [
            'class_name' => 'ResourcePropertyDefinition',
            'assoc_foreign_key' => 'property_group_id',
            'assoc_func' => 'findByPropertyGroup'
        ];

        parent::configure($config);
    }
}
