<?
class LockRulez extends Migration
{

    public function description ()
    {
        return 'creates table for lock rules';
    }

    public function up ()
    {
        $this->announce(" creating table...");

        DBManager::get()->exec( "
            CREATE TABLE `lock_rules` (
                `lock_id` varchar(32) NOT NULL default '',
                `name` varchar(255) NOT NULL default '',
                `description` text NOT NULL,
                `attributes` text NOT NULL,
                PRIMARY KEY  (`lock_id`)
            ) ENGINE=MyISAM");

        DBManager::get()->exec("ALTER TABLE `seminare` ADD `lock_rule` VARCHAR( 32 ) NULL");

        $this->announce("done.");

    }

    public function down ()
    {
        $this->announce(" removing table...");
        DBManager::get()->exec("DROP TABLE `lock_rules`");
        DBManager::get()->exec("ALTER TABLE `seminare` DROP `lock_rule`");

        $this->announce("done.");

    }
}
