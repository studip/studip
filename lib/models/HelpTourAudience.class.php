<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2014 Arne Schröder <schroeder@data-quest>,
// Suchi & Berg GmbH <info@data-quest.de>
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
 * HelpTourSteps.class.php - model class for tour audiences
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schröder <schroeder@data-quest>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * 
 * @property string tour_id database column
 * @property string range_id database column
 * @property string type database column
 * @property string id computed column read/write
 */
class HelpTourAudience extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'help_tour_audiences';
        
        parent::configure($config);
    }
}
