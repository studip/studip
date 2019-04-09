<?php

class ConfigAjaxAutocompleteDisabled extends Migration {

    function description() {
        return 'Inserts a new config-variable to enable or disable ajax autocomplete used by QuickSearch.';
    }

    function up() {
        $options[] =
            [
            'name'        => 'AJAX_AUTOCOMPLETE_DISABLED',
            'type'        => 'boolean',
            'value'       => 0,
            'section'     => '',
            'description' => 'Sollen alle QuickSearches deaktiviertes Autocomplete haben? Wenn es zu Performanceproblemen kommt, kann es sich lohnen, diese Variable auf true zu stellen.'
            ];

        $stmt = DBManager::get()->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }

    function down() {
        $db = DBManager::get()->exec("DELETE FROM config WHERE field = 'AJAX_AUTOCOMPLETE_DISABLED'");
    }
}
