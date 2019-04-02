<?php
class DialogFromNotification extends Migration
{
    public function description()
    {
        return 'It is now possible for a personal notification to be opened as a dialog.';
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE personal_notifications
            ADD `dialog` TINYINT NOT NULL DEFAULT '0' AFTER `avatar`
        ");
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE personal_notifications
            DROP `dialog`
        ");
    }
}
