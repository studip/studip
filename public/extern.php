<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Extern-Pages-mainfile. Calls the submodules.
*
*
*
* @author       Peter Thienel <pthienel@data.quest.de>
* @access       public
* @modulegroup  extern_modules
* @module       extern
* @package      Extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern.php
//
// Copyright (c) 2003 Peter Tienel <pthienel@data-quest.de>
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


require '../lib/bootstrap.php';

if (!Config::get()->EXTERN_ENABLE) {
    echo "<br><br><br><blockquote><b>This page is not available!<br>The module \"extern\"";
    echo " is not enabled in this Stud.IP-installation.</b></blockquote>";
    exit;
}

include "lib/extern/extern.inc.php";
