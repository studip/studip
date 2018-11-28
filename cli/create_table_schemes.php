#!/usr/bin/env php
<?php
# Lifter007: TODO
# Lifter003: TODO
/**
* create_table_schemes.php
* 
* 
* 
*
* @author       André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// create_table_schemes.php
// 
// Copyright (C) 2006 André Noack <noack@data-quest.de>,
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
require_once dirname(__FILE__) . '/studip_cli_env.inc.php';
exec("grep -l 'extends SimpleORMap' $STUDIP_BASE_PATH/lib/classes/*.class.php", $output, $ok);
if(!$ok ){
    fwrite(STDOUT, "<?php\n//copy to \$STUDIP_BASE_PATH/lib/dbviews/table_schemes.inc.php\n//generated ". date('r') ."\n");
    foreach($output as $line){
        require_once $line;
        list($classname,,) = explode('.',basename($line));
        $o = new $classname();
        fwrite(STDOUT, $o->exportScheme());
    }
    fwrite(STDOUT, "?>");
}

?>