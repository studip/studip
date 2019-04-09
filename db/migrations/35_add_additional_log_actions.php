<?
class AddAdditionalLogActions extends Migration {

    // Array of new Log Actions
    private $logactions = [
        [
            'name'=>'CHANGE_BASIC_DATA',
            'description'=>'Basisdaten geändert',
            'info_template'=>'%user hat in Veranstaltung %sem(%affected) die Daten %info geändert.',
            'active'=>0],
        [
            'name'=>'CHANGE_INSTITUTE_DATA',
            'description'=>'Institutdaten geändert',
            'info_template'=>'%user hat in Veranstaltung %sem(%affected) die Daten %info geändert.',
            'active'=>0],
        [
            'name'=>'PLUGIN_ENABLE',
            'description'=>'Plugin einschalten',
            'info_template'=>'%user hat in Veranstaltung %sem(%affected) das Plugin %plugin(%coaffected) aktiviert.',
            'active'=>1],
        [
            'name'=>'PLUGIN_DISABLE',
            'description'=>'Plugin ausschalten',
            'info_template'=>'%user hat in Veranstaltung %sem(%affected) das Plugin %plugin(%coaffected) deaktiviert.',
            'active'=>1],
        [
            'name'=>'SEM_CHANGED_ACCESS',
            'description'=>'Zugangsberechtigungen geändert',
            'info_template'=>'%user ändert die Zugangsberechtigungen für %sem(%affected) auf %access(%info).',
            'active'=>0],
        [
            'name'=>'SEM_USER_ADD',
            'description'=>'In Veranstaltung eingetragen',
            'info_template'=>'%user hat %user(%coaffected) für %sem(%affected) mit dem status %info eingetragen. (%dbg_info)',
            'active'=>0],
        [
            'name'=>'SEM_USER_DEL',
            'description'=>'Aus Veranstaltung ausgetragen',
            'info_template'=>'%user hat %user(%coaffected) aus %sem(%affected) ausgetragen. (%info)',
            'active'=>0],
        [
            'name'=>'SEM_CHANGED_RIGHTS',
            'description'=>'Veranstaltungsrechte geändert',
            'info_template'=>'%user hat %user(%coaffected) in %sem(%affected) als %info eingetragen. (%dbg_info)',
            'active'=>0],
        [
            'name'=>'SEM_ADD_STUDYAREA',
            'description'=>'Studienbereich zu Veranst. hinzufügen',
            'info_template'=>'%user fügt Studienbereich \"%studyarea(%coaffected)\" zu %sem(%affected) hinzu.',
            'active'=>0],
        [
            'name'=>'SEM_DELETE_STUDYAREA',
            'description'=>'Studienbereich aus Veranst. löschen',
            'info_template'=>'%user entfernt Studienbereich \"%studyarea(%coaffected)\" aus %sem(%affected).',
            'active'=>0],
        [
            'name'=>'RES_ASSIGN_SEM',
            'description'=>'Buchen einer Ressource (VA)',
            'info_template'=>'%user bucht %res(%affected) für %sem(%coaffected) (%info).',
            'active'=>0],
        [
            'name'=>'RES_ASSIGN_SINGLE',
            'description'=>'Buchen einer Ressource (Einzel)',
            'info_template'=>'%user bucht %res(%affected) direkt (%info).',
            'active'=>0],
        [
            'name'=>'RES_REQUEST_NEW',
            'description'=>'Neue Raumanfrage',
            'info_template'=>'%user stellt neue Raumanfrage für %sem(%affected), gewünschter Raum: %res(%coaffected), %info',
            'active'=>0],
        [
            'name'=>'RES_REQUEST_UPDATE',
            'description'=>'Geänderte Raumanfrage',
            'info_template'=>'%user ändert Raumanfrage für %sem(%affected), gewünschter Raum: %res(%coaffected), %info',
            'active'=>0],
        [
            'name'=>'RES_REQUEST_DEL',
            'description'=>'Raumanfrage löschen',
            'info_template'=>'%user löscht Raumanfrage für %sem(%affected).',
            'active'=>0],
        [
            'name'=>'RES_ASSIGN_DEL_SEM',
            'description'=>'VA-Buchung löschen',
            'info_template'=>'%user löscht Ressourcenbelegung für %res(%affected) in Veranstaltung %sem(%coaffected), %info.',
            'active'=>0],
        [
            'name'=>'RES_ASSIGN_DEL_SINGLE',
            'description'=>'Direktbuchung löschen',
            'info_template'=>'%user löscht Direktbuchung für %res(%affected) (%info).',
            'active'=>0],
        [
            'name'=>'RES_REQUEST_DENY',
            'description'=>'Abgelehnte Raumanfrage',
            'info_template'=>'%user lehnt Raumanfrage für %sem(%coaffected), Raum %sem(%affected) ab.',
            'active'=>0],
        [
            'name'=>'RES_REQUEST_RESOLVE',
            'description'=>'Aufgelöste Raumanfrage',
            'info_template'=>'%user löst Raumanfrage für %sem(%affected), Raum %res(%coaffected) auf.',
            'active'=>0]
        ];


    function description () {
        return 'adds new log actions for changing basic data within lectures and enabling and disabling plugins';
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
