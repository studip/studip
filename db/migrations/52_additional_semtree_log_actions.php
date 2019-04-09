<?php
class AdditionalSemtreeLogActions extends Migration {

    // Array of new Log Actions
    private $logactions = [
        [
            'name'=>'STUDYAREA_ADD',
            'description'=>'Studienbereich hinzufügen',
            'info_template'=>'%user legt Studienbereich %studyarea(%affected) an.',
            'active'=>0],
        [
            'name'=>'STUDYAREA_DELETE',
            'description'=>'Studienbereich löschen',
            'info_template'=>'%user entfernt Studienbereich %studyarea(%affected).',
            'active'=>0]
            ];


    function description () {
        return 'adds two new log actions for adding and deleting SemTree Items';
    }

    function up () {

        $insert = "INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD5('%s'), '%s', '%s', '%s', %s, NULL)";

        foreach ($this->logactions as $a)
        {
            DBManager::get()->query(sprintf($insert,$a['name'],$a['name'],$a['description'],$a['info_template'],$a['active']));
        }
    }

    function down () {

        $delete = "DELETE FROM log_actions WHERE action_id = MD5('%s')";

        foreach ($this->logactions as $a)
        {
            DBManager::get()->query(sprintf($delete,$a['name']));
        }
    }
}



?>
