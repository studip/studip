<?php
class OercampusIntegration extends Migration
{
    public function description()
    {
        return "Adds OERCampus to Stud.IP.";
    }

    public function up()
    {
        try {
            $statement = DBManager::get()->prepare("
                SELECT COUNT(*)
                FROM `lernmarktplatz_material`
            ");
            $statement->execute();
            $already_installed_plugin = $statement->fetch(PDO::FETCH_COLUMN, 0) > 0;
        } catch (Exception $e) {
            $already_installed_plugin = false;
        }

        if ($already_installed_plugin) {
            //test if all plugin tables are in place
            $oldtables = [
                'lernmarktplatz_abo',
                'lernmarktplatz_comments',
                'lernmarktplatz_downloadcounter',
                'lernmarktplatz_hosts',
                'lernmarktplatz_material_users',
                'lernmarktplatz_reviews',
                'lernmarktplatz_tags',
                'lernmarktplatz_tags_material',
                'lernmarktplatz_user'
            ];
            foreach ($oldtables as $tablename) {
                $query = "SHOW TABLES LIKE ? ";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$tablename]);
                if ($statement->rowCount() === 0) {
                    throw new Exception("Your OER Campus / Lernmarktplatz plugin is not in a current state. Uninstall or update it first, before you restart this migration.");
                }
            }
        }


