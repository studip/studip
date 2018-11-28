<?php
class RemoveFieldsFromExtermine extends Migration
{
    public function description ()
    {
        return 'removes expire|repeat|color|priority from table ex_termine';
    }

    public function up ()
    {
        $this->announce(" removing fields...");
        
        DBManager::get()->exec("ALTER TABLE `ex_termine` DROP `expire`, DROP `repeat`, DROP `color`, DROP `priority`");

        $this->announce("done.");
        
    }
    
    public function down ()
    {
        $this->announce("done.");
    }
}
