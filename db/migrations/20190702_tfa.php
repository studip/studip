<?php
class Tfa extends Migration
{
    public function description()
    {
        return 'Creates tables for two factor authentication';
    }

    public function up()
    {
        // Create tables
        $query = "CREATE TABLE IF NOT EXISTS `users_tfa` (
                    `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `secret` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `confirmed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                    `type` ENUM('email', 'app') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'email',
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`user_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `users_tfa_tokens` (
                    `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `token` CHAR(6) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`user_id`, `token`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        // Add config entries (global and user)
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'TFA_MAX_TRIES', '3', 'integer', 'global',
                      'Zwei-Faktor-Authentifizierung', 'Maximale Anzahl fehlerhafter Versuche innerhalb eines Zeitraums',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'TFA_MAX_TRIES_TIMESPAN', '300', 'integer', 'global',
                      'Zwei-Faktor-Authentifizierung', 'Zeitraum in Sekunden, nach dem fehlerhafte Versuche vergessen werden',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      'TFA_PERMS', 'root', 'string', 'global',
                      'Zwei-Faktor-Authentifizierung', 'Systemrollen für die die Zwei-Faktor-Authentifizierung aktiviert ist (kommaseparierte Liste, mögliche Werte: autor, tutor, dozent, admin, root)',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DROP TABLE IF EXISTS `users_tfa`, `users_tfa_tokens`";
        DBManager::get()->exec($query);

        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'TFA_MAX_TRIES',
                      'TFA_MAX_TRIES_TIMESPAN',
                      'TFA_PERMS'
                  )";
        DBManager::get()->exec($query);
    }
}
