<?php
class AddConfigAllowFakadmin extends Migration
{
    public $options = ['INST_FAK_ADMIN_PERMS' => '"none" Fakultätsadmin darf Einrichtungen weder anlegen noch löschen, "create" Fakultätsadmin darf Einrichtungen anlegen, aber nicht löschen, "all" Fakultätsadmin darf Einrichtungen anlegen und löschen.'];
    
    function description () 
    {
        return 'adds switch to config to controll fak_admin perms';
    }

    function up ()
    {
        $db = DBManager::get();
        $time = time();
        
        foreach ($this->options as $name => $description)
        {
            $db->exec("
              INSERT INTO config
                  (config_id, field, value, is_default, type, mkdate, chdate, description)
              VALUES
                  (MD5('$name'), '$name', 'all', 1, 'string', $time, $time, '$description')
            ");
        }
        
    }

    function down ()
    {
        $db = DBManager::get();
        foreach ($this->options as $name => $descrition)
        {
            $db->exec("DELETE FROM config WHERE field = '$name'");
        }
        
    }
}
?>
