<?php
class Tic10318HttpProxy extends Migration
{
    public function description()
    {
        return 'add config option for http proxy';
    }

    public function up()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT IGNORE INTO config (field, value, type, `range`, section, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'HTTP_PROXY',
            'description' => 'externe http Anfragen über proxy',
            'range'       => 'global',
            'type'        => 'string',
            'value'       => '',
            'section'     => 'global'
        ]);
        $stmt->execute([
            'name'        => 'HTTP_PROXY_IGNORE',
            'description' => 'Kommaseparierte Liste mit Hostnamen, die nicht über Proxy aufgerufen werden sollen',
            'range'       => 'global',
            'type'        => 'string',
            'value'       => '',
            'section'     => 'global'
        ]);
    }

    public function down()
    {
        $db = DBManager::get();

        $db->execute('DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE field = ?', ['HTTP_PROXY']);
        $db->execute('DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE field = ?', ['HTTP_PROXY_IGNORE']);
    }
}
