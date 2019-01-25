<?php
/**
 * LtiTool.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class LtiTool extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'lti_tool';

        $config['has_many']['links'] = [
            'class_name'        => LtiData::class,
            'assoc_foreign_key' => 'tool_id',
            'on_delete'         => 'delete'
        ];

        parent::configure($config);
    }

    /**
     * Find all entries.
     */
    public static function findAll()
    {
        return self::findBySQL('1 ORDER BY name');
    }
}
