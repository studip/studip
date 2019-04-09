<?php
class ConfigValues extends Migration
{
    public function description()
    {
        return 'add database table for generic config values';
    }

    public function up()
    {
        $db = DBManager::get();

        // fix up missing config entries with is_default = 1
        $db->exec('UPDATE config LEFT JOIN config c ON config.field = c.field AND c.is_default = 1
                   SET config.is_default = 1 WHERE config.is_default = 0 AND c.config_id IS NULL');

        // fix up missing user_config entries in config
        $db->exec("INSERT IGNORE INTO config (config_id, field, value, is_default, type, `range`)
                   SELECT DISTINCT MD5(user_config.field), user_config.field, '', 1, 'string', 'user'
                   FROM user_config LEFT JOIN config ON user_config.field = config.field AND is_default = 1
                   WHERE config_id IS NULL");

        // abort migration on duplicate entries in config
        $result = $db->query('SELECT field FROM config c1 JOIN config c2 USING(field, is_default) WHERE c1.config_id > c2.config_id');
        $errors = $result->fetchAll(PDO::FETCH_COLUMN);

        if (count($errors)) {
            echo "Duplicate field names in config table, aborting migration:\n", implode(', ', $errors), "\n";
            die;
        }

        // delete duplicate config values in user_config
        $db->exec('DELETE c1 FROM user_config c1 JOIN user_config c2 USING(field, user_id) WHERE c1.userconfig_id > c2.userconfig_id');

        // drop unused fields and update range
        $db->exec("ALTER TABLE config DROP parent_id, DROP position, DROP message_template,
                   CHANGE field field varchar(255) COLLATE latin1_bin NOT NULL,
                   CHANGE type type enum('boolean', 'integer', 'string', 'array') COLLATE latin1_bin NOT NULL DEFAULT 'string',
                   CHANGE `range` `range` enum('global', 'user', 'course') COLLATE latin1_bin NOT NULL DEFAULT 'global'");

        // create new table and migrate all settings
        $db->exec("ALTER TABLE user_config
                   RENAME TO config_values,
                   DROP userconfig_id,
                   DROP parent_id,
                   CHANGE field field varchar(255) COLLATE latin1_bin NOT NULL,
                   CHANGE user_id range_id varchar(32) COLLATE latin1_bin NOT NULL AFTER field,
                   ADD PRIMARY KEY (field, range_id),
                   ADD KEY range_id (range_id),
                   DROP KEY user_id");

        $db->exec("INSERT INTO config_values (field, range_id, value, comment, mkdate, chdate)
                   SELECT field, 'studip', value, comment, mkdate, chdate
                   FROM config WHERE is_default = 0");

        $db->exec('DELETE FROM config WHERE is_default = 0');

        // drop more obsolete fields
        $db->exec('ALTER TABLE config DROP config_id, DROP is_default, DROP comment, DROP KEY field, ADD PRIMARY KEY (field)');

        // migrate setting from seminare.student_mailing
        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'COURSE_STUDENT_MAILING',
            'description' => 'Über diese Option können Sie Studierenden das Schreiben von Nachrichten an alle anderen Teilnehmer der Veranstaltung erlauben.',
            'range'       => 'course',
            'type'        => 'boolean',
            'value'       => '0'
        ]);

        $db->exec("INSERT INTO config_values (field, range_id, value, mkdate, chdate, comment)
                   SELECT 'COURSE_STUDENT_MAILING', Seminar_id, student_mailing, mkdate, chdate, ''
                   FROM seminare WHERE student_mailing = 1");

        $db->exec('ALTER TABLE seminare DROP student_mailing');
    }

    public function down()
    {
        $db = DBManager::get();

        // migrate setting to seminare.student_mailing
        $db->exec('ALTER TABLE seminare ADD student_mailing tinyint(1) unsigned NOT NULL DEFAULT 0');

        $db->exec("UPDATE config_values JOIN seminare ON range_id = Seminar_id
                   SET student_mailing = value WHERE field = 'COURSE_STUDENT_MAILING'");

        // delete no longer supported values
        $db->exec("DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE `range` = 'course'");

        // restore old primary key
        $db->exec("ALTER TABLE config
                   ADD config_id varchar(32) COLLATE latin1_bin NOT NULL DEFAULT '' FIRST,
                   ADD is_default tinyint(4) NOT NULL DEFAULT 0 AFTER value,
                   ADD comment text NOT NULL,
                   DROP PRIMARY KEY");

        $db->exec('UPDATE config SET config_id = MD5(field), is_default = 1');
        $db->exec('ALTER TABLE config ADD PRIMARY KEY (config_id), ADD KEY field (field, `range`)');

        // restore user_config and old settings
        $db->exec("ALTER TABLE config_values
                   RENAME TO user_config,
                   ADD userconfig_id varchar(32) COLLATE latin1_bin NOT NULL DEFAULT '' FIRST,
                   ADD parent_id varchar(32) COLLATE latin1_bin DEFAULT NULL AFTER userconfig_id,
                   CHANGE range_id user_id varchar(32) COLLATE latin1_bin NOT NULL AFTER parent_id,
                   CHANGE field field varchar(255) NOT NULL DEFAULT ''");

        $db->exec('UPDATE user_config SET userconfig_id = MD5(CONCAT(field, user_id))');

        $db->exec("ALTER TABLE user_config
                   DROP PRIMARY KEY,
                   DROP KEY range_id,
                   ADD PRIMARY KEY (userconfig_id),
                   ADD KEY user_id (user_id, field, value(5))");

        $db->exec("INSERT INTO config (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description, comment)
                   SELECT userconfig_id, field, user_config.value, 0, type, `range`, section,
                          user_config.mkdate, user_config.chdate, description, user_config.comment
                   FROM user_config JOIN config USING(field) WHERE user_id = 'studip'");

        $db->exec("DELETE FROM user_config WHERE user_id = 'studip'");

        // restore unused fields and update range
        $db->exec("ALTER TABLE config
                   CHANGE field field varchar(255) NOT NULL DEFAULT '',
                   CHANGE type type enum('boolean', 'integer', 'string', 'array') COLLATE latin1_bin NOT NULL DEFAULT 'boolean',
                   CHANGE `range` `range` enum('global', 'user') COLLATE latin1_bin NOT NULL DEFAULT 'global',
                   ADD parent_id varchar(32) COLLATE latin1_bin NOT NULL DEFAULT '' AFTER config_id,
                   ADD position int(11) NOT NULL DEFAULT 0 AFTER section,
                   ADD message_template varchar(255) NOT NULL DEFAULT '' AFTER comment");
    }
}
