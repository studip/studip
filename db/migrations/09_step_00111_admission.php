<?php
class StEP00111Admission extends Migration
{
    public function description ()
    {
        return 'creates table admission groups';
    }

    public function up ()
    {
        $this->announce(" creating table `admission_group`...");
        
        DBManager::get()->exec( "CREATE TABLE IF NOT EXISTS `admission_group` (
                          `group_id` varchar(32) NOT NULL,
                          `name` varchar(255) NOT NULL,
                          `status` tinyint(3) unsigned NOT NULL,
                          `chdate` int(10) unsigned NOT NULL,
                          `mkdate` int(10) unsigned NOT NULL,
                          PRIMARY KEY  (`group_id`)
                        ) ENGINE=MyISAM");
        $this->announce(" fill table with existing groups...");
        DBManager::get()->exec("INSERT IGNORE INTO admission_group 
                        (group_id, status, chdate,mkdate)
                        SELECT DISTINCT admission_group,0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM seminare WHERE admission_group <> ''");
        $this->announce("done.");
    }
    
    public function down ()
    {
        $this->announce(" removing table `admission_group`...");
        DBManager::get()->exec("DROP TABLE IF EXISTS `admission_group` ");
        $this->announce("done.");
    }
}
