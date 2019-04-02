<?php

class DefaultDataFieldValues extends Migration
{
    /**
     * short description of this migration
     */
    public function description()
    {
        return 'Add default values for generic data fields.';
    }

    /**
     * perform this migration
     */
    public function up()
    {
        DBManager::get()->exec("ALTER TABLE datafields ADD default_value TEXT NULL AFTER is_required");
        DBManager::get()->exec("DELETE FROM datafields_entries WHERE content IS NULL OR content = ''");
    }

    /**
     * revert this migration
     */
    public function down()
    {
        DBManager::get()->exec("ALTER TABLE datafields DROP default_value");
    }
}
