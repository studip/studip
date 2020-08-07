<?php


class AddAdminCoursesSidebarActiveElements extends Migration
{
    public function up()
    {
        $c = DBManager::get();

        $c->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`, `description`)
            VALUES
            (
                'ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS',
                '',
                'string',
                'user',
                'Diese Einstellung legt fest, welche Elemente in der Seitenleiste der Veranstaltungsübersicht für Admins sichtbar sind.'
            )"
        );
    }


    Public function down()
    {
        $c = DBManager::get();

        $c->exec(
            "DELETE FROM `config` WHERE `field` = 'ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS'"
        );
        $c->exec(
            "DELETE FROM `config_values` where `field` = 'ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS'"
        );
    }
}
