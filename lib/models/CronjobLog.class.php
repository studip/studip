<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CronjobLog.class.php
//
// Copyright (C) 2013 Jan-Hendrik Willms <tleilax+studip@gmail.com>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

/**
 * CronjobLog - Model for the database table "cronjobs_logs"
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 *
 * @property string log_id database column
 * @property string id alias column for log_id
 * @property string schedule_id database column
 * @property string scheduled database column
 * @property string executed database column
 * @property string exception database column
 * @property string output database column
 * @property string duration database column
 * @property CronjobSchedule schedule belongs_to CronjobSchedule
 */

class CronjobLog extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cronjobs_logs';

        $config['belongs_to']['schedule'] = [
            'class_name'  => 'CronjobSchedule',
            'foreign_key' => 'schedule_id',
        ];


        parent::configure($config);
    }

    public function setException($exception_or_string)
    {
        if ($exception_or_string instanceof Exception) {
            $exception_as_string = display_exception($exception_or_string, false, true);
            return $this->content['exception'] = $exception_as_string;
        }

        if (is_string($exception_or_string)) {
            return $this->content['exception'] = $exception_or_string;
        }

        return $this->content['exception'] = null;
    }

}
