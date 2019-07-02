#!/usr/bin/env php
<?php
# Lifter007: TODO
# Lifter003: TODO
/*
 * migrate.php - Migrations for Stud.IP
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once __DIR__ . '/studip_cli_env.inc.php';

if (isset($_SERVER['argv'])) {
    # check for command line options
    $options = getopt('1:d:lm:t:v');
    if ($options === false) {
        exit(1);
    }

    # check for options
    $single = false;
    $domain = 'studip';
    $list = false;
    $path = $STUDIP_BASE_PATH . '/db/migrations';
    $verbose = false;
    $target = null;

    foreach ($options as $option => $value) {
        switch ($option) {
            case '1':
                $single = (string) $value;
                break;
            case 'd':
                $domain = (string) $value;
                break;
            case 'l':
                $list = true;
                break;
            case 'm':
                $path = $value;
                break;
            case 't':
                $target = (int) $value;
                break;
            case 'v':
                $verbose = true;
                break;
        }
    }

    $version = new DBSchemaVersion($domain);
    $migrator = new Migrator($path, $version, $verbose);

    if ($list) {
        $migrations = $migrator->relevantMigrations($target);

        foreach ($migrations as $number => $migration) {
            $description = $migration->description() ?: '(no description)';
            printf("%3d %s\n", $number, $description);
        }
    } elseif ($single) {
        $direction = 'up';
        if ($single[0] === '-') {
            $direction = 'down';
            $single = substr($single, 1);
        }
        $migrator->execute($single, $direction);
    } else {
        $migrator->migrateTo($target);
    }
}
