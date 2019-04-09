<?php
/**
 * UserInfo.class.php
 * model class for table user_info
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
 * @property string hobby database column
 * @property string lebenslauf database column
 * @property string publi database column
 * @property string schwerp database column
 * @property string home database column
 * @property string privatnr database column
 * @property string privatcell database column
 * @property string privadr database column
 * @property string score database column
 * @property string geschlecht database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string title_front database column
 * @property string title_rear database column
 * @property string preferred_language database column
 * @property string smsforward_copy database column
 * @property string smsforward_rec database column
 * @property string guestbook database column
 * @property string email_forward database column
 * @property string smiley_favorite database column
 * @property string motto database column
 * @property string lock_rule database column
 */

class UserInfo extends SimpleORMap
{
    /**
     * Constants for column geschlecht
     */
    const GENDER_UNKNOWN = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_DIVERSE = 3;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'user_info';
        $config['i18n_fields']['hobby'] = true;
        $config['i18n_fields']['lebenslauf'] = true;
        $config['i18n_fields']['schwerp'] = true;
        $config['i18n_fields']['publi'] = true;
        parent::configure($config);
    }
}
