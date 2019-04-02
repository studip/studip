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
            'name'        => 'WIKI_COURSE_EDIT_RESTRICTED',
            'description' => 'Legt fest, dass nur Teilnehmende ab Rechtestufe "tutor" das Wiki bearbeiten dürfen.',
            'range'       => 'course',
            'type'        => 'boolean',
            'value'       => '0'
        ]);

        // table for wiki page permissions settings
        $db->exec("CREATE TABLE wiki_page_config (
                    range_id CHAR(32) COLLATE latin1_bin NOT NULL,
                    keyword VARCHAR(255) COLLATE utf8mb4_bin NOT NULL,
                    read_restricted TINYINT(1) NOT NULL DEFAULT 0,
                    edit_restricted TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (range_id, keyword)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE wiki_page_config');

        $db->exec("DELETE config, config_values
                   FROM config
                   LEFT JOIN config_values USING (field)
                   WHERE field = 'WIKI_COURSE_EDIT_RESTRICTED'");
    }
}
