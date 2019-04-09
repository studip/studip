<?php
/**
 * Migration for StEP00294
 *
 * @author  Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 *
 * @see https://develop.studip.de/trac/ticket/6180
 */
class StEP00294InnoDB extends Migration
{

    /**
     * Describe what the migration does: convert tables to InnoDB.
     * @return string
     */
    public function description()
    {
        return 'Converts the Stud.IP database tables to InnoDB engine';
    }

    /**
     * Convert all tables to InnoDB engine, using Barracuda format if supported.
     */
    public function up()
    {
        global $DB_STUDIP_DATABASE;

        // Check if InnoDB is enabled in database server.
        $engines = DBManager::get()->fetchAll("SHOW ENGINES");
        $innodb = false;
        foreach ($engines as $e) {
            // InnoDB is found and enabled.
            if ($e['Engine'] == 'InnoDB' && in_array(strtolower($e['Support']), ['default', 'yes'])) {
                $innodb = true;
                break;
            }
        }

        if ($innodb) {
            $start = microtime(true);

            // Tables to ignore on engine conversion.
            $ignore_tables = [];

            // Get version of database system (MySQL/MariaDB/Percona)
            $data = DBManager::get()->fetchFirst("SELECT VERSION() AS version");
            $version = $data[0];



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
                $stmt_fulltext = DBManager::get()->prepare("SHOW INDEX FROM `:table` WHERE Index_type = 'FULLTEXT'");
                foreach ($tables as $k => $t) {
                    $stmt_fulltext->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
                    $stmt_fulltext->execute();
                    if ($stmt_fulltext->fetch()) {
                        $ignore_tables[] = $t;
                        unset($tables[$k]);
                    }
                }
                if (count($ignore_tables)) {
                    $this->announce('The following tables needs fulltext indices '.
                        'which are not supported for InnoDB in your database '.
                        'version, so the tables will be left untouched: ' . join(',', $ignore_tables));
                }
            }


            // Use Barracuda format if database supports it (5.5 upwards).
            if (version_compare($version, '5.5', '>=')) {
                // Get innodb_file_per_table setting
                $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_per_table'");
                $file_per_table = $data['Value'];

                // Check if Barracuda file format is enabled
                $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_format'");
                $file_format = $data['Value'];

                // All settings ok, use Barracuda.
                if (strtolower($file_per_table) == 'on' && strtolower($file_format) == 'barracuda') {
                    $rowformat = 'DYNAMIC';
                // Barracuda cannot be enabled, use Antelope format.
                } else {
                    $this->announce('Barracuda row format cannot be used for '.
                        'the following reason(s), falling back to Antelope.');
                    if (strtolower($file_per_table) != 'on') {
                        $this->announce('- file_per_table is not enabled');
                    }
                    if (strtolower($file_format) != 'barracuda') {
                        $this->announce('- file_format is not set to "Barracuda"');
                    }
                    $rowformat = 'COMPACT';
                }
            } else {
                $this->announce('Barracuda row format is supported only in '.
                    'MySQL 5.5 and up, falling back to Antelope.');
                $rowformat = 'COMPACT';
            }

            // Prepare query for table conversion.
            $stmt = DBManager::get()->prepare("ALTER TABLE `:table` ROW_FORMAT=:rowformat ENGINE=:newengine");
            $stmt->bindParam(':rowformat', $rowformat, StudipPDO::PARAM_COLUMN);
            $newengine = 'InnoDB';
            $stmt->bindParam(':newengine', $newengine, StudipPDO::PARAM_COLUMN);

            // Now convert the found tables.
            foreach ($tables as $t) {
                try {
                    $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
                    $stmt->execute();
                } catch (Exception $e) {
                    throw new Exception('Error while migrating table "' . $t . '", error: ' . $e->getMessage());
                }
            }


            $end = microtime(true);

            $duration = $end - $start;
            $human_duration = sprintf("%02d:%02d:%02d",
                ($duration / 60 / 60) % 24, ($duration / 60) % 60, $duration % 60);

            $this->announce('Migration finished, duration ' . $human_duration);

        // InnoDB not enabled, do nothing but show a message.
        } else {
           throw new Exception('Migration 186: The storage engine InnoDB is not enabled in your '.
                'database installation, tables cannot be converted.');
        }

    }

    /**
     * Convert all database tables back to MyISAM engine.
     */
    public function down()
    {
        global $DB_STUDIP_DATABASE;

        // Fetch all tables that need to be converted.
        $tables = DBManager::get()->fetchFirst("SELECT TABLE_NAME
            FROM `information_schema`.TABLES
            WHERE TABLE_SCHEMA=:database AND ENGINE=:oldengine
            ORDER BY TABLE_NAME",
            [
                ':database' => $DB_STUDIP_DATABASE,
                ':oldengine' => 'InnoDB'
            ]);

        // Prepare query for table conversion.
        $stmt = DBManager::get()->prepare("ALTER TABLE `:table` ENGINE=:newengine");
        $newengine = 'MyISAM';
        $stmt->bindParam(':newengine', $newengine, StudipPDO::PARAM_COLUMN);

        // Now convert the found tables.
        foreach ($tables as $t) {
            $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
            $stmt->execute();
        }

    }

}
