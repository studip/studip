<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Tool to delete old files in the tmp-directory.
* 
* This file checks the tmp-directory for old files an deletes them.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup  export_modules
* @module       oscar
* @package      Export
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// oscar.inc.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
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

require_once ("export_tmp_gc.inc.php");
export_tmp_gc();
?>