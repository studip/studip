<?php
/**
 * @author  Peter Thienel <thienel@data-quest.de>
 * @license GPL2 or any later version
 *
*/

class Step00315MvvI18n extends Migration
{
    public function description()
    {
        return 'migrates mvv localized fields to i18n api';
    }

    public function up()
    {
        $db = DBManager::get();

        // fach
        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `fach_id`, 'fach', 'name', 'en_GB', `name_en` "
                . "FROM `fach` WHERE IFNULL(`name_en`, '') != ''"
                );

        // name_kurz
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `fach_id`, 'fach', 'name_kurz', 'en_GB', `name_kurz_en` "
                . "FROM `fach` WHERE IFNULL(`name_kurz_en`, '') != ''"
                );

        // beschreibung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `fach_id`, 'fach', 'beschreibung', 'en_GB', `beschreibung_en` "
                . "FROM `fach` WHERE IFNULL(`beschreibung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `fach` DROP `name_en`, DROP `name_kurz_en`, DROP `beschreibung_en`");

        // abschluss
        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschluss_id`, 'abschluss', 'name', 'en_GB', `name_en` "
                . "FROM `abschluss` WHERE IFNULL(`name_en`, '') != ''"
                );

        // name_kurz
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschluss_id`, 'abschluss', 'name_kurz', 'en_GB', `name_kurz_en` "
                . "FROM `abschluss` WHERE IFNULL(`name_kurz_en`, '') != ''"
                );

        // beschreibung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschluss_id`, 'abschluss', 'beschreibung', 'en_GB', `beschreibung_en` "
                . "FROM `abschluss` WHERE IFNULL(`beschreibung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `abschluss` DROP `name_en`, DROP `name_kurz_en`, DROP `beschreibung_en`");


        // mvv_abschl_kategorie
        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `kategorie_id`, 'mvv_abschl_kategorie', 'name', 'en_GB', `name_en` "
                . "FROM `mvv_abschl_kategorie` WHERE IFNULL(`name_en`, '') != ''"
                );

        // name_kurz
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `kategorie_id`, 'mvv_abschl_kategorie', 'name_kurz', 'en_GB', `name_kurz_en` "
                . "FROM `mvv_abschl_kategorie` WHERE IFNULL(`name_kurz_en`, '') != ''"
                );

        // beschreibung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `kategorie_id`, 'mvv_abschl_kategorie', 'beschreibung', 'en_GB', `beschreibung_en` "
                . "FROM `mvv_abschl_kategorie` WHERE IFNULL(`beschreibung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_abschl_kategorie` DROP `name_en`, DROP `name_kurz_en`, DROP `beschreibung_en`");


        // mvv_dokument
        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `dokument_id`, 'mvv_dokument', 'name', 'en_GB', `name_en` "
                . "FROM `mvv_dokument` WHERE IFNULL(`name_en`, '') != ''"
                );

        // linktext
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `dokument_id`, 'mvv_dokument', 'linktext', 'en_GB', `linktext_en` "
                . "FROM `mvv_dokument` WHERE IFNULL(`linktext_en`, '') != ''"
                );

        // beschreibung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `dokument_id`, 'mvv_dokument', 'beschreibung', 'en_GB', `beschreibung_en` "
                . "FROM `mvv_dokument` WHERE IFNULL(`beschreibung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_dokument` DROP `name_en`, DROP `linktext_en`, DROP `beschreibung_en`");


        // mvv_dokument_zuord

        // add an own primary key to simplify i18n (used as foreign key in i18n)
        $db->execute("ALTER TABLE `mvv_dokument_zuord` ADD `dokument_zuord_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL FIRST");
        $db->execute("ALTER TABLE `mvv_dokument_zuord` DROP PRIMARY KEY, ADD UNIQUE (`dokument_id`, `range_id`, `object_type`) USING BTREE");
        $dokument_zuordnungen = $db->query("SELECT * FROM `mvv_dokument_zuord` WHERE 1");
        $is_unique = $db->prepare("SELECT `dokument_zuord_id` FROM `mvv_dokument_zuord` WHERE `dokument_zuord_id` = ?");
        foreach ($dokument_zuordnungen->fetchAll() as $dokument_zuordnung) {
            do {
                $id = md5(uniqid('mvv_dokument_zuord', 1));
                $is_unique->execute([$id]);
            } while ($is_unique->fetch());
            $db->execute("UPDATE `mvv_dokument_zuord` SET `dokument_zuord_id` = ? "
                    . "WHERE `dokument_id` = ? AND `range_id` = ? AND `object_type` = ?",
                    [$id, $dokument_zuordnung['dokument_id'], $dokument_zuordnung['range_id'],
                        $dokument_zuordnung['object_type']]);
        }
        $db->execute("ALTER TABLE `mvv_dokument_zuord` ADD PRIMARY KEY(`dokument_zuord_id`)");

        // kommentar
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `dokument_zuord_id`, 'mvv_dokument_zuord', 'kommentar', 'en_GB', `kommentar_en` "
                . "FROM `mvv_dokument_zuord` WHERE IFNULL(`kommentar_en`, '') != ''"
                );
        $db->execute("ALTER TABLE `mvv_dokument_zuord` DROP `kommentar_en`");


        // mvv_lvgruppe
        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `lvgruppe_id`, 'mvv_lvgruppe', 'name', 'en_GB', `name_en` "
                . "FROM `mvv_lvgruppe` WHERE IFNULL(`name_en`, '') != ''"
                );

        // alttext
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `lvgruppe_id`, 'mvv_lvgruppe', 'alttext', 'en_GB', `alttext_en` "
                . "FROM `mvv_lvgruppe` WHERE IFNULL(`alttext_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_lvgruppe` DROP `name_en`, DROP `alttext_en`");


        // mvv_modulteil_deskriptor

        $deskriptoren = $db->query("SELECT `mmd1`.*, `mmd2`.`deskriptor_id` AS `base_id` "
                . "FROM `mvv_modulteil_deskriptor` `mmd1` "
                . "LEFT JOIN `mvv_modulteil_deskriptor` `mmd2` "
                . "ON `mmd1`.`modulteil_id` = `mmd2`.`modulteil_id` AND `mmd2`.`sprache` = 'DE' "
                . "WHERE `mmd1`.`sprache` != 'DE'");
        $stmt = $db->prepare("INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "VALUES(?, 'mvv_modulteil_deskriptor', ?, 'en_GB', ?)");
        foreach ($deskriptoren->fetchAll() as $deskriptor) {
            $fields = ['bezeichnung', 'voraussetzung', 'kommentar', 'kommentar_kapazitaet',
                'kommentar_wl_praesenz', 'kommentar_wl_bereitung', 'kommentar_wl_selbst',
                'kommentar_wl_pruef', 'pruef_vorleistung', 'pruef_leistung', 'kommentar_pflicht'];
            foreach ($fields as $field) {
                if (trim($deskriptor[$field])) {
                    $stmt->execute([$deskriptor['base_id'], $field, $deskriptor[$field]]);
                }
            }
        }

        $db->execute("DELETE FROM `mvv_modulteil_deskriptor` WHERE sprache != 'DE'");

        $db->execute("ALTER TABLE `mvv_modulteil_deskriptor` DROP INDEX `modulteil_id`");
        $db->execute("ALTER TABLE `mvv_modulteil_deskriptor` DROP `sprache`");
        $db->execute("ALTER TABLE `mvv_modulteil_deskriptor` ADD INDEX `modulteil_id` (`modulteil_id`)");


        // mvv_modul_deskriptor

        $deskriptoren = $db->query("SELECT `mmd1`.*, `mmd2`.`deskriptor_id` AS `base_id` "
                . "FROM `mvv_modul_deskriptor` `mmd1` "
                . "LEFT JOIN `mvv_modul_deskriptor` `mmd2` "
                . "ON `mmd1`.`modul_id` = `mmd2`.`modul_id` AND `mmd2`.`sprache` = 'DE' "
                . "WHERE `mmd1`.`sprache` != 'DE'");
        $stmt = $db->prepare("INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "VALUES(?, 'mvv_modul_deskriptor', ?, 'en_GB', ?)");
        foreach ($deskriptoren->fetchAll(PDO::FETCH_ASSOC) as $deskriptor) {
            $fields = ['verantwortlich', 'bezeichnung', 'voraussetzung', 'kompetenzziele',
                'inhalte', 'literatur', 'links', 'kommentar', 'turnus', 'kommentar_kapazitaet',
                'kommentar_sws', 'kommentar_wl_selbst', 'kommentar_wl_pruef', 'kommentar_note',
                'pruef_vorleistung', 'pruef_leistung', 'pruef_wiederholung', 'ersatztext'];
            foreach ($fields as $field) {
                if (trim($deskriptor[$field])) {
                    $stmt->execute([$deskriptor['base_id'], $field, $deskriptor[$field]]);
                }
            }
        }

        $db->execute("DELETE FROM `mvv_modul_deskriptor` WHERE `sprache` != 'DE'");
        $db->execute("ALTER TABLE `mvv_modul_deskriptor` DROP INDEX `modul_id`");
        $db->execute("ALTER TABLE `mvv_modul_deskriptor` DROP `sprache`");
        $db->execute("ALTER TABLE `mvv_modul_deskriptor` ADD INDEX `modul_id` (`modul_id`)");


        // mvv_stgteil

        // zusatz
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `stgteil_id`, 'mvv_stgteil', 'zusatz', 'en_GB', `zusatz_en` "
                . "FROM `mvv_stgteil` WHERE IFNULL(`zusatz_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_stgteil` DROP `zusatz_en`");


        // mvv_stgteilabschnitt

        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschnitt_id`, 'mvv_stgteilabschnitt', 'name', 'en_GB', `name_en` "
                . "FROM `mvv_stgteilabschnitt` WHERE IFNULL(`name_en`, '') != ''"
                );

        // kommentar
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschnitt_id`, 'mvv_stgteilabschnitt', 'kommentar', 'en_GB', `kommentar_en` "
                . "FROM `mvv_stgteilabschnitt` WHERE IFNULL(`kommentar_en`, '') != ''"
                );

        // ueberschrift
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschnitt_id`, 'mvv_stgteilabschnitt', 'ueberschrift', 'en_GB', `ueberschrift_en` "
                . "FROM `mvv_stgteilabschnitt` WHERE IFNULL(`ueberschrift_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_stgteilabschnitt` DROP `name_en`, DROP `kommentar_en`, DROP `ueberschrift_en`");


        // mvv_stgteilabschnitt_modul

        // add an own primary key to simplify i18n (used as foreign key in i18n)
        $db->execute("ALTER TABLE `mvv_stgteilabschnitt_modul` ADD `abschnitt_modul_id` VARCHAR(32)  CHARACTER SET latin1 COLLATE latin1_bin NOT NULL FIRST");
        $db->execute("ALTER TABLE `mvv_stgteilabschnitt_modul` DROP PRIMARY KEY, ADD UNIQUE (`abschnitt_id`, `modul_id`) USING BTREE");
        $abschnitt_modul = $db->query("SELECT * FROM `mvv_stgteilabschnitt_modul` WHERE 1");
        $is_unique = $db->prepare("SELECT `abschnitt_modul_id` FROM `mvv_stgteilabschnitt_modul` WHERE `abschnitt_modul_id` = ?");
        foreach ($abschnitt_modul->fetchAll() as $abs_mod) {
            do {
                $id = md5(uniqid('mvv_stgteilabschnitt_modul', 1));
                $is_unique->execute([$id]);
            } while ($is_unique->fetch());
            $db->execute("UPDATE `mvv_stgteilabschnitt_modul` SET `abschnitt_modul_id` = ? WHERE `abschnitt_id` = ? AND `modul_id` = ?",
                    [$id, $abs_mod['abschnitt_id'], $abs_mod['modul_id']]);
        }
        $db->execute("ALTER TABLE `mvv_stgteilabschnitt_modul` ADD PRIMARY KEY(`abschnitt_modul_id`)");

        // bezeichnung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `abschnitt_modul_id`, 'mvv_stgteilabschnitt_modul', 'bezeichnung', 'en_GB', `bezeichnung_en` "
                . "FROM `mvv_stgteilabschnitt_modul` WHERE IFNULL(`bezeichnung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_stgteilabschnitt_modul` DROP `bezeichnung_en`");


        // mvv_stgteilversion

        // beschreibung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `version_id`, 'mvv_stgteilversion', 'beschreibung', 'en_GB', `beschreibung_en` "
                . "FROM `mvv_stgteilversion` WHERE IFNULL(`beschreibung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_stgteilversion` DROP `beschreibung_en`");


        // mvv_stgteil_bez

        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `stgteil_bez_id`, 'mvv_stgteil_bez', 'name', 'en_GB', `name_en` "
                . "FROM `mvv_stgteil_bez` WHERE IFNULL(`name_en`, '') != ''"
                );

        // name_kurz
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `stgteil_bez_id`, 'mvv_stgteil_bez', 'name_kurz', 'en_GB', `name_kurz_en` "
                . "FROM `mvv_stgteil_bez` WHERE IFNULL(`name_kurz_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_stgteil_bez` DROP `name_en`, DROP `name_kurz_en`");

        // mvv_studiengang

        // name
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `studiengang_id`, 'mvv_studiengang', 'name', 'en_GB', `name_en` "
                . "FROM `mvv_studiengang` WHERE IFNULL(`name_en`, '') != ''"
                );

        // name_kurz
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `studiengang_id`, 'mvv_studiengang', 'name_kurz', 'en_GB', `name_kurz_en` "
                . "FROM `mvv_studiengang` WHERE IFNULL(`name_kurz_en`, '') != ''"
                );

        // beschreibung
        $db->execute(
                "INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) "
                . "SELECT `studiengang_id`, 'mvv_studiengang', 'beschreibung', 'en_GB', `beschreibung_en` "
                . "FROM `mvv_studiengang` WHERE IFNULL(`beschreibung_en`, '') != ''"
                );

        $db->execute("ALTER TABLE `mvv_studiengang` DROP `name_kurz_en`, DROP `name_en`, DROP `beschreibung_en`");


        // make datafields i18ed
        $db->exec("ALTER TABLE datafields
            CHANGE `type` `type` ENUM('bool','textline','textlinei18n','textarea',
                'textareai18n','textmarkup','textmarkupi18n','selectbox',
                'date','time','email','phone','radio','combo','link','selectboxmultiple')
            CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'textline'");
        $db->execute("ALTER TABLE `datafields_entries`
                      ADD `lang` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '' AFTER `sec_range_id`");
        $db->execute('ALTER TABLE `datafields_entries` '
                . 'DROP PRIMARY KEY, ADD PRIMARY KEY (`datafield_id`, `range_id`, `sec_range_id`, `lang`)');
    }

    public function down()
    {
        $db = DBManager::get();

        // fach
        // name_en
        $db->execute('ALTER TABLE `fach` ADD `name_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `fach`
                LEFT JOIN `i18n` ON (`fach`.`fach_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'fach' AND `field` = 'name' AND `lang` = 'en_GB'");

        // name_kurz
        $db->execute('ALTER TABLE `fach` ADD `name_kurz_en` VARCHAR(50) NULL DEFAULT NULL AFTER `name_kurz`');
        $db->execute("UPDATE `fach`
                LEFT JOIN `i18n` ON (`fach`.`fach_id` = `i18n`.`object_id`)
                SET `name_kurz_en` = `value`
                WHERE `table` = 'fach' AND `field` = 'name_kurz' AND `lang` = 'en_GB'");

        // beschreibung
        $db->execute('ALTER TABLE `fach` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`');
        $db->execute("UPDATE `fach`
                LEFT JOIN `i18n` ON (`fach`.`fach_id` = `i18n`.`object_id`)
                SET `beschreibung_en` = `value`
                WHERE `table` = 'fach' AND `field` = 'beschreibung' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'fach'");


        // abschluss
        // name
        $db->execute('ALTER TABLE `abschluss` ADD `name_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `abschluss`
                LEFT JOIN `i18n` ON (`abschluss`.`abschluss_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'abschluss' AND `field` = 'name' AND `lang` = 'en_GB'");

        // name_kurz
        $db->execute('ALTER TABLE `abschluss` ADD `name_kurz_en` VARCHAR(50) NULL DEFAULT NULL AFTER `name_kurz`');
        $db->execute("UPDATE `abschluss`
                LEFT JOIN `i18n` ON (`abschluss`.`abschluss_id` = `i18n`.`object_id`)
                SET `name_kurz_en` = `value`
                WHERE `table` = 'abschluss' AND `field` = 'name_kurz' AND `lang` = 'en_GB'");

        // beschreibung
        $db->execute('ALTER TABLE `abschluss` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`');
        $db->execute("UPDATE `abschluss`
                LEFT JOIN `i18n` ON (`abschluss`.`abschluss_id` = `i18n`.`object_id`)
                SET `beschreibung_en` = `value`
                WHERE `table` = 'abschluss' AND `field` = 'beschreibung' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'abschluss'");


        // mvv_abschl_kategorie
        // name
        $db->execute('ALTER TABLE `mvv_abschl_kategorie` ADD `name_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `mvv_abschl_kategorie`
                LEFT JOIN `i18n` ON (`mvv_abschl_kategorie`.`kategorie_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'mvv_abschl_kategorie' AND `field` = 'name' AND `lang` = 'en_GB'");

        // name_kurz
        $db->execute('ALTER TABLE `mvv_abschl_kategorie` ADD `name_kurz_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name_kurz`');
        $db->execute("UPDATE `mvv_abschl_kategorie`
                LEFT JOIN `i18n` ON (`mvv_abschl_kategorie`.`kategorie_id` = `i18n`.`object_id`)
                SET `name_kurz_en` = `value`
                WHERE `table` = 'mvv_abschl_kategorie' AND `field` = 'name_kurz' AND `lang` = 'en_GB'");

        // beschreibung
        $db->execute('ALTER TABLE `mvv_abschl_kategorie` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`');
        $db->execute("UPDATE `mvv_abschl_kategorie`
                LEFT JOIN `i18n` ON (`mvv_abschl_kategorie`.`kategorie_id` = `i18n`.`object_id`)
                SET `beschreibung_en` = `value`
                WHERE `table` = 'mvv_abschl_kategorie' AND `field` = 'beschreibung' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_abschl_kategorie'");


        // mvv_dokument
        // name
        $db->execute('ALTER TABLE `mvv_dokument` ADD `name_en` VARCHAR(255) NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `mvv_dokument`
                LEFT JOIN `i18n` ON (`mvv_dokument`.`dokument_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'mvv_dokument' AND `field` = 'name' AND `lang` = 'en_GB'");

        // linktext
        $db->execute('ALTER TABLE `mvv_dokument` ADD `linktext_en` VARCHAR(255) NULL DEFAULT NULL AFTER `linktext`');
        $db->execute("UPDATE `mvv_dokument`
                LEFT JOIN `i18n` ON (`mvv_dokument`.`dokument_id` = `i18n`.`object_id`)
                SET `linktext_en` = `value`
                WHERE `table` = 'mvv_dokument' AND `field` = 'linktext' AND `lang` = 'en_GB'");

        // beschreibung
        $db->execute('ALTER TABLE `mvv_dokument` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`');
        $db->execute("UPDATE `mvv_dokument`
                LEFT JOIN `i18n` ON (`mvv_dokument`.`dokument_id` = `i18n`.`object_id`)
                SET `beschreibung_en` = `value`
                WHERE `table` = 'mvv_dokument' AND `field` = 'beschreibung' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_dokument'");


        // mvv_dokument_zuord
        // kommentar
        $db->execute('ALTER TABLE `mvv_dokument_zuord` ADD `kommentar_en` TEXT NULL DEFAULT NULL AFTER `kommentar`');
        $db->execute("UPDATE `mvv_dokument_zuord`
                LEFT JOIN `i18n` ON (`mvv_dokument_zuord`.`dokument_zuord_id` = `i18n`.`object_id`)
                SET `kommentar_en` = `value`
                WHERE `table` = 'mvv_dokument_zuord' AND `field` = 'kommentar' AND `lang` = 'en_GB'");

        $db->execute("ALTER TABLE `mvv_dokument_zuord` DROP PRIMARY KEY, ADD PRIMARY KEY (`dokument_id`, `range_id`, `object_type`) USING BTREE");
        $db->execute("ALTER TABLE `mvv_dokument_zuord` DROP `dokument_zuord_id`");


        // mvv_lvgruppe
        // name
        $db->execute('ALTER TABLE `mvv_lvgruppe` ADD `name_en` TEXT NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `mvv_lvgruppe`
                LEFT JOIN `i18n` ON (`mvv_lvgruppe`.`lvgruppe_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'mvv_lvgruppe' AND `field` = 'name' AND `lang` = 'en_GB'");

        // alttext
        $db->execute('ALTER TABLE `mvv_lvgruppe` ADD `alttext_en` TEXT NULL DEFAULT NULL AFTER `alttext`');
        $db->execute("UPDATE `mvv_lvgruppe`
                LEFT JOIN `i18n` ON (`mvv_lvgruppe`.`lvgruppe_id` = `i18n`.`object_id`)
                SET `alttext_en` = `value`
                WHERE `table` = 'mvv_lvgruppe' AND `field` = 'alttext' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_lvgruppe'");


        // mvv_modulteil_deskriptor
        $db->execute("ALTER TABLE `mvv_modulteil_deskriptor` ADD `sprache` VARCHAR(32) NULL DEFAULT NULL AFTER `modulteil_id`");

        $languages = $db->fetchFirst("SELECT distinct `lang` FROM `i18n`");
        $stmt = $db->prepare("SELECT DISTINCT(`object_id`) FROM `i18n` WHERE `table` = 'mvv_modulteil_deskriptor' AND `lang` = ?");
        foreach ($languages as $language) {
            $stmt->execute([$language]);
            foreach ($stmt->fetchAll() as $deskriptor_id) {
                $base_deskriptor = new ModulteilDeskriptor($deskriptor_id);
                $i18n_deskriptor = new ModulteilDeskriptor();
                $i18n_deskriptor->modulteil_id = $base_deskriptor->modulteil_id;
                $i18n_deskriptor->sprache = $language;
                $i18n_entries = I18NString::fetchDataForRow($base_deskriptor->id, 'mvv_modulteil_deskriptor');
                foreach ($i18n_entries as $field => $data) {
                    $i18n_deskriptor->$field = $data['value'];
                }
                $i18n_deskriptor->store();
            }
        }

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_modulteil_deskriptor'");


        // mvv_modul_deskriptor
        $db->execute("ALTER TABLE `mvv_modul_deskriptor` ADD `sprache` VARCHAR(32) NULL DEFAULT NULL AFTER `modul_id`");

        $stmt = $db->prepare("SELECT DISTINCT(`object_id`) FROM `i18n` WHERE `table` = 'mvv_modul_deskriptor' AND `lang` = ?");
        foreach ($languages as $language) {
            $stmt->execute([$language]);
            foreach ($stmt->fetchAll() as $deskriptor_id) {
                $base_deskriptor = new ModulDeskriptor($deskriptor_id);
                $i18n_deskriptor = new ModulDeskriptor();
                $i18n_deskriptor->modul_id = $base_deskriptor->modul_id;
                $i18n_deskriptor->sprache = $language;
                $i18n_entries = I18NString::fetchDataForRow($base_deskriptor->id, 'mvv_modul_deskriptor');
                foreach ($i18n_entries as $field => $data) {
                    $i18n_deskriptor->$field = $data['value'];
                }
                $i18n_deskriptor->store();
            }
        }

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_modul_deskriptor'");


        // mvv_stgteil
        // zusatz
        $db->execute('ALTER TABLE `mvv_stgteil` ADD `zusatz_en` TEXT NULL DEFAULT NULL AFTER `zusatz`');
        $db->execute("UPDATE `mvv_stgteil`
                LEFT JOIN `i18n` ON (`mvv_stgteil`.`stgteil_id` = `i18n`.`object_id`)
                SET `zusatz_en` = `value`
                WHERE `table` = 'mvv_stgteil' AND `field` = 'zusatz' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_stgteil'");


        // mvv_stgteilabschnitt
        // name
        $db->execute('ALTER TABLE `mvv_stgteilabschnitt` ADD `name_en` TEXT NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `mvv_stgteilabschnitt`
                LEFT JOIN `i18n` ON (`mvv_stgteilabschnitt`.`abschnitt_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'mvv_stgteilabschnitt' AND `field` = 'name' AND `lang` = 'en_GB'");

        // kommentar
        $db->execute('ALTER TABLE `mvv_stgteilabschnitt` ADD `kommentar_en` TEXT NULL DEFAULT NULL AFTER `kommentar`');
        $db->execute("UPDATE `mvv_stgteilabschnitt`
                LEFT JOIN `i18n` ON (`mvv_stgteilabschnitt`.`abschnitt_id` = `i18n`.`object_id`)
                SET `kommentar_en` = `value`
                WHERE `table` = 'mvv_stgteilabschnitt' AND `field` = 'kommentar' AND `lang` = 'en_GB'");

        // ueberschrift
        $db->execute('ALTER TABLE `mvv_stgteilabschnitt` ADD `ueberschrift_en` TEXT NULL DEFAULT NULL AFTER `ueberschrift`');
        $db->execute("UPDATE `mvv_stgteilabschnitt`
                LEFT JOIN `i18n` ON (`mvv_stgteilabschnitt`.`abschnitt_id` = `i18n`.`object_id`)
                SET `ueberschrift_en` = `value`
                WHERE `table` = 'mvv_stgteilabschnitt' AND `field` = 'ueberschrift' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_stgteilabschnitt'");


        // mvv_stgteilabschnitt_modul
        // bezeichnung
        $db->execute('ALTER TABLE `mvv_stgteilabschnitt_modul` ADD `bezeichnung_en` TEXT NULL DEFAULT NULL AFTER `bezeichnung`');
        $db->execute("UPDATE `mvv_stgteilabschnitt_modul`
                LEFT JOIN `i18n` ON (`mvv_stgteilabschnitt_modul`.`abschnitt_modul_id` = `i18n`.`object_id`)
                SET `bezeichnung_en` = `value`
                WHERE `table` = 'mvv_stgteilabschnitt_modul' AND `field` = 'bezeichnung' AND `lang` = 'en_GB'");

        $db->execute("ALTER TABLE `mvv_stgteilabschnitt_modul` DROP PRIMARY KEY, ADD PRIMARY KEY (`abschnitt_id`, `modul_id`) USING BTREE");
        $db->execute("ALTER TABLE `mvv_dokument_zuord` DROP `abschnitt_modul_id`");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_stgteilabschnitt_modul'");


        // mvv_stgteilversion
        // beschreibung
        $db->execute('ALTER TABLE `mvv_stgteilversion` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`');
        $db->execute("UPDATE `mvv_stgtteilversion`
                LEFT JOIN `i18n` ON (`mvv_stgteilversion`.`version_id` = `i18n`.`object_id`)
                SET `beschreibung_en` = `value`
                WHERE `table` = 'mvv_stgteilversion' AND `field` = 'beschreibung' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_stgteilversion'");


        // mvv_stgteil_bez
        // name
        $db->execute('ALTER TABLE `mvv_stgteil_bez` ADD `name_en` TEXT NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `mvv_stgtteil_bez`
                LEFT JOIN `i18n` ON (`mvv_stgteil_bez`.`stgteil_bez_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'mvv_stgteil_bez' AND `field` = 'name' AND `lang` = 'en_GB'");

        // name_kurz
        $db->execute('ALTER TABLE `mvv_stgteil_bez` ADD `name_kurz_en` TEXT NULL DEFAULT NULL AFTER `name_kurz`');
        $db->execute("UPDATE `mvv_stgtteil_bez`
                LEFT JOIN `i18n` ON (`mvv_stgteil_bez`.`stgteil_bez_id` = `i18n`.`object_id`)
                SET `name_kurz_en` = `value`
                WHERE `table` = 'mvv_stgteil_bez' AND `field` = 'name_kurz' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_stgteil_bez'");


        // mvv_studiengang
        // name
        $db->execute('ALTER TABLE `mvv_studiengang` ADD `name_en` TEXT NULL DEFAULT NULL AFTER `name`');
        $db->execute("UPDATE `mvv_studiengang`
                LEFT JOIN `i18n` ON (`mvv_studiengang`.`studiengang_id` = `i18n`.`object_id`)
                SET `name_en` = `value`
                WHERE `table` = 'mvv_studiengang' AND `field` = 'name' AND `lang` = 'en_GB'");

        // name_kurz
        $db->execute('ALTER TABLE `mvv_studiengang` ADD `name_kurz_en` TEXT NULL DEFAULT NULL AFTER `name_kurz`');
        $db->execute("UPDATE `mvv_studiengang`
                LEFT JOIN `i18n` ON (`mvv_studiengang`.`studiengang_id` = `i18n`.`object_id`)
                SET `name_kurz_en` = `value`
                WHERE `table` = 'mvv_studiengang' AND `field` = 'name_kurz' AND `lang` = 'en_GB'");

        // beschreibung
        $db->execute('ALTER TABLE `mvv_studiengang` ADD `beschreibung_en` TEXT NULL DEFAULT NULL AFTER `beschreibung`');
        $db->execute("UPDATE `mvv_studiengang`
                LEFT JOIN `i18n` ON (`mvv_studiengang`.`studiengang_id` = `i18n`.`object_id`)
                SET `beschreibung_en` = `value`
                WHERE `table` = 'mvv_studiengang' AND `field` = 'beschreibung' AND `lang` = 'en_GB'");

        $db->execute("DELETE FROM `i18n` WHERE `table` = 'mvv_studiengang'");


        // datafields
        $db->exec("ALTER TABLE datafields
            CHANGE type type ENUM('bool','textline','textarea','selectbox',
                'date','time','email','phone','radio','combo','link','selectboxmultiple')
            NOT NULL DEFAULT 'textline'");
        $db->execute('ALTER TABLE `datafields_entries` '
                . 'DROP PRIMARY KEY, ADD PRIMARY KEY (`datafield_id`, `range_id`, `sec_range_id`) USING BTREE');
        $db->exec('ALTER TABLE `datafields_entries` DROP `lang`');
    }

}
