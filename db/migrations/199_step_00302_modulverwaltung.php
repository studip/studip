<?php

class Step00302Modulverwaltung extends Migration
{

    public function description()
    {
        return 'StEP00302 - Integrate module administration (aka MVV) as core plug-in.';
    }

    public function up() {

        $db = DBManager::get();

        $old_mvv = $db->query("SELECT * FROM plugins WHERE pluginclassname = 'MVVPlugin'")
                ->fetch(PDO::FETCH_ASSOC);

        if ($old_mvv) {
            $db->exec("UPDATE plugins SET pluginpath = 'core/Modulverwaltung' WHERE pluginclassname = 'MVVPlugin'");
            if ($old_mvv['pluginpath'] !== 'core/Modulverwaltung') {
                @rmdirr($GLOBALS['PLUGINS_PATH'] . '/' . $old_mvv['pluginpath']);
            }
        } else {
            //Installieren des Plugins
            $db->exec("INSERT INTO plugins
                SET pluginclassname = 'MVVPlugin',
                    pluginpath = 'core/Modulverwaltung',
                    pluginname = 'Modulverwaltung',
                    plugintype = 'SystemPlugin',
                    enabled = 'yes',
                    navigationpos = '1'");
            $plugin_id = $db->lastInsertId();
            $db->exec("INSERT IGNORE INTO roles_plugins (roleid, pluginid)
                    SELECT roleid, " . $db->quote($plugin_id) . " FROM roles WHERE system = 'y'");
        }


        $db->exec("
            ALTER TABLE `datafields`
            CHANGE `object_type` `object_type`
            ENUM('sem','inst','user','userinstrole','usersemdata','roleinstdata',
            'moduldeskriptor','modulteildeskriptor') NULL DEFAULT NULL
        ");

        $db->exec("
            ALTER TABLE `datafields` CHANGE `object_class` `object_class` VARCHAR(255) NULL DEFAULT NULL
        ");

        if (!$db->fetchOne("SHOW COLUMNS FROM sem_classes WHERE Field = 'module'")) {
            $db->exec("
                ALTER TABLE `sem_classes` ADD `module` TINYINT(4) NOT NULL AFTER `bereiche`
            ");
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_abschl_kategorie` (
                `kategorie_id` varchar(32) NOT NULL,
                `name` varchar(255) NOT NULL,
                `name_en` varchar(255) DEFAULT NULL,
                `name_kurz` varchar(50) DEFAULT NULL,
                `name_kurz_en` varchar(50) DEFAULT NULL,
                `beschreibung` text,
                `beschreibung_en` text,
                `position` int(11) DEFAULT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`kategorie_id`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_abschl_zuord` (
                `abschluss_id` varchar(32) NOT NULL,
                `kategorie_id` varchar(32) NOT NULL,
                `position` int(4) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`abschluss_id`),
                KEY `kategorie_id` (`kategorie_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_dokument` (
                `dokument_id` varchar(32) NOT NULL,
                `url` tinytext NOT NULL,
                `name` varchar(255) NOT NULL,
                `name_en` varchar(255) DEFAULT NULL,
                `linktext` varchar(255) NOT NULL,
                `linktext_en` varchar(255) DEFAULT NULL,
                `beschreibung` text,
                `beschreibung_en` text,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`dokument_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_dokument_zuord` (
                `dokument_id` varchar(32) NOT NULL,
                `range_id` varchar(32) NOT NULL,
                `object_type` varchar(50) NOT NULL,
                `position` int(3) NOT NULL DEFAULT '999',
                `kommentar` tinytext,
                `kommentar_en` tinytext,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`dokument_id`,`range_id`,`object_type`),
                KEY `range_id_object_type`(`range_id`,`object_type`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_fach_inst` (
                `fach_id` varchar(32) NOT NULL,
                `institut_id` varchar(32) NOT NULL,
                `position` int(11) NOT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`fach_id`,`institut_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_fachberater` (
                `stgteil_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `position` int(11) NOT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`stgteil_id`,`user_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_lvgruppe` (
                `lvgruppe_id` varchar(32) NOT NULL,
                `name` varchar(250) NOT NULL,
                `name_en` varchar(250) DEFAULT NULL,
                `alttext` tinytext,
                `alttext_en` tinytext,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`lvgruppe_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_lvgruppe_modulteil` (
                `lvgruppe_id` varchar(32) NOT NULL,
                `modulteil_id` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `fn_id` varchar(32) DEFAULT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`lvgruppe_id`,`modulteil_id`),
                KEY `fn_id` (`fn_id`),
                KEY `modulteil_id` (`modulteil_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_lvgruppe_seminar` (
                `lvgruppe_id` varchar(32) NOT NULL,
                `seminar_id` varchar(32) NOT NULL,
                `author_id` varchar(32) DEFAULT NULL,
                `editor_id` varchar(32) DEFAULT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`lvgruppe_id`,`seminar_id`),
                KEY `seminar_id` (`seminar_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modul` (
                `modul_id` varchar(32) NOT NULL,
                `quelle` varchar(120) DEFAULT NULL,
                `variante` varchar(32) DEFAULT NULL,
                `flexnow_modul` varchar(250) DEFAULT NULL,
                `code` varchar(250) DEFAULT NULL,
                `start` varchar(32) DEFAULT NULL,
                `end` varchar(32) DEFAULT NULL,
                `beschlussdatum` int(11) DEFAULT NULL,
                `fassung_nr` int(2) DEFAULT NULL,
                `fassung_typ` varchar(32) DEFAULT NULL,
                `version` varchar(120) NOT NULL DEFAULT '1',
                `dauer` varchar(50) DEFAULT NULL,
                `kapazitaet` varchar(50) NOT NULL DEFAULT '',
                `kp` int(11) DEFAULT NULL,
                `wl_selbst` int(11) DEFAULT NULL,
                `wl_pruef` int(11) DEFAULT NULL,
                `pruef_ebene` varchar(32) DEFAULT NULL,
                `faktor_note` varchar(10) NOT NULL DEFAULT '1',
                `stat` varchar(32) DEFAULT NULL,
                `kommentar_status` text,
                `verantwortlich` tinytext,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modul_id`),
                KEY `stat` (`stat`),
                KEY `flexnow_modul` (`flexnow_modul`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modul_deskriptor` (
                `deskriptor_id` varchar(32) NOT NULL,
                `modul_id` varchar(32) NOT NULL,
                `sprache` varchar(32) NOT NULL,
                `verantwortlich` tinytext,
                `bezeichnung` tinytext,
                `voraussetzung` text,
                `kompetenzziele` text,
                `inhalte` text,
                `literatur` text,
                `links` text,
                `kommentar` text,
                `turnus` tinytext,
                `kommentar_kapazitaet` text,
                `kommentar_sws` text,
                `kommentar_wl_selbst` text,
                `kommentar_wl_pruef` text,
                `kommentar_note` text,
                `pruef_vorleistung` text,
                `pruef_leistung` text,
                `pruef_wiederholung` text,
                `ersatztext` text,
                `author_id` varchar(32) DEFAULT NULL,
                `editor_id` varchar(32) DEFAULT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`deskriptor_id`),
                UNIQUE KEY `modul_id` (`modul_id`,`sprache`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modul_inst` (
                `modul_id` varchar(32) NOT NULL,
                `institut_id` varchar(32) NOT NULL,
                `gruppe` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modul_id`,`institut_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modul_language` (
                `modul_id` varchar(32) NOT NULL,
                `lang` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modul_id`,`lang`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modul_user` (
                `modul_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `gruppe` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modul_id`,`user_id`,`gruppe`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modulteil` (
                `modulteil_id` varchar(32) NOT NULL,
                `modul_id` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `flexnow_modul` varchar(250) DEFAULT NULL,
                `nummer` varchar(20) DEFAULT NULL,
                `num_bezeichnung` varchar(32) DEFAULT NULL,
                `lernlehrform` varchar(32) DEFAULT NULL,
                `semester` varchar(32) DEFAULT NULL,
                `kapazitaet` varchar(50) DEFAULT NULL,
                `kp` int(11) DEFAULT NULL,
                `sws` int(11) DEFAULT NULL,
                `wl_praesenz` int(11) DEFAULT NULL,
                `wl_bereitung` int(11) DEFAULT NULL,
                `wl_selbst` int(11) DEFAULT NULL,
                `wl_pruef` int(11) DEFAULT NULL,
                `anteil_note` int(11) DEFAULT NULL,
                `ausgleichbar` int(1) NOT NULL DEFAULT '0',
                `pflicht` int(2) NOT NULL DEFAULT '0',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modulteil_id`),
                KEY `modul_id` (`modul_id`),
                KEY `flexnow_modul` (`flexnow_modul`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modulteil_deskriptor` (
                `deskriptor_id` varchar(32) NOT NULL,
                `modulteil_id` varchar(32) NOT NULL,
                `bezeichnung` tinytext NOT NULL,
                `sprache` varchar(32) NOT NULL,
                `voraussetzung` text,
                `kommentar` text,
                `kommentar_kapazitaet` text,
                `kommentar_wl_praesenz` text,
                `kommentar_wl_bereitung` text,
                `kommentar_wl_selbst` text,
                `kommentar_wl_pruef` text,
                `pruef_vorleistung` text,
                `pruef_leistung` text,
                `kommentar_pflicht` text,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`deskriptor_id`),
                UNIQUE KEY `modulteil_id` (`modulteil_id`,`sprache`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modulteil_language` (
                `modulteil_id` varchar(32) NOT NULL,
                `lang` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modulteil_id`,`lang`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modulteil_stgteilabschnitt` (
                `modulteil_id` varchar(32) NOT NULL,
                `abschnitt_id` varchar(32) NOT NULL,
                `fachsemester` int(2) NOT NULL,
                `differenzierung` varchar(100) NOT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`modulteil_id`,`abschnitt_id`,`fachsemester`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_stg_stgteil` (
                `studiengang_id` varchar(32) NOT NULL,
                `stgteil_id` varchar(32) NOT NULL,
                `stgteil_bez_id` varchar(32) NOT NULL DEFAULT '',
                `position` int(11) NOT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`studiengang_id`,`stgteil_id`,`stgteil_bez_id`),
                KEY `stgteil_id` (`stgteil_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_stgteil` (
                `stgteil_id` varchar(32) NOT NULL,
                `fach_id` varchar(32) DEFAULT NULL,
                `kp` varchar(50) DEFAULT NULL,
                `semester` int(2) DEFAULT NULL,
                `zusatz` varchar(200) NOT NULL,
                `zusatz_en` varchar(200) DEFAULT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`stgteil_id`),
                KEY `fach_id` (`fach_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_stgteil_bez` (
                `stgteil_bez_id` varchar(32) NOT NULL,
                `name` varchar(100) NOT NULL,
                `name_en` varchar(100) NOT NULL,
                `name_kurz` varchar(20) NOT NULL,
                `name_kurz_en` varchar(20) NOT NULL,
                `position` int(4) NOT NULL DEFAULT '9999',
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`stgteil_bez_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_stgteilabschnitt` (
                `abschnitt_id` varchar(32) NOT NULL,
                `version_id` varchar(32) NOT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `name` varchar(200) NOT NULL,
                `name_en` varchar(200) DEFAULT NULL,
                `kommentar` varchar(200) DEFAULT NULL,
                `kommentar_en` varchar(200) DEFAULT NULL,
                `kp` int(11) DEFAULT NULL,
                `ueberschrift` tinytext,
                `ueberschrift_en` tinytext,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`abschnitt_id`),
                KEY `version_id` (`version_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_stgteilabschnitt_modul` (
                `abschnitt_id` varchar(32) NOT NULL,
                `modul_id` varchar(32) NOT NULL,
                `flexnow_modul` varchar(250) DEFAULT NULL,
                `modulcode` varchar(250) DEFAULT NULL,
                `position` int(11) NOT NULL DEFAULT '9999',
                `bezeichnung` varchar(250) DEFAULT NULL,
                `bezeichnung_en` varchar(250) DEFAULT NULL,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`abschnitt_id`,`modul_id`),
                KEY `flexnow_modul` (`flexnow_modul`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_stgteilversion` (
                `version_id` varchar(32) NOT NULL,
                `stgteil_id` varchar(32) NOT NULL,
                `start_sem` varchar(32) DEFAULT NULL,
                `end_sem` varchar(32) DEFAULT NULL,
                `code` varchar(100) DEFAULT NULL,
                `beschlussdatum` int(11) DEFAULT NULL,
                `fassung_nr` int(2) DEFAULT NULL,
                `fassung_typ` varchar(32) DEFAULT NULL,
                `beschreibung` text,
                `beschreibung_en` text,
                `stat` varchar(32) DEFAULT NULL,
                `kommentar_status` text,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`version_id`),
                KEY `stgteil_id` (`stgteil_id`),
                KEY `stat` (`stat`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `mvv_studiengang` (
                `studiengang_id` varchar(32) NOT NULL,
                `abschluss_id` varchar(32) DEFAULT NULL,
                `typ` enum('einfach','mehrfach') NOT NULL,
                `name` varchar(255) NOT NULL,
                `name_kurz` varchar(50) DEFAULT NULL,
                `name_kurz_en` varchar(50) DEFAULT NULL,
                `name_en` varchar(255) DEFAULT NULL,
                `beschreibung` text,
                `beschreibung_en` text,
                `institut_id` varchar(32) DEFAULT NULL,
                `start` varchar(32) DEFAULT NULL,
                `end` varchar(32) DEFAULT NULL,
                `beschlussdatum` int(11) DEFAULT NULL,
                `fassung_nr` int(2) DEFAULT NULL,
                `fassung_typ` varchar(32) DEFAULT NULL,
                `stat` varchar(32) DEFAULT NULL,
                `kommentar_status` text,
                `schlagworte` text,
                `author_id` varchar(32) NOT NULL,
                `editor_id` varchar(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`studiengang_id`),
                KEY `abschluss_id` (`abschluss_id`),
                KEY `institut_id` (`institut_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        $db->exec("
            INSERT IGNORE INTO config
            (config_id, field, value, is_default, `type`, `range`, section, mkdate, chdate, description, comment)
            VALUES
            (MD5('MVV_ACCESS_ASSIGN_LVGRUPPEN'), 'MVV_ACCESS_ASSIGN_LVGRUPPEN', 'admin', 1, 'string', 'global', 'modules',
             UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), 'Ab welchem Rechtestatus können Veranstaltungen Modulen (LV-Gruppen) zugeordnet werden. Bei Angabe von fakadmin darf nur dieser Zuordnungen vornehmen.', '')
        ");

        /**
         * New roles for the plug-in
         */
        $stmt = DBManager::get()->query("SELECT pluginid FROM plugins WHERE "
                . "pluginname = 'Modulverwaltung'");
        $plugin_id = $stmt->fetchColumn();
        $role_ids = array();

        if ($plugin_id !== false) {

            $db->exec("
                INSERT INTO `roles` (`rolename`, `system`)
                VALUES ('MVVAdmin', 'n');
            ");

            $db->exec("
                INSERT INTO `roles` (`rolename`, `system`)
                VALUES ('MVVFreigabe', 'n');
            ");

            $db->exec("
                INSERT INTO `roles` (`rolename`, `system`)
                VALUES ('MVVEntwickler', 'n');
            ");

            $db->exec("
                INSERT INTO `roles` (`rolename`, `system`)
                VALUES ('MVVRedakteur', 'n');
            ");

            $db->exec("
                INSERT INTO `roles` (`rolename`, `system`)
                VALUES ('MVVTranslator', 'n');
            ");

            $db->exec("
                INSERT INTO `roles` (`rolename`, `system`)
                VALUES ('MVVLvGruppenAdmin', 'n');
            ");

            $roles = RolePersistence::getAllRoles();
            $role_ids = array();
            foreach ($roles as $r) {
                if (in_array($r->getRolename(), words('MVVAdmin MVVFreigabe MVVEntwickler MVVRedakteur MVVTranslator MVVLvGruppenAdmin'))) {
                    $role_ids[] = $r->getRoleid();
                }
            }

            RolePersistence::assignPluginRoles($plugin_id, $role_ids);
        }

        StudipCacheFactory::getCache()->expire(RolePersistence::ROLES_CACHE_KEY);

        /**
         * Logging
         */
        StudipLog::registerActionPlugin('MVV_MODUL_NEW', 'MVV: Modul erstellen', '%user erstellt neues Modul %modul(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_UPDATE', 'MVV: Modul ändern', '%user ändert Modul %modul(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_DEL', 'MVV: Modul löschen', '%user löscht Modul %modul(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STUDIENGANG_NEW', 'MVV: Studiengang erstellen', '%user erstellt neuen Studiengang %stg(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STUDIENGANG_UPDATE', 'MVV: Studiengang ändern', '%user ändert Studiengang %stg(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STUDIENGANG_DEL', 'MVV: Studiengang löschen', '%user löscht Studiengang %stg(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STGTEIL_NEW', 'MVV: Studiengangteil erstellen', '%user erstellt neuen Studiengangteil %stgteil(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEIL_UPDATE', 'MVV: Studiengangteil ändern', '%user ändert Studiengangteil %stgteil(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEIL_DEL', 'MVV: Studiengangteil löschen', '%user löscht Studiengangteil %stgteil(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STGTEILVERSION_NEW', 'MVV: Studiengangteilversion erstellen', '%user erstellt neue Studiengangteilversion %version(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILVERSION_UPDATE', 'MVV: Studiengangteilversion ändern', '%user ändert Studiengangteilversion %version(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILVERSION_DEL', 'MVV: Studiengangteilversion löschen', '%user löscht Studiengangteilversion %version(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STGTEILBEZ_NEW', 'MVV: Studiengangteil-Bezeichnung erstellen', '%user erstellt neue Studiengangteil-Bezeichnung %stgteilbez(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILBEZ_UPDATE', 'MVV: Studiengangteil-Bezeichnung ändern', '%user ändert Studiengangteil-Bezeichnung %stgteilbez(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILBEZ_DEL', 'MVV: Studiengangteil-Bezeichnung löschen', '%user löscht Studiengangteil-Bezeichnung %stgteilbez(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_LVGRUPPE_NEW', 'MVV: LV-Gruppe erstellen', '%user erstellt neue LV-Gruppe %lvgruppe(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_LVGRUPPE_UPDATE', 'MVV: LV-Gruppe ändern', '%user ändert LV-Gruppe %lvgruppe(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_LVGRUPPE_DEL', 'MVV: LV-Gruppe löschen', '%user löscht LV-Gruppe %lvgruppe(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_FACH_NEW', 'MVV: Fach erstellen', '%user erstellt neues Fach %fach(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_FACH_UPDATE', 'MVV: Fach ändern', '%user ändert Fach %fach(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_FACH_DEL', 'MVV: Fach löschen', '%user löscht Fach %fach(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_ABSCHLUSS_NEW', 'MVV: Abschluss erstellen', '%user erstellt neuen Abschluss %abschluss(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_ABSCHLUSS_UPDATE', 'MVV: Abschluss ändern', '%user ändert Abschluss %abschluss(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_ABSCHLUSS_DEL', 'MVV: Abschluss löschen', '%user löscht Abschluss %abschluss(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_KATEGORIE_NEW', 'MVV: Abschluss-Kategorie erstellen', '%user erstellt neue Abschluss-Kategorie %abskategorie(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_KATEGORIE_UPDATE', 'MVV: Abschluss-Kategorie ändern', '%user ändert Abschluss-Kategorie %abskategorie(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_KATEGORIE_DEL', 'MVV: Abschluss-Kategorie löschen', '%user löscht Abschluss-Kategorie %abskategorie(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_DOKUMENT_NEW', 'MVV: Dokument erstellen', '%user erstellt neues Dokument %dokument(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_DOKUMENT_UPDATE', 'MVV: Dokument ändern', '%user ändert Dokument %dokument(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_DOKUMENT_DEL', 'MVV: Dokument löschen', '%user löscht Dokument %dokument(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STGTEILABS_NEW', 'MVV: Studiengangteilabschnitt erstellen', '%user erstellt neuen Studiengangteilabschnitt %stgteilabs(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILABS_UPDATE', 'MVV: Studiengangteilabschnitt ändern', '%user ändert Studiengangteilabschnitt %stgteilabs(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILABS_DEL', 'MVV: Studiengangteilabschnitt löschen', '%user löscht Studiengangteilabschnitt %stgteilabs(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_MODULTEIL_NEW', 'MVV: Modulteil erstellen', '%user erstellt neuen Modulteil %modulteil(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_UPDATE', 'MVV: Modulteil ändern', '%user ändert Modulteil %modulteil(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_DEL', 'MVV: Modulteil löschen', '%user löscht Modulteil %modulteil(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_MODULTEIL_DESK_NEW', 'MVV: Modulteil Deskriptor erstellen', '%user erstellt neuen Modulteil Deskriptor %modulteildesk(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_DESK_UPDATE', 'MVV: Modulteil Deskriptor ändern', '%user ändert Modulteil Deskriptor %modulteildesk(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_DESK_DEL', 'MVV: Modulteil Deskriptor löschen', '%user löscht Modulteil Deskriptor %modulteildesk(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_MODUL_DESK_NEW', 'MVV: Modul Deskriptor erstellen', '%user erstellt neuen Modul Deskriptor %moduldesk(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_DESK_UPDATE', 'MVV: Modul Deskriptor ändern', '%user ändert Modul Deskriptor %moduldesk(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_DESK_DEL', 'MVV: Modul Deskriptor löschen', '%user löscht Modul Deskriptor %moduldesk(%affected).', 'MVVPlugin');

        //Zuweisungstabellen
        StudipLog::registerActionPlugin('MVV_MODULINST_NEW', 'MVV: Modul-Einrichtung Beziehung erstellen', '%user weist dem Modul %modul(%affected) die Einrichtungen %inst(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULINST_DEL', 'MVV: Modul-Einrichtung Beziehung löschen', '%user löscht die Zuweisung der Einrichtungen %inst(%coaffected) zum Modul %modul(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULINST_UPDATE', 'MVV: Modul-Einrichtung Beziehung ändern', '%user ändert die Zuweisung der Einrichtungen %inst(%coaffected) zum Modul %modul(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_ABS_ZUORD_NEW', 'MVV: Abschluss-Kategorien Zuweisung erstellen', '%user weist den Abschluss %abschluss(%affected) der Kategorie %abskategorie(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_ABS_ZUORD_DEL', 'MVV: Abschluss-Kategorien Zuweisung  löschen', '%user löscht die Zuweisung des Abschlusses %abschluss(%affected) zur Kategorie %abskategorie(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_ABS_ZUORD_UPDATE', 'MVV: Abschluss-Kategorien Zuweisung  ändern', '%user ändert die Zuweisung des Abschlusses %abschluss(%affected) zur Kategorie %abskategorie(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_DOK_ZUORD_NEW', 'MVV: Dokumentzuordnung erstellen', '%user weist das Dokument %dokument(%affected) %object_type(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_DOK_ZUORD_DEL', 'MVV: Dokumentzuordnung löschen', '%user löscht die Zuweisung des Dokumentes %dokument(%affected) zu %object_type(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_DOK_ZUORD_UPDATE', 'MVV: Dokumentzuordnung ändern', '%user ändert die Zuweisung des Dokumentes %dokument(%affected) zu %object_type(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_FACHINST_NEW', 'MVV: Fach-Einrichtung Zuweisung erstellen', '%user weist das Fach %fach(%affected) der Einrichtung %inst(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_FACHINST_DEL', 'MVV: Fach-Einrichtung Zuweisung löschen', '%user löscht die Zuweisung des Faches %fach(%affected) zur Einrichtung %inst(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_FACHINST_UPDATE', 'MVV: Fach-Einrichtung Zuweisung ändern', '%user ändert die Zuweisung des Faches %fach(%affected) zur Einrichtung %inst(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_LVMODULTEIL_NEW', 'MVV: LV-Gruppe zu Modulteil Zuweisung erstellen', '%user weist der LV-Gruppe %lv(%affected) den Modulteil %modulteil(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_LVMODULTEIL_DEL', 'MVV: LV-Gruppe zu Modulteil Zuweisung löschen', '%user löscht die Zuweisung der LV-Gruppe %lv(%affected) zum Modulteil %modulteil(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_LVMODULTEIL_UPDATE', 'MVV: LV-Gruppe zu Modulteil Zuweisung ändern', '%user ändert die Zuweisung der LV-Gruppe %lv(%affected) zum Modulteil %modulteil(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_LVSEMINAR_NEW', 'MVV: LV-Gruppe zu Veranstaltung Zuweisung erstellen', '%user weist der LV-Gruppe %lvgruppe(%affected) der Veranstaltung %sem(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_LVSEMINAR_DEL', 'MVV: LV-Gruppe zu Veranstaltung Zuweisung löschen', '%user löscht die Zuweisung der LV-Gruppe %lvgruppe(%affected) zur Veranstaltung %sem(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_LVSEMINAR_UPDATE', 'MVV: LV-Gruppe zu Veranstaltung Zuweisung ändern', '%user ändert die Zuweisung der LV-Gruppe %lvgruppe(%affected) zur Veranstaltung %sem(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STGTEILABS_MODUL_NEW', 'MVV: Stgteilabschnitt-Modul Zuweisung erstellen', '%user weist dem Studiengangteilabschnitt %stgteilabs(%affected) dem Modul %Modul(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILABS_MODUL_DEL', 'MVV: Stgteilabschnitt-Modul Zuweisung löschen', '%user löscht die Zuweisung des Studiengangteilabschnitts %stgteilabs(%affected) zum Modul %modul(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STGTEILABS_MODUL_UPDATE', 'MVV: Stgteilabschnitt-Modul Zuweisung ändern', '%user ändert die Zuweisung des Studiengangteilabschnitts %stgteilabs(%affected) zum Modul %modul(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_MODULTEIL_LANG_NEW', 'MVV: Sprache zu Modulteil Zuweisung erstellen', '%user weist dem Modulteil %modulteil(%affected) die Unterrichtssprache %language(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_LANG_DEL', 'MVV: Sprache zu Modulteil Zuweisung löschen', '%user löscht die Zuweisung der Unterrichtssprache %language(%coaffected) zum Modulteil %modulteil(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_LANG_UPDATE', 'MVV: Sprache zu Modulteil Zuweisung ändern', '%user ändert die Zuweisung der Unterrichtssprache %language(%coaffected) zum Modulteil %modulteil(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_MODUL_LANG_NEW', 'MVV: Sprache zu Modul Zuweisung erstellen', '%user weist dem Modul %modul(%affected) die Unterrichtssprache %language(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_LANG_DEL', 'MVV: Sprache zu Modul Zuweisung löschen', '%user löscht die Zuweisung der Unterrichtssprache %language(%coaffected) zum Modul %modul(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_LANG_UPDATE', 'MVV: Sprache zu Modul Zuweisung ändern', '%user ändert die Zuweisung der Unterrichtssprache %language(%coaffected) zum Modul %modul(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_FACHBERATER_NEW', 'MVV: Person zu Fach Zuweisung erstellen', '%user weist dem Studiengangteil %stgteil(%affected) %user(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_FACHBERATER_DEL', 'MVV: Person zu Fach Zuweisung löschen', '%user löscht die Zuweisung von %user(%coaffected) zum Studiengangteil %stgteil(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_FACHBERATER_UPDATE', 'MVV: Person zu Fach Zuweisung ändern', '%user ändert die Zuweisung von %user(%coaffected) zum Studiengangteil %stgteil(%affected).', 'MVVPlugin');

        //3er Index
        StudipLog::registerActionPlugin('MVV_MODUL_USER_NEW', 'MVV: Person zu Modul Zuweisung erstellen', '%user weist dem Modul %modul(%affected) %user(%coaffected) als %gruppe zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_USER_DEL', 'MVV: Person zu Modul Zuweisung löschen', '%user löscht die Zuweisung von %user(%coaffected) als %gruppe zum Modul %modul(%affected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODUL_USER_UPDATE', 'MVV: Person zu Modul Zuweisung ändern', '%user ändert die Zuweisung von %user(%coaffected) als %gruppe zum Modul %modul(%affected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_MODULTEIL_STGTEILABS_NEW', 'MVV: Studiengangteilabschnitt zu Modulteil Zuweisung erstellen', '%user weist den Modulteil %modulteil(%affected) dem Studiengangteilabschnitt %stgteilabs(%coaffected) im %fachsem. Fachsemester zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_STGTEILABS_DEL', 'MVV: Studiengangteilabschnitt zu Modulteil Zuweisung löschen', '%user löscht die Zuweisung des Modulteils %modulteil(%affected) im %fachsem. des Studiengangteilabschnitt %stgteilabs(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_MODULTEIL_STGTEILABS_UPDATE', 'MVV: Studiengangteilabschnitt zu Modulteil Zuweisung ändern', '%user ändert die Zuweisung des Modulteils %modulteil(%affected) im %fachsem. des Studiengangteilabschnitt %stgteilabs(%coaffected).', 'MVVPlugin');

        StudipLog::registerActionPlugin('MVV_STG_STGTEIL_NEW', 'MVV: Studiengang zu Studiengangteil Zuweisung erstellen', '%user weist den Studiengang %stg(%affected) dem Studiengangteil %stgteil(%coaffected) zu.', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STG_STGTEIL_DEL', 'MVV: Studiengang zu Studiengangteil Zuweisung löschen', '%user löscht die Zuweisung des Studienganges %stg(%affected) zum Studiengangteil %stgteil(%coaffected).', 'MVVPlugin');
        StudipLog::registerActionPlugin('MVV_STG_STGTEIL_UPDATE', 'MVV: Studiengang zu Studiengangteil Zuweisung ändern', '%user ändert die Zuweisung des Studienganges %stg(%affected) zum Studiengangteil %stgteil(%coaffected).', 'MVVPlugin');

        // migrate table studiengaenge to fach
        $db->exec("RENAME TABLE `studiengaenge` TO `fach`");
        $db->exec("ALTER TABLE `fach` CHANGE `studiengang_id` `fach_id` "
                . "VARCHAR(32) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE `fach` CHANGE `name` `name` VARCHAR(255) NOT NULL");
        $db->exec("ALTER TABLE `fach` ADD `name_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name`, "
                . "ADD `name_kurz` VARCHAR(50) NULL DEFAULT NULL AFTER `name_en`, "
                . "ADD `name_kurz_en` VARCHAR(50) NULL DEFAULT NULL AFTER `name_kurz`");
        $db->exec("ALTER TABLE `fach` ADD `beschreibung_en` TINYTEXT NULL DEFAULT NULL AFTER `beschreibung`, "
                . "ADD `schlagworte` TEXT NULL DEFAULT NULL AFTER `beschreibung_en`, "
                . "ADD `author_id` VARCHAR(32) NOT NULL AFTER `schlagworte`, "
                . "ADD `editor_id` VARCHAR(32) NOT NULL AFTER `author_id`");

        // extend table abschluss
        $db->exec("ALTER TABLE `abschluss` ADD `name_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name`, "
                . "ADD `name_kurz` VARCHAR(50) NULL DEFAULT NULL AFTER `name_en`, "
                . "ADD `name_kurz_en` VARCHAR(50) NULL DEFAULT NULL AFTER `name_kurz`");
        $db->exec("ALTER TABLE `abschluss` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`, "
                . "ADD `author_id` VARCHAR(32) NOT NULL AFTER `beschreibung_en`, "
                . "ADD `editor_id` VARCHAR(32) NOT NULL AFTER `author_id`");

        // erweitert Tabelle user_studiengang um die optionale Angabe einer
        // Version des Studiengangs (genauer: Studiengangteils), Fremdschlüssel
        // aus Tabelle mvv_stgteilversion
        $db->exec("ALTER TABLE `user_studiengang` CHANGE `studiengang_id` `fach_id` "
                . "VARCHAR(32) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE `user_studiengang` ADD `version_id` VARCHAR(32) "
                . "NULL DEFAULT NULL AFTER `abschluss_id`");

        // Step 3: LVGroup Assign
        if (!CourseWizardStepRegistry::findByClassName('LVGroupsWizardStep')) {
            CourseWizardStepRegistry::registerStep('LVGruppen', 'LVGroupsWizardStep', 3, true);
        }
    }

    public function down() {

        $db = DBManager::get();

        // remove plug-in
        $db->exec("DELETE FROM plugins WHERE pluginpath = 'core/Modulverwaltung'");

        // delete datafiled entries
        $db->exec("DELETE FROM datafield_entrie INNER JOIN datafields "
                . "USING(datafields_id) WHERE object_type "
                . "IN('moduldeskriptor','modulteildeskriptor");

        // delete datafields for descriptors
        $db->exec("DELETE FROM datafields WHERE object_type "
                . "IN('moduldeskriptor','modulteildekriptor");

        // undo changes for datafields
        $db->exec("
            ALTER TABLE `datafields`
            CHANGE `object_type` `object_type`
            ENUM('sem','inst','user','userinstrole','usersemdata','roleinstdata') NULL DEFAULT NULL
        ");

        $db->exec("
            ALTER TABLE `datafields` CHANGE `object_class` `object_class` VARCHAR(255) NULL DEFAULT NULL
        ");

        // undo changes for sem classes
        $db->execute("
            ALTER TABLE `sem_classes` DROP `module`
        ");

        $db->exec("DROP TABLE IF EXISTS `mvv_abschl_kategorie`");
        $db->exec("DROP TABLE IF EXISTS `mvv_abschl_zuord`");
        $db->exec("DROP TABLE IF EXISTS `mvv_dokument`");
        $db->exec("DROP TABLE IF EXISTS `mvv_dokument_zuord`");
        $db->exec("DROP TABLE IF EXISTS `mvv_fachberater`");
        $db->exec("DROP TABLE IF EXISTS `mvv_fach_inst`");
        $db->exec("DROP TABLE IF EXISTS `mvv_lvgruppe`");
        $db->exec("DROP TABLE IF EXISTS `mvv_lvgruppe_modulteil`");
        $db->exec("DROP TABLE IF EXISTS `mvv_lvgruppe_seminar`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modul`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modulteil`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modulteil_deskriptor`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modulteil_language`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modulteil_stgteilabschnitt`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modul_deskriptor`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modul_inst`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modul_language`");
        $db->exec("DROP TABLE IF EXISTS `mvv_modul_user`");
        $db->exec("DROP TABLE IF EXISTS `mvv_stgteil`");
        $db->exec("DROP TABLE IF EXISTS `mvv_stgteilabschnitt`");
        $db->exec("DROP TABLE IF EXISTS `mvv_stgteilabschnitt_modul`");
        $db->exec("DROP TABLE IF EXISTS `mvv_stgteilversion`");
        $db->exec("DROP TABLE IF EXISTS `mvv_stgteil_bez`");
        $db->exec("DROP TABLE IF EXISTS `mvv_stg_stgteil`");
        $db->exec("DROP TABLE IF EXISTS `mvv_studiengang`");

        $db->exec("ALTER TABLE `fach` DROP `name_en`, DROP `name_kurz`, "
                . "DROP `name_kurz_en`, DROP `beschreibung_en`, "
                . "DROP `schlagworte`, DROP `author_id`, DROP `editor_id`");

        $db->exec("RENAME TABLE `fach` TO `studiengaenge`");

        $db->exec("ALTER TABLE `abschluss` DROP `name_en`, DROP `name_kurz`, "
                . "DROP `name_kurz_en`, DROP `beschreibung_en`, "
                . "DROP `author_id`, DROP `editor_id`");

        $db->exec("ALTER TABLE `user_studiengang` CHANGE `fach_id``studiengang_id` "
                . "VARCHAR(32) NOT NULL DEFAULT ''");

        $db->exec("ALTER TABLE `user_studiengang` DROP `version_id`");

        // remove course wizard step
        CourseWizardStepRegistry::deleteBySQL('classname = ?', ['LVGroupsWizardStep']);

        /**
         * Logging
         */
        StudipLog::unregisterAction('MVV_MODUL_NEW');
        StudipLog::unregisterAction('MVV_MODUL_UPDATE');
        StudipLog::unregisterAction('MVV_MODUL_DEL');

        StudipLog::unregisterAction('MVV_STUDIENGANG_NEW');
        StudipLog::unregisterAction('MVV_STUDIENGANG_UPDATE');
        StudipLog::unregisterAction('MVV_STUDIENGANG_DEL');

        StudipLog::unregisterAction('MVV_STGTEIL_NEW');
        StudipLog::unregisterAction('MVV_STGTEIL_UPDATE');
        StudipLog::unregisterAction('MVV_STGTEIL_DEL');

        StudipLog::unregisterAction('MVV_STGTEILVERSION_NEW');
        StudipLog::unregisterAction('MVV_STGTEILVERSION_UPDATE');
        StudipLog::unregisterAction('MVV_STGTEILVERSION_DEL');

        StudipLog::unregisterAction('MVV_STGTEILBEZ_NEW');
        StudipLog::unregisterAction('MVV_STGTEILBEZ_UPDATE');
        StudipLog::unregisterAction('MVV_STGTEILBEZ_DEL');

        StudipLog::unregisterAction('MVV_LVGRUPPE_NEW');
        StudipLog::unregisterAction('MVV_LVGRUPPE_UPDATE');
        StudipLog::unregisterAction('MVV_LVGRUPPE_DEL');

        StudipLog::unregisterAction('MVV_FACH_NEW');
        StudipLog::unregisterAction('MVV_FACH_UPDATE');
        StudipLog::unregisterAction('MVV_FACH_DEL');

        StudipLog::unregisterAction('MVV_ABSCHLUSS_NEW');
        StudipLog::unregisterAction('MVV_ABSCHLUSS_UPDATE');
        StudipLog::unregisterAction('MVV_ABSCHLUSS_DEL');

        StudipLog::unregisterAction('MVV_KATEGORIE_NEW');
        StudipLog::unregisterAction('MVV_KATEGORIE_UPDATE');
        StudipLog::unregisterAction('MVV_KATEGORIE_DEL');

        StudipLog::unregisterAction('MVV_DOKUMENT_NEW');
        StudipLog::unregisterAction('MVV_DOKUMENT_UPDATE');
        StudipLog::unregisterAction('MVV_DOKUMENT_DEL');

        StudipLog::unregisterAction('MVV_STGTEILABS_NEW');
        StudipLog::unregisterAction('MVV_STGTEILABS_UPDATE');
        StudipLog::unregisterAction('MVV_STGTEILABS_DEL');

        StudipLog::unregisterAction('MVV_MODULTEIL_NEW');
        StudipLog::unregisterAction('MVV_MODULTEIL_UPDATE');
        StudipLog::unregisterAction('MVV_MODULTEIL_DEL');

        StudipLog::unregisterAction('MVV_MODULTEIL_DESK_NEW');
        StudipLog::unregisterAction('MVV_MODULTEIL_DESK_UPDATE');
        StudipLog::unregisterAction('MVV_MODULTEIL_DESK_DEL');

        StudipLog::unregisterAction('MVV_MODUL_DESK_NEW');
        StudipLog::unregisterAction('MVV_MODUL_DESK_UPDATE');
        StudipLog::unregisterAction('MVV_MODUL_DESK_DEL');

        //Zuweisungstabellen
        StudipLog::unregisterAction('MVV_MODULINST_NEW');
        StudipLog::unregisterAction('MVV_MODULINST_UPDATE');
        StudipLog::unregisterAction('MVV_MODULINST_DEL');

        StudipLog::unregisterAction('MVV_ABS_ZUORD_NEW');
        StudipLog::unregisterAction('MVV_ABS_ZUORD_UPDATE');
        StudipLog::unregisterAction('MVV_ABS_ZUORD_DEL');

        StudipLog::unregisterAction('MVV_DOK_ZUORD_NEW');
        StudipLog::unregisterAction('MVV_DOK_ZUORD_UPDATE');
        StudipLog::unregisterAction('MVV_DOK_ZUORD_DEL');

        StudipLog::unregisterAction('MVV_FACHINST_NEW');
        StudipLog::unregisterAction('MVV_FACHINST_UPDATE');
        StudipLog::unregisterAction('MVV_FACHINST_DEL');

        StudipLog::unregisterAction('MVV_LVMODULTEIL_NEW');
        StudipLog::unregisterAction('MVV_LVMODULTEIL_UPDATE');
        StudipLog::unregisterAction('MVV_LVMODULTEIL_DEL');

        StudipLog::unregisterAction('MVV_LVSEMINAR_NEW');
        StudipLog::unregisterAction('MVV_LVSEMINAR_UPDATE');
        StudipLog::unregisterAction('MVV_LVSEMINAR_DEL');

        StudipLog::unregisterAction('MVV_STGTEILABS_MODUL_NEW');
        StudipLog::unregisterAction('MVV_STGTEILABS_MODUL_UPDATE');
        StudipLog::unregisterAction('MVV_STGTEILABS_MODUL_DEL');

        StudipLog::unregisterAction('MVV_MODULTEIL_LANG_NEW');
        StudipLog::unregisterAction('MVV_MODULTEIL_LANG_UPDATE');
        StudipLog::unregisterAction('MVV_MODULTEIL_LANG_DEL');

        StudipLog::unregisterAction('MVV_MODUL_LANG_NEW');
        StudipLog::unregisterAction('MVV_MODUL_LANG_UPDATE');
        StudipLog::unregisterAction('MVV_MODUL_LANG_DEL');

        StudipLog::unregisterAction('MVV_FACHBERATER_NEW');
        StudipLog::unregisterAction('MVV_FACHBERATER_UPDATE');
        StudipLog::unregisterAction('MVV_FACHBERATER_DEL');

        //3er Index
        StudipLog::unregisterAction('MVV_MODUL_USER_NEW');
        StudipLog::unregisterAction('MVV_MODUL_USER_UPDATE');
        StudipLog::unregisterAction('MVV_MODUL_USER_DEL');

        StudipLog::unregisterAction('MVV_MODULTEIL_STGTEILABS_NEW');
        StudipLog::unregisterAction('MVV_MODULTEIL_STGTEILABS_UPDATE');
        StudipLog::unregisterAction('MVV_MODULTEIL_STGTEILABS_DEL');

        StudipLog::unregisterAction('MVV_STG_STGTEIL_NEW');
        StudipLog::unregisterAction('MVV_STG_STGTEIL_UPDATE');
        StudipLog::unregisterAction('MVV_STG_STGTEIL_DEL');
    }

}
