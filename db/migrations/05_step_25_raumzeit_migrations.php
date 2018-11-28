<?php
class Step25RaumzeitMigrations extends Migration
{
    function description ()
    {
        return 'modify db schema for StEP00025; see logfile in $TMP_PATH';
    }

    function up ()
    {
        // StEP202 causes this migration to fail, see #1551
        throw new Exception(_('Die Ausgangsversion der Datenbank ist zu alt. Die Migration kann nicht durchgeführt werden.'));

        // open log file
        $logfile_handle = fopen( $GLOBALS["TMP_PATH"] ."/Stud.IP_date_conversion.log", "ab");
        if(!$logfile_handle) {
            throw new Exception ("Can't open logfile ".$GLOBALS["TMP_PATH"]."/Stud.IP_date_conversion.log");
        }

        $this->write( get_class($this)." - Creating db schema...");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `themen` (
              `issue_id` varchar(32) NOT NULL default '',
              `seminar_id` varchar(32) NOT NULL default '',
              `author_id` varchar(32) NOT NULL default '',
              `title` varchar(255) NOT NULL default '',
              `description` text NOT NULL,
              `priority` smallint(5) unsigned NOT NULL default '0',
              `mkdate` int(10) unsigned NOT NULL default '0',
              `chdate` int(10) unsigned NOT NULL default '0',
              PRIMARY KEY  (`issue_id`),
              KEY `seminar_id` (`seminar_id`,`priority`)
            ) ENGINE=MyISAM;
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `ex_termine` (
              `termin_id` varchar(32) NOT NULL default '',
              `range_id` varchar(32) NOT NULL default '',
              `autor_id` varchar(32) NOT NULL default '',
              `content` varchar(255) NOT NULL default '',
              `description` text,
              `date` int(20) NOT NULL default '0',
              `end_time` int(20) NOT NULL default '0',
              `mkdate` int(20) NOT NULL default '0',
              `chdate` int(20) NOT NULL default '0',
              `date_typ` tinyint(4) NOT NULL default '0',
              `topic_id` varchar(32) default NULL,
              `expire` int(20) default NULL,
              `repeat` varchar(128) default NULL,
              `color` varchar(20) default NULL,
              `priority` tinyint(4) default NULL,
              `raum` varchar(255) default NULL,
              `metadate_id` varchar(32) default NULL,
              `resource_id` varchar(32) NOT NULL default '',
              PRIMARY KEY  (`termin_id`),
              KEY `range_id` (`range_id`,`date`),
              KEY `metadate_id` (`metadate_id`,`date`)
            ) ENGINE=MyISAM PACK_KEYS=1;
        ");

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `themen_termine` (
              `issue_id` varchar(32) NOT NULL default '',
              `termin_id` varchar(32) NOT NULL default '',
              PRIMARY KEY  (`issue_id`,`termin_id`),
                KEY `termin_id` (`termin_id`,`issue_id`)
            ) ENGINE=MyISAM;
        ");
        DBManager::get()->exec("
            ALTER TABLE `termine` DROP INDEX `autor_id`
        ");
        DBManager::get()->exec("
            ALTER TABLE `termine` DROP INDEX `range_id`
        ");
        DBManager::get()->exec("
            ALTER TABLE `termine` ADD `metadate_id` VARCHAR( 32 );
        ");

        DBManager::get()->exec("
            ALTER TABLE `termine` ADD INDEX ( `metadate_id` , `date` )
        ");

        DBManager::get()->exec("
            ALTER TABLE `termine` ADD INDEX ( `range_id` , `date` );
        ");

        DBManager::get()->exec("
            ALTER TABLE `resources_requests` ADD `reply_comment` TEXT AFTER `comment`;
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_UNDELETE_SINGLEDATE'), 'SEM_UNDELETE_SINGLEDATE', 'Einzeltermin wiederherstellen', '%user stellt Einzeltermin %singledate(%affected) in %sem(%coaffected) wieder her.', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_DELETE_SINGLEDATE'), 'SEM_DELETE_SINGLEDATE', 'Einzeltermin löschen', '%user löscht Einzeltermin %singledate(%affected) in %sem(%coaffected).', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_SET_STARTSEMESTER'), 'SEM_SET_STARTSEMESTER', 'Startsemester ändern', '%user hat in %sem(%affected) das Startsemester auf %semester(%coaffected) geändert.', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_SET_ENDSEMESTER'), 'SEM_SET_ENDSEMESTER', 'Semesterlaufzeit ändern', '%user hat in %sem(%affected) die Laufzeit auf %semester(%coaffected) geändert', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_ADD_CYCLE'), 'SEM_ADD_CYCLE', 'Regelmäßige Zeit hinzugefügt', '%user hat in %sem(%affected) die regelmäßige Zeit <em>%coaffected</em> hinzugefügt.', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_DELETE_CYCLE'), 'SEM_DELETE_CYCLE', 'Regelmäßige Zeit gelöscht', '%user hat in %sem(%affected) die regelmäßige Zeit <em>%coaffected</em> gelöscht.', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_ADD_SINGLEDATE'), 'SEM_ADD_SINGLEDATE', 'Einzeltermin hinzufügen', '%user hat in %sem(%affected) den Einzeltermin <em>%coaffected</em> hinzugefügt', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_DELETE_REQUEST'), 'SEM_DELETE_REQUEST', 'Raumanfrage gelöscht', '%user hat in %sem(%affected) die Raumanfrage für die gesamte Veranstaltung gelöscht.', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SEM_DELETE_SINGLEDATE_REQUEST'), 'SEM_DELETE_SINGLEDATE_REQUEST', 'Einzeltermin, Raumanfrage gelöscht', '%user hat in %sem(%affected) die Raumanfrage für den Termin <em>%coaffected</em> gelöscht.', 1, NULL);
        ");

                DBManager::get()->exec("
                    INSERT INTO `log_actions` VALUES (MD5('SINGLEDATE_CHANGE_TIME'), 'SINGLEDATE_CHANGE_TIME', 'Einzeltermin bearbeiten', '%user hat in %sem(%affected) den Einzeltermin %coaffected geändert.', 1, NULL);
        ");

        // move "RESOURCES_ENABLE" from config_local.inc.php to config table:
        if( $GLOBALS["RESOURCES_ENABLE"] ){
            // if "true", insert this as a local customization
            DBManager::get()->exec("
                INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
                VALUES ( 'dade8ea9ac4ec346e796ab9449d35b0e' , '', 'RESOURCES_ENABLE', '1', '0', 'boolean', 'global', '', '0', '0', '0', 'Enable the Stud.IP resource management module', '', '');
            ");
        }

        // RESOURCES_ENABLE default value (=false)
        DBManager::get()->exec("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES ( '06cdb765fb8f0853e3ebe08f51c3596e' , '', 'RESOURCES_ENABLE', '0', '1', 'boolean', 'global', '', '0', '0', '0', 'Enable the Stud.IP resource management module', '', '');
        ");

        DBManager::get()->exec("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES ( '93da66ca9e2d17df5bc61bd56406add7' , '', 'RESOURCES_ROOM_REQUEST_DEFAULT_ACTION', 'NO_ROOM_INFO_ACTION', '1', 'string', 'global', '', '0', '0', '0', 'Designates the pre-selected action for the room request dialog', 'Valid values are: NO_ROOM_INFO_ACTION, ROOM_REQUEST_ACTION, BOOKING_OF_ROOM_ACTION, FREETEXT_ROOM_ACTION', '');
        ");

        DBManager::get()->exec("
            INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
            VALUES ('0d3f84ed4dd6b7147b504ffb5b6fbc2c', '', 'RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW', '0', 1, 'boolean', 'global', '', 0, 12, 12, 'Enables the expert view of the course schedules', '', '');
        ");

        DBManager::get()->exec("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES ( 'bc3004618b17b29dc65e10e89be9a7a0', '', 'RESOURCES_ENABLE_BOOKINGSTATUS_COLORING', '1', '1', 'boolean', 'global', '', '0', '0', '0', 'Enable the colored presentation of the room booking status of a date', '', '');
        ");

        $this->write( get_class($this).": Finished with creating db schema.");

        // close logfile
        fclose($logfile_handle);
    }
}

