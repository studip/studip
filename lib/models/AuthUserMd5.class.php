<?php
/**
 * AuthUserMd5.class.php
 * model class for table auth_user_md5
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string user_id database column
 * @property string id alias column for user_id
 * @property string username database column
 * @property string password database column
 * @property string perms database column
 * @property string vorname database column
 * @property string nachname database column
 * @property string email database column
 * @property string validation_key database column
 * @property string auth_plugin database column
 * @property string locked database column
 * @property string lock_comment database column
 * @property string locked_by database column
 * @property string visible database column
 */

class AuthUserMd5 extends SimpleORMap
{

    /**
     *
     * @param string $id primary key of table
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'auth_user_md5';
        parent::configure($config);
    }
}
