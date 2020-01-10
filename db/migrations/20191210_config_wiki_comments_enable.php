<?php
class ConfigWikiCommentsEnable extends Migration
{
    public function description()
    {
        return 'add config option for WIKI_COMMENTS_ENABLE';
    }

    public function up()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT INTO config (field, value, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'WIKI_COMMENTS_ENABLE',
            'description' => 'Einstellung für die Anzeige von Kommentaren in Wiki als Icon',
            'range'       => 'user',
            'type'        => 'boolean',
            'value'       => '0'
        ]);
    }

    public function down()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE field = ?');
        $stmt->execute(['WIKI_COMMENTS_ENABLE']);
    }
}
