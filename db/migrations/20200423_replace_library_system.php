<?php


class ReplaceLibrarySystem extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        //add new configuration variables:

        $db->exec(
            "INSERT IGNORE INTO config
            (
                `field`,
                `value`,
                `type`,
                `range`,
                `section`,
                `mkdate`,
                `chdate`,
                `description`
            )
            VALUES
            (
                'LIBRARY_ADD_ITEM_ACTION_DESCRIPTION',
                'Sie können digitale Originaldokumente direkt aus der Bibliothek (ggf. Name) beziehen. Sie erhalten Materialien mit geklärten Rechten und in hochwertiger Qualität. Bei Bedarf kann die Bibliothek zur Bereitstellung eingebunden werden.',
                'string',
                'global',
                'Library',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'Der Beschreibungstext für die Aktion zum Hinzufügen eines Bibliothekseintrags in den Dateibereich.'
            ),
            (
                'COURSE_LIBRARY_CITATION_STYLE',
                '',
                'string',
                'course',
                'Library',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'Der standardmäßig genutzte Zitationsstil innerhalb der Veranstaltung.'
            ),
            (
                'USER_LIBRARY_CITATION_STYLE',
                '',
                'string',
                'user',
                'Library',
                UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(),
                'Der präferierte Zitationsstil einer Person.'
            )"
        );

        $db->exec('DROP TABLE `lit_catalog`');
        $db->exec('DROP TABLE `lit_list`');
        $db->exec('DROP TABLE `lit_list_content`');
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "DELETE FROM `config` WHERE `field` IN (
                'LIBRARY_ADD_ITEM_ACTION_DESCRIPTION',
                'COURSE_LIBRARY_CITATION_STYLE',
                'USER_LIBRARY_CITATION_STYLE'
            )"
        );
        $db->exec(
            "DELETE FROM `config_values` WHERE `field` IN (
                'LIBRARY_ADD_ITEM_ACTION_DESCRIPTION',
                'COURSE_LIBRARY_CITATION_STYLE',
                'USER_LIBRARY_CITATION_STYLE'
            )"
        );

        $db->exec(
            "CREATE TABLE `lit_catalog` (
                `catalog_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `mkdate` int(11) NOT NULL DEFAULT '0',
                `chdate` int(11) NOT NULL DEFAULT '0',
                `lit_plugin` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Studip',
                `accession_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `dc_creator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `dc_subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_description` text COLLATE utf8mb4_unicode_ci,
                `dc_publisher` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_contributor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_date` date DEFAULT NULL,
                `dc_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_format` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_language` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                `dc_relation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_coverage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `dc_rights` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`catalog_id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC"
        );

        $db->exec(
            "CREATE TABLE `lit_list` (
                `list_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `range_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `mkdate` int(11) NOT NULL DEFAULT '0',
                `chdate` int(11) NOT NULL DEFAULT '0',
                `priority` smallint(6) NOT NULL DEFAULT '0',
                `visibility` tinyint(4) NOT NULL DEFAULT '0',
                PRIMARY KEY (`list_id`),
                KEY `range_id` (`range_id`),
                KEY `priority` (`priority`),
                KEY `visibility` (`visibility`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;"
        );

        $db->exec(
            "CREATE TABLE `lit_list_content` (
                `list_element_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `list_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `catalog_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `mkdate` int(11) NOT NULL DEFAULT '0',
                `chdate` int(11) NOT NULL DEFAULT '0',
                `note` text COLLATE utf8mb4_unicode_ci,
                `priority` smallint(6) NOT NULL DEFAULT '0',
                PRIMARY KEY (`list_element_id`),
                KEY `list_id` (`list_id`),
                KEY `catalog_id` (`catalog_id`),
                KEY `priority` (`priority`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;"
        );
    }
}
