<?php
class AllowTopicsToBePublic extends Migration
{
    public function description()
    {
        return 'Teachers may now configure to let therir topics be public.';
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `seminare` ADD `public_topics` TINYINT( 2 ) NOT NULL DEFAULT '1'
        ");
        DBManager::get()->exec("
            UPDATE `seminare` SET `public_topics` = '0'
        ");
    }

    public function down()
    {
        DBManager::get()->execute('
            ALTER TABLE `seminare` DROP `public_topics`
        ');
    }
}
