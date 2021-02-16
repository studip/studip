<?php
class StEP00320ConfigSearchNavigation extends Migration
{

    function description()
    {
        return 'Adds configuration options of public visibility and navigation for '
        . 'course and module search to global config';
    }

    function up()
    {

        $standard_navigation_options = [
            'courses' => [
                'visible' => true,
                'target'  => 'sidebar'
            ],
            'semtree' => [
                'visible' => true,
                'target'  => 'sidebar'
            ],
            'rangetree' => [
                'visible' => true,
                'target'  => 'sidebar'
            ],
            'module' => [
                'visible' => true,
                'target'  => 'sidebar'
            ]
        ];

        $stmt = DBManager::get()->prepare("INSERT INTO `config`
            (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
            VALUES
            (:name, :value, :type, 'global', 'coursesearch', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :desc)
            ON DUPLICATE KEY UPDATE `chdate`=VALUES(`chdate`)");

        $stmt->execute([
                'name' => 'COURSE_SEARCH_NAVIGATION_OPTIONS',
                'value' => json_encode($standard_navigation_options),
                'type' => 'array',
                'desc' => 'Aktivierung und Reihenfolge der Navigationsoptionen in der Veranstaltungssuche'
            ]);
        $stmt->execute([
                'name' => 'COURSE_SEARCH_IS_VISIBLE_NOBODY',
                'value' => false,
                'type' => 'boolean',
                'desc' => 'Soll die Veranstaltungssuche auch für nobody (ohne Anmeldung) sichtbar sein?'
            ]);

    }

    function down()
    {
        DBManager::get()->exec("DELETE FROM `config` WHERE `field` IN (
            'COURSE_SEARCH_NAVIGATION_OPTIONS',
            'COURSE_SEARCH_IS_VISIBLE_NOBODY')"
        );
    }

}
