<?php
class DisableArchiveSearch extends Migration
{
    public function description()
    {
        return 'Adds a config to enable the archive search.';
    }

    public function up()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT INTO config (field, value, section, type, `range`, mkdate, chdate, description)
                              VALUES (:name, :value, :section, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'ENABLE_ARCHIVE_SEARCH',
            'section'     => 'global',
            'description' => 'Soll es eine Suche in dem alten Archiv geben?',
            'range'       => 'global',
            'type'        => 'boolean',
            'value'       => '0'
        ]);

    }

    public function down()
    {
        DBManager::get()->exec("DELETE config, config_values
                   FROM config
                   LEFT JOIN config_values USING (field)
                   WHERE field = 'ENABLE_ARCHIVE_SEARCH'");
    }
}
