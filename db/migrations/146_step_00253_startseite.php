<?php

class Step00253startseite extends Migration
{
    function description()
    {
        return 'Adds new Indexpage and a set of core-widgets';
    }

    function up()
    {
        DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `widget_user` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `pluginid` int(11) NOT NULL,
                    `position` int(11) NOT NULL DEFAULT 0,
                    `range_id` varchar(32) NOT NULL,
                    `col` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY (`range_id`)
                ) ENGINE=MyISAM
        ");

        DBManager::get()->exec("
                CREATE TABLE IF NOT EXISTS `widget_default` (
                    `pluginid` int(11) NOT NULL,
                    `col` tinyint(1) NOT NULL DEFAULT 0,
                    `position` tinyint(1) NOT NULL DEFAULT 0,
                    `perm` enum('user', 'autor', 'tutor', 'dozent', 'admin', 'root') NOT NULL DEFAULT 'autor',
                    PRIMARY KEY (`perm`,`pluginid`)
                ) ENGINE=MyISAM
        ");

        // take care of the widgets
        foreach (words('EvaluationsWidget NewsWidget QuickSelection ScheduleWidget TerminWidget') as $classname) {
            self::addWidget($classname);
        }

        // add a default configuration for each
        self::addWidgetDefaulConfiguration(words('QuickSelection NewsWidget TerminWidget EvaluationsWidget'));


    }

    function down()
    {
        // take care of the widgets
        foreach (words('EvaluationsWidget NewsWidget QuickSelection ScheduleWidget TerminWidget') as $classname) {
            self::removeWidget($classname);
        }

        DBManager::get()->exec("DROP TABLE IF EXISTS `widget_user`,`widget_default`");
    }

    private function addWidget($classname) {
        // get highest position
        $navpos = DBManager::get()->query("SELECT navigationpos FROM plugins
            ORDER BY navigationpos DESC")->fetchColumn() + 1;

        // insert plugin into db
        $stmt = DBManager::get()->prepare("INSERT INTO plugins
            (pluginclassname, pluginpath, pluginname, plugintype, enabled, navigationpos)
            VALUES (?, ?, ?, 'PortalPlugin', 'yes', ?)");
        $stmt->execute(array($classname, 'core/'.$classname, $classname, $navpos));

        // get id of newly created plugin (we purposely do not use PDO::lastInserId())
        $plugin_id = DBManager::get()->query("SELECT pluginid FROM plugins
            WHERE pluginclassname = '$classname'")->fetchColumn();

        // set all default roles for the plugin
        $stmt = DBManager::get()->prepare("INSERT INTO roles_plugins
            (roleid, pluginid) VALUES (?, ?)");
        foreach (range(1, 6) as $role_id) {
            $stmt->execute(array($role_id, $plugin_id));
        }
    }
    
    private function addWidgetDefaulConfiguration($classnames) {
        foreach($classnames as $key => $classname) {
            $plugin_id = DBManager::get()->query("SELECT pluginid FROM plugins
                WHERE pluginclassname = '$classname'")->fetchColumn();
            $stmt = DBManager::get()->prepare("INSERT INTO widget_default
                (`pluginid`, `col`, `position`, `perm`) VALUES (?, ?, ?, ?)");
            foreach (words("user autor tutor dozent admin root") as $perm) {
               $stmt->execute(array($plugin_id, 0, $key, $perm));
            }
        }
    }

    private function removeWidget($classname) {
        // get id of widget
        $widget_id = DBManager::get()->query("SELECT pluginid FROM plugins
            WHERE pluginclassname = '$classname'")->fetchColumn();

        $stmt = DBManager::get()->prepare("DELETE FROM plugins WHERE pluginid = ?");
        $stmt->execute(array($widget_id));

        $stmt = DBManager::get()->prepare("DELETE FROM widget_default WHERE pluginid = ?");
        $stmt->execute(array($widget_id));

        $stmt = DBManager::get()->prepare("DELETE FROM widget_user WHERE pluginid = ?");
        $stmt->execute(array($widget_id));

        $stmt = DBManager::get()->prepare("DELETE FROM roles_plugins WHERE pluginid = ?");
        $stmt->execute(array($widget_id));
    }
}
