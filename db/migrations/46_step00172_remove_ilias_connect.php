<?
class Step00172RemoveIliasConnect extends Migration
{

    function description ()
    {
        return "Remove tables `seminar_lernmodul` and `studip_ilias` ";
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS `seminar_lernmodul`");
        $db->exec("DROP TABLE IF EXISTS `studip_ilias`");
    }

    function down ()
    {

    }
}
?>