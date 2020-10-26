#!/usr/bin/env php
<?php
    require_once __DIR__ . '/studip_cli_env.inc.php';

    if ($argc < 2) {
        die(1);
    }
    if ($argv[1] === '--before') {
        // Remove current versions of files
        array_map('unlink', glob("{$GLOBALS['STUDIP_BASE_PATH']}/public/assets/stylesheets/*.css"));
        array_map('unlink', glob("{$GLOBALS['STUDIP_BASE_PATH']}/public/assets/javascripts/*.js"));
        array_map('unlink', glob("{$GLOBALS['STUDIP_BASE_PATH']}/public/assets/javascripts/*.js.map"));
    } elseif ($argv[1] === '--after') {
        // Get current svn status of files
        $status = shell_exec("svn st {$GLOBALS['STUDIP_BASE_PATH']}/public/assets");
        foreach (explode("\n", $status) as $line) {
            if (!$line) {
                continue;
            }

            // Extract filename
            $line = trim($line);
            $file = trim(substr($line, 1));

            // Discard every other file than .css, .js and .map files
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($extension, ['css', 'js', 'map'])) {
                continue;
            }

            if ($line[0] === '!') {
                // If file is missing, remove it via svn
                system("svn rm {$file}");
            } elseif ($line[0] === '?') {
                // If file is new, add it via svn
                system("svn add {$file}");
            }
        }
    } else {
        die(1);
    }
