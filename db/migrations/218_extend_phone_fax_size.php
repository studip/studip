<?php
class ExtendPhoneFaxSize extends Migration
{
    public function description()
    {
        return 'Increase max length of phone and fax fields from 32 to 255 chars';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE Institute CHANGE telefon telefon varchar(255) NOT NULL DEFAULT '',
                                         CHANGE fax fax varchar(255) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE user_info CHANGE privatnr privatnr varchar(255) NOT NULL DEFAULT '',
                                         CHANGE privatcell privatcell varchar(255) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE user_inst CHANGE Telefon Telefon varchar(255) NOT NULL DEFAULT '',
                                         CHANGE Fax Fax varchar(255) NOT NULL DEFAULT ''");
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE Institute CHANGE telefon telefon varchar(32) NOT NULL DEFAULT '',
                                         CHANGE fax fax varchar(32) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE user_info CHANGE privatnr privatnr varchar(32) NOT NULL DEFAULT '',
                                         CHANGE privatcell privatcell varchar(32) NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE user_inst CHANGE Telefon Telefon varchar(32) NOT NULL DEFAULT '',
                                         CHANGE Fax Fax varchar(32) NOT NULL DEFAULT ''");
    }
}
