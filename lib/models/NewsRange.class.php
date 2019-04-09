<?php
/**
 * NewsRange.class.php - model class for table Institute
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string news_id database column
 * @property string range_id database column
 * @property string type computed column
 * @property string name computed column
 * @property string id computed column read/write
 * @property User user belongs_to User
 * @property Course course belongs_to Course
 * @property Institute institute belongs_to Institute
 */
class NewsRange extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'news_range';
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['institute'] = [
            'class_name' => 'Institute',
            'foreign_key' => 'range_id',
        ];
        $config['additional_fields']['type'] = true;
        $config['additional_fields']['name'] = true;
        parent::configure($config);
    }

    function getType()
    {
        return get_object_type($this->range_id, ['sem','inst','user']);
    }

    function getName()
    {
        switch ($this->type) {
            case 'global':
                return 'Stud.IP';
                break;
            case 'sem':
                return $this->course->name;
                break;
            case 'user':
                return $this->user->getFullname();
                break;
            case 'inst':
            case 'fak':
                return $this->institute->name;
                break;
            }
    }
}
