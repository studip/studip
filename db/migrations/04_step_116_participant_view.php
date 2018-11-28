<?php
class Step116ParticipantView extends Migration
{
    public function description ()
    {
        return 'creates table necessary for StEP116';
    }

    public function up ()
    {
        $this->announce(" creating table...");

        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `teilnehmer_view` (
                                    `datafield_id` varchar(40) NOT NULL default '',
                                    `seminar_id` varchar(40) NOT NULL default '',
                                    `active` tinyint(4) default NULL,
                                    PRIMARY KEY  (`datafield_id`, `seminar_id`)
                                )");

        $this->announce("done.");

    }

    public function down ()
    {
        $this->announce(" removing table...");

        DBManager::get()->exec("DROP TABLE `teilnehmer_view`");

        $this->announce("done.");
    }
}
