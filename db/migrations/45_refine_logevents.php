<?
require_once 'lib/admission.inc.php';
class RefineLogevents extends Migration
{

    function description ()
    {
        return "Refine OS Logevent  'SEM_CHANGED_ACCESS' Information text into more sensebale and readable text";
    }

    function up ()
    {

        // (1) Get action_id for logactions'SEM_CHANGED_ACCESS'
        $data = DBManager::get()->query("SELECT action_id FROM log_actions WHERE name  = 'SEM_CHANGED_ACCESS'")->fetch();
        $action_id = $data['action_id'];
        
        // (2) Update the format string
        DBManager::get()->query("UPDATE log_actions 
            SET info_template = '%user ändert die Zugangsberechtigungen der Veranstaltung %sem(%affected).'
            WHERE action_id = '$action_id'");

        // (3) Get all corresponding logevents and update the info text
        $stmt = DBManager::get()->query("SELECT event_id, info FROM log_events WHERE action_id = '$action_id'");
        while ($data = $stmt->fetch()) {
          if($info=unserialize($data['info'])) {
            $anmeldeverfahren=$info["admission_type"]; 
            $startzeit=$info["start_time"]; 
            $endzeit=$info["end_time"]; 
            $lesezugriff=$info["read_level"]; 
            $schreibzugriff=$info["write_level"]; 
            $admission_prelim=$info["admission_prelim"]; 
            $passwort=$info["passwort"]; 
            $disable_waiting_list=$info["admission_disable_waitlist"]; 
            $maxteilnehmerzahl=$info["admission_turnout"]; 
            $verbindlich = $info["admission_binding"]; 
            $enable_quota=$info["admission_enable_quota"]; 

            $inf_txt=": \nAnmeldeverfahren auf ".get_admission_description('admission_type', $anmeldeverfahren)." geändert.". 
                            " Startzeit auf ".date("d.m.Y H:i",$startzeit)." geändert.". 
                            " Endzeit auf ".date("d.m.Y H:i",$endzeit)." geändert.\n". 
                            "Lesezugriff auf ".get_admission_description('read_level', $lesezugriff)." geändert.". 
                            " Schreibzugriff auf ".get_admission_description('write_level', $schreibzugriff)." geändert.". 
                            " Vorläufigen Zugang auf ".get_admission_description('admission_prelim',$admission_prelim)." geändert.\n". 
                            "Passwort auf ".$passwort." geändert.". 
                            "Warteliste auf ".$disable_waiting_list." geändert.". 
                            " Teilnehmerzahl auf ".$maxteilnehmerzahl." geändert.". 
                            " Verbindlich auf ".$verbindlich." geändert.". 
                            " Enable_Quotas ".$enable_quota." geändert."; 
            
            DBManager::get()->query("UPDATE log_events SET info = '$inf_txt' WHERE event_id = '{$data['event_id']}'");
          }  
          
        }
    }

    function down ()
    {

    }
}
?>
