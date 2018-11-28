<?
class TableTokenClass extends Migration
{
    public function description ()
    {
        return 'creates table for Token class';
    }

    public function up ()
    {
        $this->announce(" creating table...");
        
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `user_token` (
                                        `user_id` VARCHAR( 32 ) NOT NULL ,
                                        `token` VARCHAR( 32 ) NOT NULL ,
                                        `expiration` INT NOT NULL ,
                                        PRIMARY KEY ( `user_id` , `token` , `expiration` ),
                                        INDEX index_expiration (`expiration`),
                                        INDEX index_token (`token`),
                                        INDEX index_user_id (`user_id`)
                                    ) ENGINE=MyISAM;");
        
        $this->announce("done.");
        
    }
    
    public function down ()
    {
        $this->announce(" removing table...");
        DBManager::get()->exec("DROP TABLE `user_token`");
        
        $this->announce("done.");
        
    }
}
