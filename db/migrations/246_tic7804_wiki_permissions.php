<?php
class Tic7804WikiPermissions extends Migration
{
    public function description()
    {
        return 'add wiki page permissions';
    }

    public function up()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'WIKI_COURSE_EDIT_PERM',
            'description' => 'Legt fest, ab welcher Rechtestufe in der Veranstaltung das Wiki bearbeitbar ist.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'autor'
        ]);

        // table for wiki page permissions settings
        $db->exec("CREATE TABLE wiki_page_config (
                    range_id CHAR(32) COLLATE latin1_bin NOT NULL,
                    keyword VARCHAR(255) COLLATE utf8mb4_bin NOT NULL,
                    read_perms ENUM('user', 'tutor', 'dozent') COLLATE latin1_bin NOT NULL DEFAULT 'user',
                    edit_perms ENUM('autor', 'tutor', 'dozent') COLLATE latin1_bin NOT NULL DEFAULT 'autor',
                    PRIMARY KEY (range_id, keyword)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE wiki_page_config');

        $db->exec("DELETE config, config_values
                   FROM config
                   LEFT JOIN config_values USING (field)
                   WHERE field = 'WIKI_COURSE_EDIT_PERM'");

        SimpleORMap::expireTableScheme();
    }
}
