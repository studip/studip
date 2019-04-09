<?php
/**
 * FileURL.php
 * model class for table file_urls
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class FileURL extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'file_urls';
        $config['belongs_to']['file'] = [
            'class_name'  => 'File',
            'foreign_key' => 'file_id',
        ];
        parent::configure($config);
    }
}
