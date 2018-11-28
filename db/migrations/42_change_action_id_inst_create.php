<?
class ChangeActionIdInstCreate extends Migration
{
    function description ()
    {
        return 'Corrects action_id for INST_CREATE log action and log events';
    }

    function up ()
    {
        $db = DBManager::get();

    // fixes #448, cf. http://develop.studip.de/trac/ticket/448
        $db->exec("UPDATE log_actions SET action_id=MD5('INST_CREATE')
                                    WHERE action_id=MD5('INST_NEW')");
        $db->exec("UPDATE log_events SET action_id=MD5('INST_CREATE')
                                   WHERE action_id=MD5('INST_NEW')");
    }

}
?>
