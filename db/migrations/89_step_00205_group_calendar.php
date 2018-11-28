<?php

class Step00205GroupCalendar extends Migration
{
    /**
     * new config options to install
     */
    private $options_new = array(
        array(
            'name' => 'CALENDAR_GROUP_ENABLE',
            'description' => 'Schaltet die Gruppenterminkalender-Funktionen ein.',
            'section' => 'modules',
            'type' => 'boolean',
            'value' => '0'
        ),
        array(
            'name' => 'COURSE_CALENDAR_ENABLE',
            'description' => 'Kalender als Inhaltselement in Veranstaltungen.',
            'section' => 'modules',
            'type' => 'boolean',
            'value' => '0'
        )
    );

    /**
     * short description of this migration
     */
    function description()
    {
        return 'extends the calendar by group calendar functions';
    }

    /**
     * insert list of options into config table
     */
    function insertConfig($options)
    {
        $db = DBManager::get();
        $time = time();

        $stmt = $db->prepare("
                INSERT INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, $time, $time, :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }

    /**
     * remove list of options from config table
     */
    function deleteConfig($options)
    {
        $db = DBManager::get();

        $stmt = $db->prepare("DELETE FROM config WHERE field = :name");

        foreach ($options as $option) {
            $stmt->execute(array('name' => $option['name']));
        }
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `seminar_user` ADD `bind_calendar` TINYINT( 1 ) NOT NULL DEFAULT '1'");
        $db->exec("ALTER TABLE `calendar_events` ADD `editor_id` VARCHAR( 32 ) NOT NULL AFTER `autor_id`");
        $db->exec("ALTER TABLE `calendar_events` ADD `importdate` INT( 11 ) NOT NULL DEFAULT '0'");
        $db->exec("ALTER TABLE `contact` ADD `calpermission` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '1'");
        $db->exec("ALTER TABLE `statusgruppen` ADD `calendar_group` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0'");
        $db->exec("CREATE TABLE IF NOT EXISTS `calendar_sync` (
            `range_id` varchar(32) NOT NULL default '',
            `client_identifier` varchar(255) NOT NULL default '',
            `last_sync` int(11) NOT NULL default '0',
            PRIMARY KEY (`range_id`,`client_identifier`)
            ) ENGINE=MyISAM");
        $this->insertConfig($this->options_new);
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `seminar_user` DROP `bind_calendar`");
        $db->exec("ALTER TABLE `calendar_events` DROP `editor_id`");
        $db->exec("ALTER TABLE `calendar_events` DROP `importdate`");
        $db->exec("ALTER TABLE `contact` DROP `calpermission`");
        $db->exec("ALTER TABLE `statusgruppen` DROP `calendar_group`");
        $db->exec("DROP TABLE `calendar_sync`");
        $this->deleteConfig($this->options_new);
    }
}
