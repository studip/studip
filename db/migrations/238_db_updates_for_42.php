<?php
class DbUpdatesFor42 extends Migration
{
    public function description()
    {
        return 'various small db schema fixes for Stud.IP 4.2';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE sem_classes DROP compact_mode, DROP workgroup_mode, DROP turnus_default');

        $db->exec('ALTER TABLE personal_notifications_user CHANGE user_id user_id char(32) COLLATE latin1_bin NOT NULL');

        $db->exec("ALTER TABLE plugins_activated
                    ADD range_type enum('sem','inst','user') COLLATE latin1_bin NOT NULL default 'sem' AFTER pluginid,
                    ADD range_id char(32) COLLATE latin1_bin NOT NULL AFTER range_type,
                    CHANGE state state tinyint(1) NOT NULL DEFAULT 1");

        $db->exec("UPDATE plugins_activated SET range_type = 'sem',  range_id = SUBSTRING(poiid, 4) WHERE poiid LIKE 'sem%'");
        $db->exec("UPDATE plugins_activated SET range_type = 'inst', range_id = SUBSTRING(poiid, 5) WHERE poiid LIKE 'inst%'");
        $db->exec("UPDATE plugins_activated SET range_type = 'user', range_id = SUBSTRING(poiid, 5) WHERE poiid LIKE 'user%'");
        $db->exec('UPDATE plugins_activated SET state = 0 WHERE state = 2');

        // delete invalid entries
        $db->exec("DELETE FROM plugins_activated WHERE range_id = ''");

        $db->exec('ALTER TABLE plugins_activated
                    DROP PRIMARY KEY, DROP KEY poiid,
                    ADD PRIMARY KEY (pluginid, range_type, range_id),
                    DROP poiid');
        //alter default for studygroups, see #9002
        $db->exec("UPDATE `sem_classes` SET `course_creation_forbidden` = 0 WHERE `studygroup_mode` = 1");

    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE sem_classes
                    ADD compact_mode tinyint(4) NOT NULL AFTER name,
                    ADD workgroup_mode tinyint(4) NOT NULL AFTER compact_mode,
                    ADD turnus_default int(11) NOT NULL AFTER only_inst_user');

        $db->exec("ALTER TABLE plugins_activated ADD poiid varchar(36) COLLATE latin1_bin NOT NULL DEFAULT '' AFTER pluginid");

        $db->exec('UPDATE plugins_activated SET poiid = CONCAT(range_type, range_id)');
        $db->exec('UPDATE plugins_activated SET state = 2 WHERE state = 0');

        $db->exec("ALTER TABLE plugins_activated
                    CHANGE state state enum('on','off') COLLATE latin1_bin NOT NULL DEFAULT 'on',
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (pluginid, poiid),
                    ADD UNIQUE KEY poiid (poiid, pluginid, state),
                    DROP range_type, DROP range_id");
    }
}
