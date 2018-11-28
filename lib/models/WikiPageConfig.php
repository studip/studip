<?php
/**
 * WikiPageConfig.php - Wiki page permissions
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class WikiPageConfig extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'wiki_page_config';

        $config['belongs_to']['course'] = array(
            'class_name' => 'Course',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['institute'] = array(
            'class_name' => 'Institute',
            'foreign_key' => 'range_id',
        );

        parent::configure($config);
    }
}
