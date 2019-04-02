<?php
/**
 * LtiConsumer - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class LtiConsumer extends Migration
{
    public function description()
    {
        return 'initial database setup for LTI consumer API';
    }

    public function up()
    {
        $db = DBManager::get();

        $sql = "CREATE TABLE lti_data (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    position INT(11) NOT NULL DEFAULT 0,
                    course_id CHAR(32) COLLATE latin1_bin NOT NULL,
                    title VARCHAR(255) NOT NULL DEFAULT '',
                    description TEXT NOT NULL DEFAULT '',
                    tool_id INT(11) NOT NULL DEFAULT 0,
                    launch_url VARCHAR(255) NOT NULL DEFAULT '',
                    mkdate INT(11) NOT NULL DEFAULT 0,
                    chdate INT(11) NOT NULL DEFAULT 0,
                    options TEXT NULL,
                    PRIMARY KEY (id),
                    KEY course_id (course_id)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        $db->exec($sql);

        $sql = "CREATE TABLE lti_tool (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL DEFAULT '',
                    launch_url VARCHAR(255) NOT NULL DEFAULT '',
                    consumer_key VARCHAR(255) NOT NULL DEFAULT '',
                    consumer_secret VARCHAR(255) NOT NULL DEFAULT '',
                    custom_parameters TEXT NOT NULL,
                    allow_custom_url TINYINT(1) NOT NULL DEFAULT 0,
                    deep_linking TINYINT(1) NOT NULL DEFAULT 0,
                    send_lis_person TINYINT(1) NOT NULL DEFAULT 0,
                    mkdate INT(11) NOT NULL DEFAULT 0,
                    chdate INT(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        $db->exec($sql);

        $sql = "CREATE TABLE lti_grade (
                    link_id INT(11) NOT NULL DEFAULT 0,
                    user_id CHAR(32) COLLATE latin1_bin NOT NULL,
                    score FLOAT NOT NULL DEFAULT 0,
                    mkdate INT(11) NOT NULL DEFAULT 0,
                    chdate INT(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (link_id, user_id)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        $db->exec($sql);

        // install as core plugin
        $sql = "INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
                VALUES ('LtiToolModule', 'LTI-Tool', 'StandardPlugin,SystemPlugin,PrivacyPlugin', 'yes', 1)";
        $db->exec($sql);

        $sql = "INSERT INTO roles_plugins (roleid, pluginid)
                SELECT roleid, ? FROM roles WHERE system = 'y'";
        $db->execute($sql, [$db->lastInsertId()]);

        // install config settings
        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute(array(
            'name'        => 'LTI_TOOL_TITLE',
            'description' => 'Voreinstellung für den Titel des Reiters "LTI-Tool" im Kurs.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'LTI-Tool'
        ));

        // migrate data from alija plugin
        $result = $db->query("SHOW TABLES LIKE 'alija_grade'");

        if ($result->rowCount() > 0) {
            $db->exec("INSERT INTO lti_data
                       SELECT id, position, course_id, title, description, tool_id,
                              launch_url, mkdate, chdate, options FROM alija_data");
            $db->exec('INSERT INTO lti_tool SELECT * FROM alija_tool');
            $db->exec('INSERT INTO lti_grade SELECT * FROM alija_grade');
        }
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DELETE config, config_values FROM config
                   LEFT JOIN config_values USING (field)
                   WHERE field = 'LTI_TOOL_TITLE'");

        $db->exec("DELETE plugins, roles_plugins FROM plugins
                   LEFT JOIN roles_plugins USING (pluginid)
                   WHERE pluginclassname = 'LtiToolModule'");

        $db->exec('DROP table lti_grade, lti_tool, lti_data');
    }
}
