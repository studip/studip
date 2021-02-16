<?php
class StEP00278GlobalSearch extends Migration
{

    function description() {
        return 'Adds global search module management to config';
    }

    function up() {

        $modules = [
            'GlobalSearchBuzzwords' => [
                'order' => 1,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchMyCourses' => [
                'order' => 2,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchCourses' => [
                'order' => 3,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchUsers' => [
                'order' => 4,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchInstitutes' => [
                'order' => 5,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchFiles' => [
                'order' => 6,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchCalendar' => [
                'order' => 7,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchMessages' => [
                'order' => 8,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchForum' => [
                'order' => 9,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchResources' => [
                'order' => 10,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchRoomAssignments' => [
                'order' => 11,
                'active' => true,
                'fulltext' => false
            ],
            'GlobalSearchModules' => [
                'order' => 12,
                'active' => true,
                'fulltext' => false
            ]
        ];

        $stmt = DBManager::get()->prepare("INSERT INTO `config`
            (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
            VALUES
            (:name, :value, :type, 'global', 'globalsearch', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :desc)
            ON DUPLICATE KEY UPDATE `chdate`=VALUES(`chdate`)");

        $stmt->execute([
                'name' => 'GLOBALSEARCH_MODULES',
                'value' => json_encode($modules),
                'type' => 'array',
                'desc' => 'Aktivierung und Reihenfolge der Module in der globalen Suche'
            ]);
        $stmt->execute([
                'name' => 'GLOBALSEARCH_MAX_RESULT_OF_TYPE',
                'value' => 3,
                'type' => 'integer',
                'desc' => 'Wie viele Ergebnisse sollen in der globalen Schnellsuche pro Kategorie angezeigt werden?'
            ]);
        $stmt->execute([
                'name' => 'GLOBALSEARCH_ASYNC_QUERIES',
                'value' => true,
                'type' => 'boolean',
                'desc' => 'Sollen die Suchanfragen asynchron über mysqli gestellt werden? Andernfalls wird PDO verwendet.'
            ]);

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `globalsearch_buzzwords` (
                `id` VARCHAR(32) NOT NULL,
                `rights` ENUM('user','autor','tutor','dozent','admin','root') NOT NULL DEFAULT 'user',
                `name` varchar(255) NOT NULL DEFAULT '',
                `buzzwords` varchar(2048) NOT NULL DEFAULT '',
                `subtitle` varchar(255) DEFAULT NULL,
                `url` varchar(2048) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
    }

    function down() {
        DBManager::get()->exec("DELETE FROM `config` WHERE `field` IN (
            'GLOBALSEARCH_MODULES',
            'GLOBALSEARCH_MAX_RESULT_OF_TYPE',
            'GLOBALSEARCH_ASYNC_QUERIES')"
        );

        DBManager::get()->exec("DROP TABLE IF EXISTS `globalsearch_buzzwords`");
    }

}
