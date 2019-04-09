#!/usr/bin/env php
<?php
require_once(__DIR__.'/studip_cli_env.inc.php');

echo 'Migration starting at '.date('d.m.Y H:i:s').".\n";
$start = microtime(true);

global $DB_STUDIP_DATABASE;

// Tables to ignore on engine conversion.
$ignore_tables = [];

// Check if InnoDB is enabled in database server.
$engines = DBManager::get()->fetchAll("SHOW ENGINES");
$innodb = false;
foreach ($engines as $e) {
    // InnoDB is found and enabled.
    if ($e['Engine'] == 'InnoDB' && in_array(mb_strtolower($e['Support']), ['default', 'yes'])) {
        $innodb = true;
        break;
    }
}

if ($innodb) {
    // Get version of database system (MySQL/MariaDB/Percona)
    $data = DBManager::get()->fetchFirst("SELECT VERSION() AS version");
    $version = $data[0];

    // Use Barracuda format if database supports it (5.5 upwards).
    if (version_compare($version, '5.5', '>=')) {
        echo "\tChecking if Barracuda file format is supported...";
        // Get innodb_file_per_table setting
        $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_per_table'");
        $file_per_table = $data['Value'];

        // Check if Barracuda file format is enabled
        $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_format'");
        $file_format = $data['Value'];

        if (mb_strtolower($file_per_table) == 'on' && mb_strtolower($file_format) == 'barracuda') {

            echo " yes.\n";

            // Fetch all tables that need to be converted.
            $tables = DBManager::get()->fetchFirst("SELECT TABLE_NAME
                FROM `information_schema`.TABLES
                WHERE TABLE_SCHEMA=:database AND ENGINE=:engine
                    AND ROW_FORMAT IN (:rowformats)
                ORDER BY TABLE_NAME",
                [
                    ':database' => $DB_STUDIP_DATABASE,
                    ':engine' => 'InnoDB',
                    ':rowformats' => ['Compact', 'Redundant']
                ]);

            $newformat = 'DYNAMIC';

            // Prepare query for table conversion.
            $stmt = DBManager::get()->prepare("ALTER TABLE :database.:table ROW_FORMAT=:newformat");
            $stmt->bindParam(':database', $DB_STUDIP_DATABASE, StudipPDO::PARAM_COLUMN);
            $stmt->bindParam(':newformat', $newformat, StudipPDO::PARAM_COLUMN);

            if (count($tables) > 0) {

                // Now convert the found tables.
                foreach ($tables as $t) {
                    $local_start = microtime(true);
                    $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
                    $stmt->execute();
                    $local_end = microtime(true);
                    $local_duration = $local_end - $local_start;
                    $human_local_duration = sprintf("%02d:%02d:%02d",
                        ($local_duration / 60 / 60) % 24, ($local_duration / 60) % 60, $local_duration % 60);

                    echo "\tConversion of table " . $t . " took " . $human_local_duration . ".\n";
                }

            } else {
                echo "\tNo Antelope format tables found.\n";
            }

        } else {
            echo " no:\n";
            if (mb_strtolower($file_per_table) != 'on') {
                echo "\t- file_per_table not set\n";
            }
            if (mb_strtolower($file_format) != 'barracuda') {
                echo "\t- file_format not set to Barracuda (but to " . $file_format . ")\n";
            }
        }

        $end = microtime(true);

        $duration = $end - $start;
        $human_duration = sprintf("%02d:%02d:%02d",
            ($duration / 60 / 60) % 24, ($duration / 60) % 60, $duration % 60);

        echo 'Migration finished at ' . date('d.m.Y H:i:s') . ', duration ' . $human_duration . ".\n";

    } else {
        echo "Your database server does not yet support the Barracuda row format (you need at least 5.5).\n";
    }

} else {
    echo "The storage engine InnoDB is not enabled in your ".
        "database installation, tables cannot be converted.\n";
}
