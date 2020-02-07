<?php
class AdditionalMvvTables extends Migration
{
    public function description()
    {
        return 'Adds tables for the new mvv document and contact person structure.';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE IF NOT EXISTS `mvv_files` (
            `mvvfile_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `year` int(10) DEFAULT NULL,
            `type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `category` text COLLATE utf8mb4_unicode_ci,
            `tags` text COLLATE utf8mb4_unicode_ci,
            `extern_visible` tinyint(1) DEFAULT NULL,
            `author_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `editor_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,
            PRIMARY KEY (`mvvfile_id`)
          ) ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        $db->exec("CREATE TABLE IF NOT EXISTS `mvv_files_filerefs` (
            `mvvfile_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `file_language` varchar(32) COLLATE latin1_bin NOT NULL,
            `name` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
            `fileref_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `author_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `editor_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,
            PRIMARY KEY (`mvvfile_id`,`file_language`)
          ) ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        $db->exec("CREATE TABLE IF NOT EXISTS `mvv_files_ranges` (
            `mvvfile_id` VARCHAR(32) COLLATE latin1_bin NOT NULL ,
            `range_id` VARCHAR(32) COLLATE latin1_bin NOT NULL ,
            `range_type` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL ,
            `position` INT(10) NULL DEFAULT NULL ,
            `author_id` VARCHAR(32) COLLATE latin1_bin NULL DEFAULT NULL ,
            `editor_id` VARCHAR(32) COLLATE latin1_bin NULL DEFAULT NULL ,
            `mkdate` INT(11) NOT NULL ,
            `chdate` INT(11) NOT NULL ,
            PRIMARY KEY (`mvvfile_id`, `range_id`))
            ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        $db->exec("CREATE TABLE IF NOT EXISTS `mvv_contacts` (
            `contact_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `contact_status` enum('intern','extern','institution') COLLATE latin1_bin NOT NULL,
            `alt_mail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `author_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `editor_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,
            PRIMARY KEY (`contact_id`),
            KEY `contact_status` (`contact_status`))
            ENGINE = InnoDB ROW_FORMAT = DYNAMIC");
        
        $db->exec("CREATE TABLE IF NOT EXISTS `mvv_extern_contacts` (
            `extern_contact_id` VARCHAR(32) COLLATE latin1_bin NOT NULL,
            `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `vorname` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `homepage` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `mail` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `tel` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `author_id` VARCHAR(32) COLLATE latin1_bin DEFAULT NULL,
            `editor_id` VARCHAR(32) COLLATE latin1_bin DEFAULT NULL,
            `mkdate` INT(11) NOT NULL,
            `chdate` INT(11) NOT NULL,
            PRIMARY KEY (`extern_contact_id`))
            ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        $db->exec("CREATE TABLE IF NOT EXISTS `mvv_contacts_ranges` (
            `contact_range_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `contact_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `range_id` varchar(32) COLLATE latin1_bin NOT NULL,
            `range_type` enum('Modul','Studiengang','StudiengangTeil') COLLATE latin1_bin NOT NULL,
            `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
            `category` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
            `position` int(10) DEFAULT NULL,
            `author_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `editor_id` varchar(32) COLLATE latin1_bin DEFAULT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,
            PRIMARY KEY (`contact_range_id`),
            KEY `range_id` (`range_id`),
            KEY `range_type` (`range_type`),
            KEY `type` (`type`),
            KEY `category_range` (`category`,`range_id`),
            KEY `contact_id` (`contact_id`,`range_id`,`category`) USING BTREE
          ) ENGINE = InnoDB ROW_FORMAT = DYNAMIC");


        //Merge old mvv_dokument
        foreach ($db->query("SELECT * FROM `mvv_dokument`") as $old_doc) {
            $fileref_id = md5('FileRef'.  $old_doc['dokument_id']);
            $folder_id = md5('Folder'.  $old_doc['dokument_id']);
            $file_id = md5('File'.  $old_doc['dokument_id']);
            $mvvfile_id = md5('MvvFile'.  $old_doc['dokument_id']);
            $db->execute("INSERT IGNORE INTO `mvv_files` (`mvvfile_id`, `year`, `type`, `category`, `tags`, `extern_visible`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, NULL, NULL, NULL, NULL , 1, ?, ?, ?, ?)",
                    [$mvvfile_id, $old_doc['author_id'], $old_doc['editor_id'], $old_doc['mkdate'], $old_doc['chdate']]);
            $db->execute("INSERT IGNORE INTO `mvv_files_filerefs` (`mvvfile_id`, `file_language`, `name`, `fileref_id`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$mvvfile_id, 'DE', $old_doc['linktext'], $fileref_id, $old_doc['author_id'], $old_doc['editor_id'], $old_doc['mkdate'], $old_doc['chdate']]);
            $db->execute("INSERT IGNORE INTO `file_urls` (`file_id`, `url`, `access_type`) VALUES (?, ?, 'proxy')", [$file_id, $old_doc['url']]);
            $db->execute("INSERT IGNORE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`)
                        VALUES (?, ?, NULL, ?, ?, 'MVVFolder', ?, NULL, ?, ?, ?)",
                            [$folder_id, $old_doc['author_id'], $mvvfile_id, 'mvv', $old_doc['name'], $old_doc['linktext'], $old_doc['mkdate'], $old_doc['chdate']]);
            $db->execute("INSERT IGNORE INTO `file_refs` (`id`, `file_id`, `folder_id`, `description`, `user_id`, `name`, `mkdate`, `chdate`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$fileref_id, $file_id, $folder_id, $old_doc['beschreibung'], $old_doc['author_id'], $old_doc['name'], $old_doc['mkdate'], $old_doc['chdate']]);
        }

