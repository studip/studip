<?php
class ConfigPublicTopics extends Migration
{
    public function description()
    {
        return 'migrate seminare.public_topics to config';
    }

    public function up()
    {
        $db = DBManager::get();

        // migrate setting from seminare.public_topics
        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'COURSE_PUBLIC_TOPICS',
            'description' => 'Über diese Option können Sie die Themen einer Veranstaltung öffentlich einsehbar machen.',
            'range'       => 'course',
            'type'        => 'boolean',
            'value'       => '0'
        ]);

        $db->exec("INSERT INTO config_values (field, range_id, value, mkdate, chdate, comment)
                   SELECT 'COURSE_PUBLIC_TOPICS', Seminar_id, public_topics, mkdate, chdate, ''
                   FROM seminare WHERE public_topics = 1");

        $db->exec('ALTER TABLE seminare DROP public_topics');
    }

    public function down()
    {
        $db = DBManager::get();

        // migrate setting to seminare.public_topics
        $db->exec('ALTER TABLE seminare ADD public_topics tinyint(1) unsigned NOT NULL DEFAULT 0 AFTER completion');

        $db->exec("UPDATE config_values JOIN seminare ON range_id = Seminar_id
                   SET public_topics = value WHERE field = 'COURSE_PUBLIC_TOPICS'");

        $db->exec("DELETE config, config_values FROM config LEFT JOIN config_values USING(field)
                   WHERE field = 'COURSE_PUBLIC_TOPICS'");
    }
}
