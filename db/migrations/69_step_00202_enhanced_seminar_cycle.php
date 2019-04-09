<?php

class Step00202EnhancedSeminarCycle extends Migration
{

    function description()
    {
        return 'adds new table seminar_cycle_dates and converts old metadata_dates';
    }

    function up()
    {
        $db = DBManager::get();
        $options[] =
        [
            'name'        => 'ALLOW_METADATE_SORTING',
            'type'        => 'boolean',
            'value'       => 0,
            'section'     => 'permissions',
            'description' => 'Soll es erlaubt sein, dass regelmäßige Zeiten einer Veranstaltung frei sortiert werden können?'
            ];

        $stmt = $db->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }

        $db->exec("CREATE TABLE IF NOT EXISTS `seminar_cycle_dates` (
                  `metadate_id` varchar(32) NOT NULL,
                  `seminar_id` varchar(32) NOT NULL,
                  `start_time` time NOT NULL,
                  `end_time` time NOT NULL,
                  `weekday` tinyint(3) unsigned NOT NULL,
                  `description` varchar(255) NOT NULL DEFAULT '',
                  `sws` decimal(2,1) NOT NULL DEFAULT '0.0',
                  `cycle` tinyint(3) unsigned NOT NULL DEFAULT '0',
                  `week_offset` tinyint(3) unsigned NOT NULL DEFAULT '0',
                  `sorter` tinyint(3) unsigned NOT NULL DEFAULT '0',
                  `mkdate` int(10) unsigned NOT NULL,
                  `chdate` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`metadate_id`),
                  KEY `seminar_id` (`seminar_id`)
                ) ENGINE=MyISAM;");

        $stmt = $db->prepare("INSERT INTO `seminar_cycle_dates`
         (`metadate_id`, `seminar_id`, `start_time`, `end_time`, `weekday`,
          `description`, `cycle`, `week_offset`, `mkdate`, `chdate`)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($db->query("SELECT Seminar_id, metadata_dates, mkdate, chdate FROM seminare") as $row) {
            $md = @unserialize($row['metadata_dates']);
            if (is_array($md['turnus_data'])) {
                foreach ($md['turnus_data'] as $c) {
                    if ($c['metadate_id']) {
                        $stmt->execute([$c['metadate_id'],
                                            $row['Seminar_id'],
                                            sprintf('%02s:%02s', (int)$c['start_stunde'], (int)$c['start_minute']),
                                            sprintf('%02s:%02s', (int)$c['end_stunde'], (int)$c['end_minute']),
                                            (int)$c['day'],
                                            (string)$c['desc'],
                                            (int)$md['turnus'],
                                            (int)$md['start_woche'],
                                            $row['mkdate'],
                                            $row['chdate']
                                            ]
                                         );
                    }
                }
            }
        }
        //So Long, and Thanks for All the Fish
        $db->exec("ALTER TABLE `seminare` DROP `metadata_dates`");
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE `seminar_cycle_dates`");
        $db->exec("ALTER TABLE `seminare` ADD `metadata_dates` TEXT NOT NULL DEFAULT ''");
        $db->exec("DELETE FROM config WHERE field LIKE 'ALLOW_METADATE_SORTING'");
    }
}
?>
