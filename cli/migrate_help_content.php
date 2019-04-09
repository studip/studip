#!/usr/bin/env php
<?php
/**
* migrate_help_content.php
*
* @author       Arne Schröder <schroeder@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2014 Arne Schröder <schroeder@data-quest.de>,
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

require_once __DIR__ . '/studip_cli_env.inc.php';

$help_path = __DIR__ . '/../doc/helpbar';

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

if (!$argv[2]){
    fwrite(STDOUT,'Usage: ' . basename(__FILE__) . ' [version] [language]' .chr(10));
    exit(0);
}

$query = "SELECT * FROM help_content WHERE studip_version = ? LIMIT 1";
$statement = DBManager::get()->prepare($query);
$statement->execute([$argv[1]]);
$ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);
if (count($ret)) {
    trigger_error('Helpbar content already present for this version!', E_USER_ERROR);
}

$filename = $help_path .'/'. $argv[2] . '/helpcontent.json';
if (is_file($filename)){
    $json = json_decode(file_get_contents($filename), true);
} else {
    trigger_error("File not found: ".$filename, E_USER_ERROR);
}

if ($json === null) {
    trigger_error('Helpbar content could not be loaded. File: '.$filename, E_USER_ERROR);
}

foreach ($json as $row) {
    if (!is_array($row['text']))
        $row['text'] = [$row['text']];
        if (!$row['label'])
            $row['label'] = '';
        if (!$row['icon'])
            $row['icon'] = '';
            foreach ($row['text'] as $index => $text) {
        $count[$argv[2].$row['route']]++;
        $query = "INSERT INTO help_content (content_id, language, label, icon, content, route, studip_version, position, custom, visible, author_id, installation_id, mkdate)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, '', ?, UNIX_TIMESTAMP())";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([md5(uniqid(rand(), true)), $argv[2], ($index == 0 ? $row['label'] : ''), ($index == 0 ? $row['icon'] : ''), $text, $row['route'], $argv[1], $count[$argv[2].$row['route']], Config::get()->STUDIP_INSTALLATION_ID]);
    }
}
if (count($count)) {
    if (!Config::get()->getValue('HELP_CONTENT_CURRENT_VERSION'))
        Config::get()->create('HELP_CONTENT_CURRENT_VERSION', [
            'value' => $argv[1],
            'is_default' => 0,
            'type' => 'string',
            'range' => 'global',
            'section' => 'global',
            'description' => _('Aktuelle Version der Helpbar-Einträge in Stud.IP')
            ]);
    else
        Config::get()->store('HELP_CONTENT_CURRENT_VERSION', $argv[1]);
}
fwrite(STDOUT, 'help content added for '.count($count).' routes.' . chr(10));
exit(1);
