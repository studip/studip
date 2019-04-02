<?php
class AddEvaldate extends Migration
{
    public function description()
    {
        return 'add column "evaldate" to "evalanswer_user" table';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE evalanswer_user ADD evaldate int(11) NOT NULL default 0');
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE evalanswer_user DROP evaldate');
    }
}
