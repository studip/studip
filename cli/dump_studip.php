#!/usr/bin/env php
<?php
/**
* dump_studip.php
*
*
*
*
* @author       André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// dump_studip.php
//
// Copyright (C) 2011 André Noack <noack@data-quest.de>
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
require_once 'studip_cli_env.inc.php';

function exec_or_die($cmd) {
    exec($cmd . ' 2>&1',$output,$ok);
    if ($ok > 0) {
        fwrite(STDOUT,join("\n", array_merge([$cmd], $output)) . "\n");
        exit(1);
    }
}

$dump_dir = $_SERVER['argv'][1] ? realpath($_SERVER['argv'][1]) : null;
$dump_only = $_SERVER['argv'][2];

if (!$dump_dir) {
    fwrite(STDOUT,'Usage: ' . basename(__FILE__) . ' PATH [db|base|data]' .chr(10).'Dump all without second parameter.'.chr(10));
    exit(0);
}
if (!is_writeable($dump_dir)) {
    trigger_error('Directory: ' . $dump_dir . ' is not writeable!', E_USER_ERROR);
}

$today = date("Ymd");
$prefix = Config::get()->STUDIP_INSTALLATION_ID ? Config::get()->STUDIP_INSTALLATION_ID : 'studip';
if (!$dump_only || $dump_only == 'db') {
    $dump_db_dir = $dump_dir . '/db-' . $today;
    if (!is_dir($dump_db_dir)) {
        mkdir($dump_db_dir);
    }
    foreach(DBManager::get()->query("SHOW TABLES") as $tables) {
        $table = $tables[0];
        $dump_table = $dump_db_dir . '/' . $table . '-' . $today . '.sql';
        fwrite(STDOUT, 'Dumping database table ' . $table . chr(10));
        exec_or_die("mysqldump -u$DB_STUDIP_USER -h$DB_STUDIP_HOST -p$DB_STUDIP_PASSWORD $DB_STUDIP_DATABASE $table > $dump_table");
    }
    $dump_db = $dump_dir . '/' . $prefix . '-DB-' . $today . '.tar.gz';
    fwrite(STDOUT, 'Packing database to ' . $dump_db . chr(10));
    exec_or_die("cd $dump_db_dir && tar -czf $dump_db *");
    exec_or_die("rm -rf $dump_db_dir");
}
if (!$dump_only || $dump_only == 'base') {
    $dumb_studip = $dump_dir . '/' . $prefix . '-BASE-' . $today . '.tar.gz';
    $base_path = realpath($STUDIP_BASE_PATH);
    if (!$base_path) {
        trigger_error('Stud.IP directory not found!', E_USER_ERROR);
    }
    fwrite(STDOUT, 'Dumping Stud.IP directory to ' . $dumb_studip . chr(10));
    exec_or_die("cd $base_path && tar -czf $dumb_studip --exclude 'data/*' .");
}
if (!$dump_only || $dump_only == 'data') {
    $data_path = realpath($UPLOAD_PATH . '/../');
    if ($data_path) {
        $dumb_data = $dump_dir . '/' . $prefix . '-DATA-' . $today . '.tar.gz';
        fwrite(STDOUT, 'Dumping data directory to ' . $dumb_data . chr(10));
        exec_or_die("cd $data_path && tar -czf $dumb_data .");
    }
}
exit(0);
