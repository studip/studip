<?php
class Tic7206 extends Migration
{
    public function up()
    {
        DBManager::get()->exec("ALTER TABLE `questionnaires` ADD `copyable` TINYINT NOT NULL DEFAULT '0' AFTER `editanswers`");
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `questionnaires` DROP `copyable`");
    }
}
