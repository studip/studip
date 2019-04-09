<?php
class LimitMailqueue extends Migration
{
    function description()
    {
        return 'Sets an optional limit for emails to be sent via mailqueue. Default 0 means all mails will be sent at once.';
    }

    function up()
    {
        // Add config entries
        $query = "INSERT IGNORE INTO `config`
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`,
                     `mkdate`, `chdate`, `description`)
                  VALUES (MD5(:field), :field, :value, 1, :type, 'global', 'global',
                          UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':field' => 'MAILQUEUE_SEND_LIMIT',
            ':value' => "0",
            ':type'  => 'integer',
            ':description' => 'Wieviele Mails soll die Mailqueue maximal auf einmal an den Mailserver schicken. 0 für unendlich viele.',
        ]);
    }

    function down()
    {
        DBManager::get()->query("DELETE FROM config WHERE field IN ('MAILQUEUE_SEND_LIMIT')");
    }
}
