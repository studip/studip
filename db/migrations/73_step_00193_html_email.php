<?php

class Step00193HtmlEmail extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'add MAIL_AS_HTML config setting';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();
        $time = time();

        $stmt = $db->prepare("
                INSERT INTO config
                    (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, $time, $time, :description)
                ");

        $stmt->execute([
            'name' => 'MAIL_AS_HTML',
            'description' => 'Benachrichtigungen werden im HTML-Format versandt',
            'section' => '',
            'range' => 'user',
            'type' => 'boolean',
            'value' => 0
        ]);
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM config WHERE field = 'MAIL_AS_HTML'");
    }
}
