<?php


class Tic8458AddUploadDescription extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT INTO `config`
            (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`,
            `description`)
            VALUES
            ('ENABLE_DESCRIPTION_ENTRY_ON_UPLOAD', '1', 'boolean', 'global', 'files', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Whether to allow adding a description directly after file upload (true) or not (false). Defaults to true.')"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "DELETE FROM `config`
            WHERE `field` = 'ENABLE_DESCRIPTION_ENTRY_ON_UPLOAD'"
        );
    }


    public function description()
    {
        return 'Adds the configuration entry ENABLE_DESCRIPTION_ENTRY_ON_UPLOAD to make it possible to enter a file description directly after uploading the file(s).';
    }
}
