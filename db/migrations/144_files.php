<?php

/**
 * files.php
 *
 * Die Migration erzeugt alle Tabellen, die fuer den Betrieb des persoenlichen
 * Dateibereichs noetig sind. Dies sind:
 *
 * - die Kern-Tabellen zur Verwaltung der persoenlichen Verzeichnisse und
 *   Dateien
 *
 * - die Kern-Tabellen zur Verwaltung der vom Stud.IP-Systemadminstrator
 *   vorgenommenen Konfiguration des persoenlichen Dateibereichs und
 *
 * - die Kern-Tabelle zur Verwaltung der von einem Benutzer gewaehlten
 *   Anzeigeoptionen fuer "seinen" Dateimanager.
 *
 *  Zur physikalischen Speicherung persoenlicher Dateien auf dem lokalen Server
 *  richtet files das in der config_local.inc.php in der globalen Variablen
 *  "$USER_DOC_PATH" angegebene Verzeichnis ein.
 *
 * @category    Stud.IP
 * @version     3.1
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Stefan Osterloh <s.osterloh@uni-oldenburg.de>
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 */


class files extends Migration
{
    public function description()
    {
        return 'Setup db for personal file space';
    }

    public function up()
    {
        // Create directory
        if (empty($GLOBALS['USER_DOC_PATH'])) {
            throw new Exception('Config variable "USER_DOC_PATH" has not been set in file "config/config_local.inc.php". '
                               .'If you are upgrading to a newer release, please refer to the file "config/config_local.inc.php.dist" '
                               .'or just set it to default value of "$USER_DOC_PATH = $STUDIP_BASE_PATH . \'/data/user_doc\';".');
        }
        
        if (!file_exists($GLOBALS['USER_DOC_PATH'])) {
            $result = mkdir($GLOBALS['USER_DOC_PATH'], 0755, true);
            if ($result === false) {
                throw new Exception('Could not create user file path. Please check the config variable "USER_DOC_PATH" '
                                   .'in file "config/config_local.inc.php".');
            }
        }

        /*
         * Migration for API in lib/files
         */
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS files
            (file_id CHAR(32) NOT NULL,
            user_id CHAR(32) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            mime_type VARCHAR(64) NOT NULL,
            size BIGINT UNSIGNED NOT NULL,
            restricted TINYINT(1) NOT NULL DEFAULT 0,
            storage VARCHAR(32) NOT NULL DEFAULT 'DiskFileStorage',
            storage_id VARCHAR(32) NOT NULL,
            mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            chdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (file_id)) ENGINE=MyISAM");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS file_refs
            (id CHAR(32) NOT NULL,
            file_id CHAR(32) NOT NULL,
            parent_id CHAR(32) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            downloads INT NOT NULL DEFAULT 0,
            PRIMARY KEY (id)) ENGINE=MyISAM");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS files_backend_studip
            (id INT UNSIGNED NOT NULL,
            files_id VARCHAR(64) NOT NULL,
            path VARCHAR(256) NOT NULL,
            PRIMARY KEY (id)) ENGINE=MyISAM");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS files_backend_url
            (id INT UNSIGNED NOT NULL,
            files_id VARCHAR(64) NOT NULL,
            url VARCHAR(256) NOT NULL,
            PRIMARY KEY (id)) ENGINE=MyISAM");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS files_share
            (files_id VARCHAR(64) NOT NULL,
            entity_id VARCHAR(32) NOT NULL,
            description MEDIUMTEXT NULL,
            read_perm BOOLEAN DEFAULT FALSE,
            write_perm BOOLEAN DEFAULT FALSE,
            start_date INT UNSIGNED NOT NULL,
            end_date INT UNSIGNED NOT NULL,
            PRIMARY KEY (files_id, entity_id)) ENGINE=MyISAM");

        /*
         * Migration for the Admin-Area
         */
        DBManager::get()->query("CREATE  TABLE IF NOT EXISTS `doc_filetype`
            (`id` INT NOT NULL AUTO_INCREMENT ,
            `type` VARCHAR(45) NOT NULL ,
            `description` TEXT NULL ,
            PRIMARY KEY (`id`)) ENGINE=MyISAM"
        );

        DBManager::get()->query("CREATE  TABLE IF NOT EXISTS `doc_usergroup_config`
            (`id` INT NOT NULL AUTO_INCREMENT ,
            `usergroup` VARCHAR(45) NOT NULL ,
            `upload_quota` TEXT NOT NULL ,
            `upload_unit` VARCHAR(45) NULL ,
            `quota` TEXT NULL ,
            `quota_unit` VARCHAR(45) NULL ,
            `upload_forbidden` INT NOT NULL DEFAULT 0 ,
            `area_close` INT NOT NULL DEFAULT 0 ,
            `area_close_text` TEXT NULL ,
            `is_group_config` INT NOT NULL DEFAULT 0 ,
            PRIMARY KEY (`id`, `usergroup`)) ENGINE=MyISAM"
        );

        DBManager::get()->query("CREATE  TABLE IF NOT EXISTS `doc_filetype_forbidden`
            (`id` INT NOT NULL AUTO_INCREMENT ,
            `usergroup` VARCHAR(45) NOT NULL ,
            `dateityp_id` INT NOT NULL ,
            PRIMARY KEY (`id`) ,
            INDEX `fk_dateityp_verbot_nutzerbereich_2_idx` (`dateityp_id` ASC) ,
            INDEX `fk_dateityp_verbot_nutzerbereich_1_idx` (`usergroup` ASC)) ENGINE=MyISAM"
        );

        /*
         * Set the entry into the table "config" to enable or disable the Personal Document Area
         */
        $query = "INSERT IGNORE INTO `config`
                  (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`,
                   `mkdate`, `chdate`, `description`)
                  VALUES (:id, :field, :value, 1, :type, 'global', 'files', UNIX_TIMESTAMP(),
                          UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':id' => md5(uniqid('PERSONALDOCUMENT_ENABLE')),
            ':field' => 'PERSONALDOCUMENT_ENABLE',
            ':value' => (int) false,
            ':type' => 'boolean',
            ':description' => 'Aktiviert den persoenlichen Dateibereich',
        ]);

        $query = "INSERT IGNORE INTO `doc_usergroup_config`
                     (`id`, `usergroup`, `upload_quota`, `upload_unit`,
                      `quota`, `quota_unit`, `is_group_config`)
                   VALUES (:id, :group, :uploadQuota, :uploadUnit, :quota,
                           :quotaUnit, :isGroupConfig)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':id' => '1',
            ':group' => 'default',
            ':uploadQuota' => '5242880',
            ':uploadUnit' => 'MB',
            ':quota' => '52428800',
            ':quotaUnit' => 'MB',
            ':isGroupConfig' => '1'
        ]);

        $query = "INSERT IGNORE INTO `doc_filetype` (`type`) VALUES (:type)";

        $statement = DBManager::get()->prepare($query);
        foreach (words('exe com pif bat scr') as $value) {
            $statement->execute([
                ':type' => $value
            ]);
        }
    }

    public function down()
    {
        // Remove user directories
        $this->rm_rf($GLOBALS['USER_DOC_PATH']);

        // Remove db tables
        DBManager::get()->exec("DROP TABLE IF EXISTS
            `files`,
            `files_layout`,
            `files_backend_studip`,
            `files_backend_url`,
            `files_share`,
            `doc_usergroup_config`,
            `doc_filetype`,
            `doc_filetype_forbidden`
        ");

        // Remove config entry
        DBManager::get()->query("DELETE FROM config WHERE field IN ('PERSONALDOCUMENT_ENABLE')");
    }
    
    protected function rm_rf($path)
    {
        $files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->rm_rf($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }
 }