        //Merge old mvv_dokument_zuord
        foreach ($db->query("SELECT * FROM `mvv_dokument_zuord`") as $old_docrange) {
            $mvvfile_id = md5('MvvFile'.  $old_docrange['dokument_id']);
            $db->execute("INSERT IGNORE INTO `mvv_files_ranges` (`mvvfile_id`, `range_id`, `range_type`, `position`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                            [$mvvfile_id, $old_docrange['range_id'], $old_docrange['object_type'], $old_docrange['position'], $old_docrange['author_id'], $old_docrange['editor_id'], $old_docrange['mkdate'], $old_docrange['chdate']]);
        }
        $db->exec('DROP TABLE `mvv_dokument`');
        $db->exec('DROP TABLE `mvv_dokument_zuord`');

        //Merge old mvv_modul_user
        foreach ($db->query("SELECT * FROM `mvv_modul_user`") as $old_modul_user) {
            $contact_range_id = md5('MvvContactRange' .  $old_modul_user['user_id'] . $old_modul_user['modul_id']);
            $db->execute("INSERT IGNORE INTO `mvv_contacts` (`contact_id`, `contact_status`, `alt_mail`,
                        `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ? ,?)",
                        [
                            $old_modul_user['user_id'], 'intern', '', $old_modul_user['author_id'],
                            $old_modul_user['editor_id'], $old_modul_user['mkdate'], $old_modul_user['chdate']
                        ]);
            $db->execute("INSERT IGNORE INTO `mvv_contacts_ranges` (`contact_range_id`, `range_id`, `contact_id`, `range_type`, `type`, `category`, `position`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$contact_range_id, $old_modul_user['modul_id'], $old_modul_user['user_id'], 'Modul', '',
                        $old_modul_user['gruppe'],  $old_modul_user['position'], $old_modul_user['author_id'],
                        $old_modul_user['editor_id'], $old_modul_user['mkdate'], $old_modul_user['chdate']]);
        }
        $db->exec('DROP TABLE `mvv_modul_user`');
        
        // Merge old Fachberater
        foreach ($db->query("SELECT * FROM `mvv_fachberater`") as $old_fachberater) {
            $contact_range_id = md5('MvvContactRange' .  $old_fachberater['user_id'] . $old_fachberater['stgteil_id']);
            $db->execute("INSERT IGNORE INTO `mvv_contacts` (`contact_id`, `contact_status`, `alt_mail`,
                        `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ? ,?)",
                        [
                            $old_fachberater['user_id'], 'intern', '', $old_fachberater['author_id'],
                            $old_fachberater['editor_id'], $old_fachberater['mkdate'], $old_fachberater['chdate']
                        ]);
            $db->execute("INSERT IGNORE INTO `mvv_contacts_ranges` (`contact_range_id`, `range_id`, `contact_id`, `range_type`, `type`, `category`, `position`, `author_id`, `editor_id`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$contact_range_id, $old_fachberater['stgteil_id'], $old_fachberater['user_id'], 'StudiengangTeil', '',
                        'fachberater',  $old_fachberater['position'], $old_fachberater['author_id'],
                        $old_fachberater['editor_id'], $old_fachberater['mkdate'], $old_fachberater['chdate']]);
        }
        $db->exec('DROP TABLE `mvv_fachberater`');

        // datafields for study courses
        $db->exec("ALTER TABLE `datafields`
            CHANGE `object_type` `object_type`
            ENUM('sem','inst','user','userinstrole','usersemdata','roleinstdata',
            'moduldeskriptor','modulteildeskriptor','studycourse') NULL DEFAULT NULL");

        // switch to enable/disable studycourse info page
        $db->exec(
            "INSERT INTO `config` (`field`, `value`, `type`, `range`,
            `section`, `mkdate`, `chdate`, `description`)
            VALUES
            ('ENABLE_STUDYCOURSE_INFO_PAGE', '0', 'boolean', 'global',
            'global', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Shows an icon to open a dialog with studycourse informations in module search if true.')"
        );

        // new fields for study courses
        $db->exec("ALTER TABLE `mvv_studiengang`
            ADD `enroll` VARCHAR(50) NULL DEFAULT NULL AFTER `schlagworte`");
        $db->exec("ALTER TABLE `mvv_studiengang`
            ADD `abschlussgrad` VARCHAR(32) NULL DEFAULT NULL AFTER `schlagworte`");
        $db->exec("ALTER TABLE `mvv_studiengang`
            ADD `studienplaetze` INT UNSIGNED NULL DEFAULT NULL AFTER `schlagworte`");
        $db->exec("ALTER TABLE `mvv_studiengang`
            ADD `studienzeit` TINYINT UNSIGNED NULL DEFAULT NULL AFTER `schlagworte`");

        // postgraduate study courses (Aufbaustudiengänge)
        $db->exec("CREATE TABLE `mvv_aufbaustudiengang` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `grund_stg_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `aufbau_stg_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `typ` VARCHAR(32) COLLATE latin1_bin NOT NULL,
                `kommentar` TEXT NULL,
                `author_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `editor_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `mkdate` INT(11) NOT NULL,
                `chdate` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `grund_stg_id` (`grund_stg_id`,`aufbau_stg_id`)
            ) ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        // types of study courses
        $db->exec("CREATE TABLE `mvv_studycourse_type` (
                `studiengang_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `type` VARCHAR(32) COLLATE latin1_bin NOT NULL,
                `author_id` CHAR(32) COLLATE latin1_bin NULL,
                `editor_id` CHAR(32) COLLATE latin1_bin NULL,
                `mkdate` INT(11) NOT NULL,
                `chdate` INT(11) NOT NULL,
                PRIMARY KEY (`studiengang_id`, `type`)
            ) ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        // assigned languages to study course
        $db->execute("CREATE TABLE `mvv_studycourse_language` (
                `studiengang_id` char(32) COLLATE latin1_bin NOT NULL,
                `lang` varchar(32) COLLATE latin1_bin NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `author_id` char(32) COLLATE latin1_bin DEFAULT NULL,
                `editor_id` char(32) COLLATE latin1_bin DEFAULT NULL,
                `mkdate` int(11) NOT NULL,
                `chdate` int(11) NOT NULL,
                PRIMARY KEY (`studiengang_id`, `lang`)
            ) ENGINE = InnoDB ROW_FORMAT = DYNAMIC");

        // add index to speed up filters
        $db->execute("ALTER TABLE `mvv_modul_inst` ADD INDEX (`institut_id`)");
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE `mvv_files`, `mvv_files_filerefs`, `mvv_files_ranges`, `mvv_contacts`, `mvv_extern_contacts`");
        DBManager::get()->exec("DROP TABLE `mvv_aufbaustudiengang`, `mvv_studycourse_type`, `mvv_studycourse_language`");
        DBManager::get()->exec("ALTER TABLE `mvv_studiengang`
                                DROP `studienzeit`,
                                DROP `studienplaetze`,
                                DROP `abschlussgrad`,
                                DROP `enroll`;");
        DBManager::get()->exec("ALTER TABLE `datafields`
            CHANGE `object_type` `object_type`
            ENUM('sem','inst','user','userinstrole','usersemdata','roleinstdata',
            'moduldeskriptor','modulteildeskriptor') NULL DEFAULT NULL");
        DBManager::get()->exec(
            "DELETE FROM config WHERE field = 'ENABLE_STUDYCOURSE_INFO_PAGE'"
        );
        DBManager::get()->exec(
            "CREATE TABLE `mvv_fachberater` (
                `stgteil_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `position` int(11) NOT NULL,
                `author_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `editor_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`stgteil_id`,`user_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
        DBManager::get()->exec(
            "CREATE TABLE `mvv_modul_user` (
                `modul_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `gruppe` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `editor_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modul_id`,`user_id`,`gruppe`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;");
        DBManager::get()->exec(
            "CREATE TABLE `mvv_dokument` (
                `dokument_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `url` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
                `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `linktext` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `beschreibung` text COLLATE utf8mb4_unicode_ci,
                `author_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `editor_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`dokument_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
        DBManager::get()->exec(
            "CREATE TABLE `mvv_dokument_zuord` (
                `dokument_zuord_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `dokument_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `range_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `object_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                `position` int(3) NOT NULL DEFAULT '999',
                `kommentar` tinytext COLLATE utf8mb4_unicode_ci,
                `author_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `editor_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`dokument_zuord_id`),
                UNIQUE KEY `dokument_id` (`dokument_id`,`range_id`,`object_type`) USING BTREE,
                KEY `range_id_object_type` (`range_id`,`object_type`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
    }
}
