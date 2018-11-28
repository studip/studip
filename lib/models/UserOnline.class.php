<?php

/**
 * UserOnline.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @copyright   206 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string user_id       database column
 * @property string id            alias column for user_id
 * @property string last_lifesign computed column read/write
 */
class UserOnline extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'user_online';
        parent::configure($config);
    }
}