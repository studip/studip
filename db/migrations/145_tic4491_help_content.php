<?php

/**
 * tic4491_help_content.php
 *
 * Die Migration erzeugt die Tabelle für Texte in der Helpbar.
 *  
 * @category    Stud.IP
 * @version     3.1
 *
 * @author      Arne Schröder <schroeder@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 */


class Tic4491HelpContent extends Migration
{
    public function description()
    {
        return 'Setup db table for helpbar texts';
    }

    public function up()
    {
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `help_content` (
                                    `content_id` char(32) NOT NULL,
                                    `language` char(2) NOT NULL DEFAULT 'de',
                                    `label` varchar(255) NOT NULL,
                                    `icon` varchar(255) NOT NULL,
                                    `content` text NOT NULL,
                                    `route` varchar(255) NOT NULL,
                                    `studip_version` varchar(32) NOT NULL,
                                    `position` tinyint(4) NOT NULL DEFAULT '1',
                                    `custom` tinyint(4) NOT NULL DEFAULT '0',
                                    `visible` tinyint(4) NOT NULL DEFAULT '1',
                                    `author_id` char(32) NOT NULL DEFAULT '',
                                    `installation_id` varchar(255) NOT NULL,
                                    `mkdate` int(11) unsigned NOT NULL,
                                    PRIMARY KEY (`route`,`studip_version`,`language`,`position`,`custom`)
                                ) ENGINE=MyISAM;");
    }

    public function down()
    {
        // Remove db tables
        DBManager::get()->exec("DROP TABLE IF EXISTS `help_content`;");
    }
 }
