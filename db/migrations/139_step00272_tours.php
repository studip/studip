<?php
# refers to https://develop.studip.de/trac/ticket/4409
class Step00272Tours extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds tables for Stud.IP tours';
    }

    /**
     * perform this migration
     */
    function up()
    {
        Config::get()->create('TOURS_ENABLE', array(
            'value' => 1, 
            'is_default' => 1, 
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'global',
            'description' => _('Aktiviert die Funktionen zum Anbieten von Touren in Stud.IP')
            ));
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `help_tours` (
                `tour_id` char(32) NOT NULL,
                `name` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `type` enum('tour','wizard') NOT NULL,
                `roles` varchar(255) NOT NULL,
                `version` int(11) unsigned NOT NULL DEFAULT '1',
                `language` char(2) NOT NULL DEFAULT 'de',
                `studip_version` varchar(32) NOT NULL DEFAULT '',
                `installation_id` varchar(255) NOT NULL DEFAULT 'demo-installation',
                `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`tour_id`)
            ) ENGINE=MyISAM;");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `help_tour_audiences` (
                `tour_id` char(32) NOT NULL,
                `range_id` char(32) NOT NULL,
                `type` enum('inst','sem','studiengang','abschluss','userdomain','tour') NOT NULL,
                PRIMARY KEY (`tour_id`,`range_id`,`type`)
            ) ENGINE=MyISAM;");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `help_tour_settings` (
                `tour_id` varchar(32) NOT NULL,
                `active` tinyint(4) NOT NULL,
                `access` enum('standard','link','autostart','autostart_once') DEFAULT NULL,
                PRIMARY KEY (`tour_id`)
            ) ENGINE=MyISAM;");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `help_tour_steps` (
                `tour_id` char(32) NOT NULL DEFAULT '',
                `step` tinyint(4) NOT NULL DEFAULT '1',
                `title` varchar(255) NOT NULL DEFAULT '',
                `tip` text NOT NULL,
                `orientation` enum('T','TL','TR','L','LT','LB','B','BL','BR','R','RT','RB') NOT NULL DEFAULT 'B',
                `interactive` tinyint(4) NOT NULL,
                `css_selector` varchar(255) NOT NULL,
                `route` varchar(255) NOT NULL DEFAULT '',
                `author_id` char(32) NOT NULL DEFAULT '',
                `mkdate` int(11) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`tour_id`,`step`)
            ) ENGINE=MyISAM;");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `help_tour_user` (
                `tour_id` varchar(32) COLLATE latin1_german1_ci NOT NULL,
                `user_id` varchar(32) COLLATE latin1_german1_ci NOT NULL,
                `step_nr` int(11) NOT NULL,
                `completed` tinyint(4) NOT NULL DEFAULT '0',
                PRIMARY KEY (`tour_id`,`user_id`)
            ) ENGINE=MyISAM;");
    }

    /**
     * revert this migration
     */
    function down()
    {
        Config::get()->delete('TOURS_ENABLE');
    }
}
