<?
class UnhideDozents extends Migration
{
    function description ()
    {
        return 'unhide users with status "dozent" in seminars (BIEST #711)';
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec("UPDATE seminar_user SET visible = 'yes' WHERE status = 'dozent'");
    }

    function down ()
    {
    
    }
}
?>