        DBManager::get()->exec("
            ALTER TABLE `user_info`
            ADD COLUMN `oercampus_description` TEXT DEFAULT NULL
        ");

        DBManager::get()->exec("
            UPDATE `user_info`
            SET `oercampus_description` = (
                SELECT `content`
                FROM `datafields_entries`
                WHERE `datafield_id` = MD5('Lernmarktplatz-Beschreibung')
                    AND `datafields_entries`.`range_id` = `user_info`.`user_id`
                LIMIT 1
            )
        ");

        DBManager::get()->exec("
            ALTER TABLE blubber_external_contact
            CHANGE COLUMN `mail_identifier` `foreign_id` varchar(256) DEFAULT NULL,
            ADD COLUMN `host_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL AFTER `foreign_id`,
            ADD COLUMN `avatar_url` varchar(256) DEFAULT NULL AFTER `name`
        ");

        DBManager::get()->exec("
            RENAME TABLE `blubber_external_contact` TO `external_users`;
        ");

        if ($already_installed_plugin) {
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_abo` TO `oer_abo`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_comments` TO `oer_comments`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_downloadcounter` TO `oer_downloadcounter`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_hosts` TO `oer_hosts`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_material` TO `oer_material`;
            ");
            DBManager::get()->exec("
                ALTER TABLE `oer_material`
                CHANGE license license_identifier varchar(64) NOT NULL DEFAULT 'CC BY SA 3.0'
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_material_users` TO `oer_material_users`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_reviews` TO `oer_reviews`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_tags` TO `oer_tags`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_tags_material` TO `oer_tags_material`;
            ");
            DBManager::get()->exec("
                RENAME TABLE `lernmarktplatz_user` TO `oer_user`;
            ");

            $rename_config = DBManager::get()->prepare("
                UPDATE `config`
                SET `field` = :new
                WHERE `field` = :old
            ");
            $rename_config_values = DBManager::get()->prepare("
                UPDATE `config_values`
                SET `field` = :new
                WHERE `field` = :old
            ");

            $rename_config->execute([
                'old' => "LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD",
                'new' => "OER_USER_DESCRIPTION_DATAFIELD"
            ]);
            $rename_config_values->execute([
                'old' => "LERNMARKTPLATZ_USER_DESCRIPTION_DATAFIELD",
                'new' => "OER_USER_DESCRIPTION_DATAFIELD"
            ]);
            $rename_config->execute([
                'old' => "LERNMARKTPLATZ_PUBLIC_STATUS",
                'new' => "OER_PUBLIC_STATUS"
            ]);
            $rename_config_values->execute([
                'old' => "LERNMARKTPLATZ_PUBLIC_STATUS",
                'new' => "OER_PUBLIC_STATUS"
            ]);
            $rename_config->execute([
                'old' => "LERNMARKTPLATZ_DISABLE_LICENSE",
                'new' => "OER_DISABLE_LICENSE"
            ]);
            $rename_config_values->execute([
                'old' => "LERNMARKTPLATZ_DISABLE_LICENSE",
                'new' => "OER_DISABLE_LICENSE"
            ]);

            $rename_config->execute([
                'old' => "LERNMARKTPLATZ_TITLE",
                'new' => "OER_TITLE"
            ]);
            $rename_config_values->execute([
                'old' => "LERNMARKTPLATZ_TITLE",
                'new' => "OER_TITLE"
            ]);

            $query = "INSERT INTO `config`
                  SET `field` = :field,
                      `value` = :value,
                      `type` = :type,
                      `range` = :range,
                      `section` = :section,
                      `mkdate` = UNIX_TIMESTAMP(),
                      `chdate` = UNIX_TIMESTAMP(),
                      `description` = :description";
            $config_statement = DBManager::get()->prepare($query);

            $config_statement->execute([
                ':field'       => 'OERCAMPUS_ENABLED',
                ':value'       => '1',
                ':type'        => 'boolean',
                ':range'       => 'global',
                ':section'     => 'OERCampus',
                ':description' => 'Ist der OER Campus aktiviert?',
            ]);

            //Alte Dateien des Lernmarktplatzes umziehen:
            if (is_dir($GLOBALS['STUDIP_BASE_PATH'].'/data/lehrmarktplatz')) {
                foreach (scandir($GLOBALS['STUDIP_BASE_PATH']."/data/lehrmarktplatz") as $file) {
                    if ($file[0] !== ".") {
                        @rename(
                            $GLOBALS['STUDIP_BASE_PATH']."/data/lehrmarktplatz/".$file,
                            $GLOBALS['OER_PATH']."/".$file
                        );
                    }
                }
                @unlink($GLOBALS['STUDIP_BASE_PATH']."/data/lehrmarktplatz");
            }
            if (is_dir($GLOBALS['STUDIP_BASE_PATH'].'/data/lehrmarktplatz_images')) {
                foreach (scandir($GLOBALS['STUDIP_BASE_PATH']."/data/lehrmarktplatz_images") as $file) {
                    if ($file[0] !== ".") {
                        @rename(
                            $GLOBALS['STUDIP_BASE_PATH']."/data/lehrmarktplatz_images/".$file,
                            $GLOBALS['OER_LOGOS_PATH']."/".$file
                        );
                    }
                }
                @unlink($GLOBALS['STUDIP_BASE_PATH']."/data/lehrmarktplatz_images");
            }

        } else {
            DBManager::get()->exec("
                CREATE TABLE `oer_abo` (
                    `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    UNIQUE KEY `user_id` (`user_id`,`material_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_comments` (
                    `comment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `review_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `foreign_comment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `comment` text NOT NULL,
                    `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `chdate` bigint(20) NOT NULL,
                    `mkdate` bigint(20) NOT NULL,
                    PRIMARY KEY (`comment_id`),
                    KEY `review_id` (`review_id`),
                    KEY `foreign_comment_id` (`foreign_comment_id`),
                    KEY `host_id` (`host_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_downloadcounter` (
                    `counter_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `longitude` double DEFAULT NULL,
                    `latitude` double DEFAULT NULL,
                    `mkdate` int(11) DEFAULT NULL,
                    PRIMARY KEY (`counter_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_hosts` (
                    `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `name` varchar(64) NOT NULL,
                    `url` varchar(200) NOT NULL,
                    `public_key` text NOT NULL,
                    `private_key` text DEFAULT NULL,
                    `active` tinyint(4) NOT NULL DEFAULT 1,
                    `index_server` tinyint(4) NOT NULL DEFAULT 0,
                    `allowed_as_index_server` tinyint(4) NOT NULL DEFAULT 1,
                    `last_updated` bigint(20) NOT NULL,
                    `chdate` bigint(20) NOT NULL,
                    `mkdate` bigint(20) NOT NULL,
                    PRIMARY KEY (`host_id`),
                    UNIQUE KEY `url` (`url`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_material` (
                    `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `foreign_material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `name` varchar(64) NOT NULL,
                    `category` varchar(64) NOT NULL DEFAULT '',
                    `draft` tinyint(1) NOT NULL DEFAULT 0,
                    `filename` varchar(64) NOT NULL,
                    `short_description` varchar(100) DEFAULT NULL,
                    `description` text NOT NULL,
                    `difficulty_start` tinyint(12) NOT NULL DEFAULT 1,
                    `difficulty_end` tinyint(12) NOT NULL DEFAULT 12,
                    `player_url` varchar(256) DEFAULT NULL,
                    `tool` varchar(128) DEFAULT NULL,
                    `content_type` varchar(64) NOT NULL,
                    `front_image_content_type` varchar(64) DEFAULT NULL,
                    `structure` text DEFAULT NULL,
                    `rating` double DEFAULT NULL,
                    `license_identifier` varchar(64) NOT NULL DEFAULT 'CC BY SA 3.0',
                    `chdate` bigint(20) NOT NULL,
                    `mkdate` int(11) NOT NULL,
                    PRIMARY KEY (`material_id`),
                    KEY `host_id` (`host_id`),
                    KEY `category` (`category`),
                    KEY `foreign_material_id` (`foreign_material_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_material_users` (
                    `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `external_contact` int(11) NOT NULL DEFAULT 0,
                    `position` int(11) NOT NULL DEFAULT 1,
                    `chdate` int(11) NOT NULL,
                    `mkdate` int(11) NOT NULL,
                    PRIMARY KEY (`material_id`,`user_id`,`external_contact`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_reviews` (
                    `review_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `foreign_review_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                    `rating` int(11) NOT NULL,
                    `review` text NOT NULL,
                    `chdate` int(11) NOT NULL,
                    `mkdate` int(11) NOT NULL,
                    PRIMARY KEY (`review_id`),
                    UNIQUE KEY `unique_users` (`user_id`,`host_id`,`material_id`),
                    KEY `material_id` (`material_id`),
                    KEY `foreign_review_id` (`foreign_review_id`),
                    KEY `user_id` (`user_id`),
                    KEY `host_id` (`host_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_tags` (
                    `tag_hash` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `name` varchar(64) NOT NULL,
                    PRIMARY KEY (`tag_hash`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_tags_material` (
                    `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `tag_hash` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    UNIQUE KEY `unique_tags` (`material_id`,`tag_hash`),
                    KEY `tag_hash` (`tag_hash`),
                    KEY `material_id` (`material_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");
            DBManager::get()->exec("
                CREATE TABLE `oer_user` (
                    `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `foreign_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `name` varchar(100) NOT NULL,
                    `avatar` varchar(256) DEFAULT NULL,
                    `description` text DEFAULT NULL,
                    `chdate` int(11) NOT NULL,
                    `mkdate` int(11) NOT NULL,
                    PRIMARY KEY (`user_id`),
                    UNIQUE KEY `unique_users` (`foreign_user_id`,`host_id`),
                    KEY `foreign_user_id` (`foreign_user_id`),
                    KEY `host_id` (`host_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
            ");

            $query = "INSERT INTO `config`
                  SET `field` = :field,
                      `value` = :value,
                      `type` = :type,
                      `range` = :range,
                      `section` = :section,
                      `mkdate` = UNIX_TIMESTAMP(),
                      `chdate` = UNIX_TIMESTAMP(),
                      `description` = :description";
            $config_statement = DBManager::get()->prepare($query);

            $config_statement->execute([
                ':field'       => 'OERCAMPUS_ENABLED',
                ':value'       => '1',
                ':type'        => 'boolean',
                ':range'       => 'global',
                ':section'     => 'OERCampus',
                ':description' => 'Ist der OER Campus aktiviert?',
            ]);
            $config_statement->execute([
                ':field'       => 'OER_PUBLIC_STATUS',
                ':value'       => 'autor',
                ':type'        => 'string',
                ':range'       => 'global',
                ':section'     => 'OERCampus',
                ':description' => 'Ab welchem Nutzerstatus (nobody, user, autor, tutor, dozent) darf man den Marktplatz sehen?',
            ]);
            $config_statement->execute([
                ':field'       => 'OER_DISABLE_LICENSE',
                ':value'       => '0',
                ':type'        => 'boolean',
                ':range'       => 'global',
                ':section'     => 'OERCampus',
                ':description' => 'Sollen die Lizenzen deaktiviert / nicht angezeigt werden?',
            ]);
            $config_statement->execute([
                ':field'       => 'OER_TITLE',
                ':value'       => 'OER Campus',
                ':type'        => 'string',
                ':range'       => 'global',
                ':section'     => 'OERCampus',
                ':description' => 'Name des OER Campus in Stud.IP',
            ]);
        }

        //Adding licenses to Stud.IP:
        DBManager::get()->exec("
            CREATE TABLE `licenses` (
                `identifier` varchar(64) NOT NULL COMMENT 'According to SPDX standard if able.',
                `name` varchar(128) DEFAULT NULL,
                `link` varchar(256) DEFAULT NULL,
                `default` tinyint(1) DEFAULT 0,
                `description` text DEFAULT NULL,
                `chdate` int(11) DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`identifier`),
                KEY `default` (`default`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
        ");

        DBManager::get()->exec("
            ALTER TABLE `oer_material` ADD INDEX ( `license_identifier` )
        ");
        DBManager::get()->exec("
            UPDATE `oer_material`
            SET license_identifier = 'CC-BY-SA-3.0'
        ");


        DBManager::get()->exec("
            INSERT INTO `licenses` (`identifier`, `name`, `link`, `default`, `description`, `chdate`, `mkdate`)
            VALUES
                ('CC-BY-1.0','Creative Commons Attribution 1.0 Generic','https://creativecommons.org/licenses/by/1.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-2.0','Creative Commons Attribution 2.0 Generic','https://creativecommons.org/licenses/by/2.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-2.5','Creative Commons Attribution 2.5 Generic','https://creativecommons.org/licenses/by/2.5/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-3.0','Creative Commons Attribution 3.0 Unported','https://creativecommons.org/licenses/by/3.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-4.0','Creative Commons Attribution 4.0 International','https://creativecommons.org/licenses/by/4.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-SA-1.0','Creative Commons Attribution Share Alike 1.0 Generic','https://creativecommons.org/licenses/by-sa/1.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-SA-2.0','Creative Commons Attribution Share Alike 2.0 Generic','https://creativecommons.org/licenses/by-sa/2.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-SA-2.5','Creative Commons Attribution Share Alike 2.5 Generic','https://creativecommons.org/licenses/by-sa/2.5/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-SA-3.0','Creative Commons Attribution Share Alike 3.0 Unported','https://creativecommons.org/licenses/by-sa/3.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-BY-SA-4.0','Creative Commons Attribution Share Alike 4.0 International','https://creativecommons.org/licenses/by-sa/4.0/legalcode',1,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC-PDDC','Creative Commons Public Domain Dedication and Certification','https://creativecommons.org/licenses/publicdomain/',0,'Diese Lizenz ist nur sinnvoll, wenn Sie Material eintragen, das gemeinfrei ist. Gemeinfreie Materialien stammen von Autoren, die mindetens 80 Jahre tot sind, oder von Autoren, die im Ausland leben und ihre Werke unter die sogenannte Public Domain gestellt haben. Diese Lizenz ist nicht sinnvoll für Werke, bei denen ein Copyright besteht.',UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
                ('CC0-1.0','Creative Commons Zero v1.0 Universal','https://creativecommons.org/publicdomain/zero/1.0/legalcode',0,NULL,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());
        ");


    }


    public function down()
    {

    }

}
