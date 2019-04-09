#!/usr/bin/env php
<?php
require_once(__DIR__.'/studip_cli_env.inc.php');

echo 'Migration starting at '.date('d.m.Y H:i:s').".\n";
$start = microtime(true);

global $DB_STUDIP_DATABASE;

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

    // Tables to ignore on engine conversion.
    $ignore_tables = [];




    // Fetch all tables that need to be converted.
    $tables = DBManager::get()->fetchFirst("SELECT TABLE_NAME
        FROM `information_schema`.TABLES
        WHERE TABLE_SCHEMA=:database AND ENGINE=:oldengine
        ORDER BY TABLE_NAME",
        [
            ':database' => $DB_STUDIP_DATABASE,
            ':oldengine' => 'MyISAM',
        ]);

            /*
             * lit_catalog needs fulltext indices which InnoDB doesn't support
             * in older versions.
             */
            if (version_compare($version, '5.6', '<')) {
                $stmt_fulltext = DBManager::get()->prepare("SHOW INDEX FROM :database.:table WHERE Index_type = 'FULLTEXT'");
                foreach ($tables as $k => $t) {
                    $stmt_fulltext->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
                    $stmt_fulltext->bindParam(':database', $DB_STUDIP_DATABASE, StudipPDO::PARAM_COLUMN);
                    $stmt_fulltext->execute();
                    if ($stmt_fulltext->fetch()) {
                        $ignore_tables[] = $t;
                        unset($tables[$k]);
                    }
                }
                if (count($ignore_tables)) {
                    echo 'The following tables needs fulltext indices '.
                        'which are not supported for InnoDB in your database '.
                        'version, so the tables will be left untouched: ' . join(',', $ignore_tables) . "\n";
                }
            }


    // Use Barracuda format if database supports it (5.5 upwards).
    if (version_compare($version, '5.5', '>=')) {
        echo "\tFound MySQL in version >= 5.5, checking if Barracuda file format is supported...";
        // Get innodb_file_per_table setting
        $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_per_table'");
        $file_per_table = $data['Value'];

        // Check if Barracuda file format is enabled
        $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_format'");
        $file_format = $data['Value'];

        if (mb_strtolower($file_per_table) == 'on' && mb_strtolower($file_format) == 'barracuda') {
            echo " yes.\n";
            $rowformat = 'DYNAMIC';
        } else {
            echo " no:\n";
            if (mb_strtolower($file_per_table) != 'on') {
                echo "\t- file_per_table not set\n";
            }
            if (mb_strtolower($file_format) != 'barracuda') {
                echo "\t- file_format not set to Barracuda (but to " . $file_format . ")\n";
            }
            $rowformat = 'COMPACT';
        }
    }

    // Prepare query for table conversion.
    $stmt = DBManager::get()->prepare("ALTER TABLE :database.:table ROW_FORMAT=:rowformat ENGINE=:newengine");
    $stmt->bindParam(':database', $DB_STUDIP_DATABASE, StudipPDO::PARAM_COLUMN);
    $stmt->bindParam(':rowformat', $rowformat, StudipPDO::PARAM_COLUMN);
    $newengine = 'InnoDB';
    $stmt->bindParam(':newengine', $newengine, StudipPDO::PARAM_COLUMN);

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


    $end = microtime(true);

    $duration = $end - $start;
    $human_duration = sprintf("%02d:%02d:%02d",
        ($duration / 60 / 60) % 24, ($duration / 60) % 60, $duration % 60);

    echo 'Migration finished at ' . date('d.m.Y H:i:s') . ', duration ' . $human_duration . ".\n";
} else {
    echo "The storage engine InnoDB is not enabled in your ".
        "database installation, tables cannot be converted.\n";
}
