<?php
class RemoveStudygroupdozent extends Migration
{
    function description () 
    {
        return 'removes the studygroup_dozent as a founder of all studygroups as well as the according entry in auth_user_md5 ';
    }

    function up ()
    {
        $db = DBManager::get();
        
        $db->exec("DELETE FROM seminar_user WHERE status = 'dozent' AND user_id = MD5('studygroup_dozent')");
        $db->exec("DELETE FROM auth_user_md5 WHERE user_id = MD5('studygroup_dozent')");
        
    }
}
?>
