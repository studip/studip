<?php

class Termsconfig extends Migration
{
    public function description()
    {
        return 'Add global config entry for "TERMS_CONFIG"';
    }

    public function up()
    {
        $query = "INSERT INTO `config` (
            `field`, `value`, `type`,
            `range`, `section`, `mkdate`, `chdate`,
            `description`
        ) VALUES (
            :field, :value, 'array', 'global', 'global',
            UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description
        )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':field', 'TERMS_CONFIG');
        $statement->bindValue(':value', json_encode(['compulsory' => false, 'denial_message' => '']));
        $statement->bindValue(':description', 'In case the terms are not compulsory, user can deny them.' .
                                            'if denial_message is not set, a default text is displayed.');
        $statement->execute();
    }

    public function down()
    {
        $query = "DELETE FROM `config` WHERE `field` = :field";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':field', 'TERMS_CONFIG');
        $statement->execute();
    }
}
