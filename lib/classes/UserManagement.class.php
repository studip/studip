<?php
# Lifter007: TODO
/**
 * UserManagement.class.php
 *
 * Management for the Stud.IP global users
 *
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @author      Stefan Suchi <suchi@data-quest>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL Licence 2
 * @category    Stud.IP
 */

// Imports
require_once 'lib/admission.inc.php';   // remove user from waiting lists
require_once 'lib/statusgruppe.inc.php';    // remove user from statusgroups
require_once 'lib/messaging.inc.php';   // remove messages send or recieved by user
require_once 'lib/object.inc.php';

/**
 * UserManagement.class.php
 *
 * Management for the Stud.IP global users
 *
 */
class UserManagement
{
    private $user;
    private $validator;
    private $user_data;

    public $msg;

    private static $pwd_hasher;

    public static function getPwdHasher()
    {
        if (self::$pwd_hasher === null) {
            self::$pwd_hasher = new PasswordHash(8, Config::get()->PHPASS_USE_PORTABLE_HASH);
        }
        return self::$pwd_hasher;
    }

    /**
    * Constructor
    *
    * Pass nothing to create a new user, or the user_id from an existing user to change or delete
    * @param    string  $user_id    the user which should be retrieved
    */
    public function __construct($user_id = false)
    {
        $this->validator = new email_validation_class();
        $this->validator->timeout = 10;                 // How long do we wait for response of mailservers?
        $this->getFromDatabase($user_id);
    }

    public function __get($attr)
    {
        if ($attr === 'user_data') {
            return $this->user_data;
        }
    }

    public function __set($attr, $value)
    {
        if ($attr === 'user_data') {
            if (!is_array($value)) {
                throw new InvalidArgumentException('user_data only accepts array');
            }
            return $this->user_data->setData($value, true);
        }
    }

    /**
    * load user data from database into internal array
    *
    * @param    string  $user_id    the user which should be retrieved
    */
    public function getFromDatabase($user_id)
    {
        $this->user = User::toObject($user_id);
        if (!$this->user) {
            $this->user = new User();
        }
        $this->user_data = new UserDataAdapter($this->user);
    }

    /**
    * store user data from internal array into database
    *
    * @access   private
    * @return   bool all data stored?
    */
    private function storeToDatabase()
    {
        if ($this->user->isNew()) {
            if ($this->user->store()) {
                StudipLog::log('USER_CREATE', $this->user->id, null, implode(';', $this->user->toArray('username vorname nachname perms email')));
                return true;
            } else {
                return false;
            }
        }

        $nperms = [
            'user' => 0,
            'autor' => 1,
            'tutor' => 2,
            'dozent' => 3
        ];

        if ($this->user->isDirty('perms')) {
            if ($this->user->perms === 'dozent' && in_array($this->user->getPristineValue('perms'), ['user','autor','tutor'])) {
                $this->logInstUserDel($this->user->id, "inst_perms = 'user'");
                $this->user->institute_memberships->unsetBy('inst_perms', 'user');
                // make user visible globally if dozent may not be invisible (StEP 00158)
                if (Config::get()->DOZENT_ALWAYS_VISIBLE && $this->user->visible !== 'never') {
                    $this->user->visible = 'yes';
                }
                if ($nperms[$this->user->perms] < $nperms[$this->user->getPristineValue('perms')]) {
                    $query = "UPDATE seminar_user
                              INNER JOIN seminare ON (seminare.Seminar_id = seminar_user.Seminar_id)
                              SET seminar_user.status = :new_max_status
                              WHERE seminar_user.user_id = :user_id
                                AND seminar_user.status IN (:old_status)
                                AND seminare.status NOT IN (:studygroups)";
                    $downgrade = DBManager::get()->prepare($query);
                    $old_status = [];
                    foreach ($nperms as $status => $n) {
                        if ($n > $nperms[$this->user->perms] && $n <= $nperms[$this->user->getPristineValue('perms')]) {
                            $old_status[] = $status;
                        }
                    }
                    $downgrade->execute([
                        'user_id'        => $this->user->id,
                        'old_status'     => $old_status,
                        'studygroups'    => studygroup_sem_types(),
                        'new_max_status' => $this->user->perms,
                    ]);
                }
            }
        }
        foreach (words('username vorname nachname perms email title_front title_rear password') as $field) {
            // logging
            if ($this->user->isFieldDirty($field)) {
                $old_value = $this->user->getPristineValue($field);
                $value = $this->user->getValue($field);
                switch ($field) {
                    case 'username':
                        StudipLog::log('USER_CHANGE_USERNAME', $this->user->id, null, "{$old_value} -> {$value}");
                        break;
                    case 'vorname':
                        StudipLog::log('USER_CHANGE_NAME', $this->user->id, null, "Vorname: {$old_value} -> {$value}");
                        break;
                    case 'nachname':
                        StudipLog::log('USER_CHANGE_NAME', $this->user->id, null, "Nachname: {$old_value} -> {$value}");
                        break;
                    case 'perms':
                        StudipLog::log('USER_CHANGE_PERMS', $this->user->id, null, "{$old_value} -> {$value}");
                        break;
                    case 'email':
                        StudipLog::log('USER_CHANGE_EMAIL', $this->user->id, null, "{$old_value} -> {$value}");
                        break;
                    case 'title_front':
                        StudipLog::log('USER_CHANGE_TITLE', $this->user->id, null, "title_front: {$old_value} -> {$value}");
                        break;
                    case 'title_rear':
                        StudipLog::log('USER_CHANGE_TITLE', $this->user->id, null, "title_rear: {$old_value} -> {$value}");
                    case 'password':
                        StudipLog::log('USER_CHANGE_PASSWORD', $this->user->id, null, "password: {$old_value} -> {$value}");
                        break;
                }
            }
        }

        $changed = $this->user->store();
        return (bool) $changed;
    }


    /**
    * generate a secure password of $length characters [a-z0-9]
    *
    * @param    integer $length number of characters
    * @return   string password
    */
    public function generate_password($length)
    {
        mt_srand((double) microtime() * 1000000);
        for ($i = 1; $i <= $length; $i++) {
            $temp = mt_rand() % 36;
            if ($temp < 10) {
                $temp += 48;     // 0 = chr(48), 9 = chr(57)
            } else {
                $temp += 87;     // a = chr(97), z = chr(122)
            }
            $pass .= chr($temp);
        }
        return $pass;
    }


    /**
    * Check if Email-Adress is valid and reachable
    *
    * @param    string  Email-Adress to check
    * @return   bool Email-Adress valid and reachable?
    */
    private function checkMail($Email)
    {
        // Adress correct?
        if (!$this->validator->ValidateEmailAddress($Email)) {
            $this->msg .= 'error§' . _('E-Mail-Adresse syntaktisch falsch!') . '§';
            return false;
        }

        // E-Mail reachable?
        if (!$this->validator->ValidateEmailHost($Email)) {
            // Mailserver nicht erreichbar, ablehnen
            $this->msg .= 'error§' . _('Mailserver ist nicht erreichbar!') . '§';
            return false;
        }

        if (!$this->validator->ValidateEmailBox($Email)) {
            // Nutzer unbekannt, ablehnen
            $this->msg .= 'error§' . sprintf(_('E-Mail an <em>%s</em> ist nicht zustellbar!'), $Email) . '§';
            return false;
        }

        return true;
    }

    /**
    * Create a new studip user with the given parameters
    *
    * @param    array   structure: array('string table_name.field_name'=>'string value')
    * @return   bool Creation successful?
    */
    public function createNewUser($newuser)
    {
        global $perm;

        // Do we have permission to do so?
        if (!$perm->have_perm('admin')) {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung Accounts anzulegen.') . '§';
            return false;
        }

        if (!$perm->is_fak_admin() && $newuser['auth_user_md5.perms'] === 'admin') {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung <em>>Admin-Accounts</em> anzulegen.') . '§';
            return false;
        }

        if (!$perm->have_perm('root') && $newuser['auth_user_md5.perms'] === 'root') {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung <em>Root-Accounts</em> anzulegen.') . '§';
            return false;
        }

        // Do we have all necessary data?
        if (empty($newuser['auth_user_md5.username']) || empty($newuser['auth_user_md5.perms']) || empty($newuser['auth_user_md5.Email'])) {
            $this->msg .= 'error§' . _('Bitte geben Sie <em>Username</em>, <em>Status</em> und <em>E-Mail</em> an!') . '§';
            return false;
        }

        // Is the username correct?
        if (!$this->validator->ValidateUsername($newuser['auth_user_md5.username'])) {
            $this->msg .= 'error§' .  _('Der gewählte Benutzername ist zu kurz oder enthält unzulässige Zeichen!') . '§';
            return false;
        }

        // Can we reach the email?
        if (!$this->checkMail($newuser['auth_user_md5.Email'])) {
            return false;
        }

        if (!$newuser['auth_user_md5.auth_plugin']) {
            $newuser['auth_user_md5.auth_plugin'] = 'standard';
        }

        // Store new values in internal array
        $this->getFromDatabase(null);
        $this->user_data->setData($newuser);

        if ($this->user_data['auth_user_md5.auth_plugin'] === 'standard') {
            $password = $this->generate_password(8);
            $this->user_data['auth_user_md5.password'] = self::getPwdHasher()->HashPassword($password);
        }

        // Does the user already exist?
        // NOTE: This should be a transaction, but it is not...
        $temp = User::findByUsername($newuser['auth_user_md5.username']);
        if ($temp) {
            $this->msg .= 'error§' . sprintf(_('BenutzerIn <em>%s</em> ist schon vorhanden!'), $newuser['auth_user_md5.username']) . '§';
            return false;
        }

        if (!$this->storeToDatabase()) {
            $this->msg .= 'error§' . sprintf(_('BenutzerIn "%s" konnte nicht angelegt werden.'), $newuser['auth_user_md5.username']) . '§';
            return false;
        }

        $this->msg .= 'msg§' . sprintf(_('BenutzerIn "%s" angelegt.'), $newuser['auth_user_md5.username']) . '§';

        // Automated entering new users, based on their status (perms)
        $result = AutoInsert::instance()->saveUser($this->user_data['auth_user_md5.user_id'], $this->user_data['auth_user_md5.perms']);

        foreach ($result['added'] as $item) {
            $this->msg .= 'msg§' . sprintf(_('Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt.'), $item) . '§';
        }
        foreach ($result['removed'] as $item) {
            $this->msg .= 'msg§' . sprintf(_('Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt.'), $item) . '§';
        }

        // include language-specific subject and mailbody
        $user_language = $this->user_data['user_info.preferred_language'] ?: Config::get()->DEFAULT_LANGUAGE;

        $Zeit = strftime('%x, %X');
        include "locale/{$GLOBALS['INSTALLED_LANGUAGES'][$user_language]['path']}/LC_MAILS/create_mail.inc.php";

        // send mail
        StudipMail::sendMessage($this->user_data['auth_user_md5.Email'], $subject, $mailbody);

        // add default visibility settings
        Visibility::createDefaultCategories($this->user_data['auth_user_md5.user_id']);

        return true;
    }

    /**
     * Create a new preliminary studip user with the given parameters
     *
     * @param    array   structure: array('string table_name.field_name'=>'string value')
     * @return   bool Creation successful?
     */
    public function createPreliminaryUser($newuser)
    {
        global $perm;

        $this->getFromDatabase(null);
        $this->user_data->setData($newuser);
        // Do we have permission to do so?
        if (!$perm->have_perm('admin')) {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung Accounts anzulegen.') . '§';
            return false;
        }
        if (in_array($this->user->perms, words('root admin'))) {
            $this->msg .= 'error§' . _('Es können keine vorläufigen Administrationsaccounts angelegt werden.') . '§';
            return false;
        }
        if (!$this->user->id) {
            $this->user->setId($this->user->getNewId());
        }
        if (!$this->user->username) {
            $this->user->username = $this->user->id;
        }
        $this->user->auth_plugin = null;
        $this->user->visible = 'never';

        // Do we have all necessary data?
        if (empty ($this->user->perms) || empty ($this->user->vorname) || empty ($this->user->nachname)) {
            $this->msg .= 'error§' . _('Bitte geben Sie <em>Status</em>, <em>Vorname</em> und <em>Nachname</em> an!') . '§';
            return false;
        }

        // Is the username correct?
        if (!$this->validator->ValidateUsername($this->user->username)) {
            $this->msg .= 'error§' .  _('Der gewählte Benutzername ist zu kurz oder enthält unzulässige Zeichen!') . '§';
            return false;
        }

        // Does the user already exist?
        // NOTE: This should be a transaction, but it is not...
        $temp = User::findByUsername($this->user->username);
        if ($temp) {
            $this->msg .= 'error§' . sprintf(_('BenutzerIn <em>%s</em> ist schon vorhanden!'), $this->user->username) . '§';
            return false;
        }

        if (!$this->storeToDatabase()) {
            $this->msg .= 'error§' . sprintf(_('BenutzerIn "%s" konnte nicht angelegt werden.'), $this->user->username) . '§';
            return false;
        }

        $this->msg .= 'msg§' . sprintf(_('BenutzerIn "%s" (vorläufig) angelegt.'), $this->user->username) . '§';

        // add default visibility settings
        Visibility::createDefaultCategories($this->user->id);

        return true;
    }

    /**
    * Change an existing studip user according to the given parameters
    *
    * @param    array   structure: array('string table_name.field_name'=>'string value')
    * @return   bool Change successful?
    */
    public function changeUser($newuser)
    {
        global $perm;

        // Do we have permission to do so?
        if (!$perm->have_perm('admin')) {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung Accounts zu verändern.') . '§';
            return false;
        }

        if (!$perm->is_fak_admin() && $newuser['auth_user_md5.perms'] === 'admin') {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung, <em>Admin-Accounts</em> anzulegen.') . '§';
            return false;
        }

        if (!$perm->have_perm('root') && $newuser['auth_user_md5.perms'] === 'root') {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung, <em>Root-Accounts</em> anzulegen.') . '§';
            return false;
        }

        if (!$perm->have_perm('root')) {
            if (!$perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] === 'admin') {
                $this->msg .= 'error§' . _('Sie haben keine Berechtigung <em>Admin-Accounts</em> zu verändern.') . '§';
                return false;
            }

            if ($this->user_data['auth_user_md5.perms'] === 'root') {
                $this->msg .= 'error§' . _('Sie haben keine Berechtigung <em>Root-Accounts</em> zu verändern.') . '§';
                return false;
            }

            if ($perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] === 'admin') {
                if (!$this->adminOK()) {
                    $this->msg .= 'error§' . _('Sie haben keine Berechtigung diesen Admin-Account zu verändern.') . '§';
                    return false;
                }
            }
        }

        // active dozent? (ignore the studygroup guys)
        $status = studygroup_sem_types();

        if (empty($status)) {
            $count = 0;
        } else {
            $query = "SELECT COUNT(*)
                      FROM seminar_user AS su
                      LEFT JOIN seminare AS s USING (Seminar_id)
                      WHERE su.user_id = ?
                        AND s.status NOT IN (?)
                        AND su.status = 'dozent'
                        AND (SELECT COUNT(*) FROM seminar_user su2 WHERE Seminar_id = su.Seminar_id AND su2.status = 'dozent') = 1
                      GROUP BY user_id";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->user_data['auth_user_md5.user_id'],
                $status,
            ]);
            $count = $statement->fetchColumn();
        }
        if ($count && isset($newuser['auth_user_md5.perms']) && $newuser['auth_user_md5.perms'] !== 'dozent') {
            $this->msg .= 'error§' . sprintf(_('Der Benutzer <em>%s</em> ist alleiniger Dozent in %s aktiven Veranstaltungen und kann daher nicht in einen anderen Status versetzt werden!'), $this->user_data['auth_user_md5.username'], $count) . '§';
            return false;
        }

        // active admin?
        if ($this->user_data['auth_user_md5.perms'] === 'admin' && $newuser['auth_user_md5.perms'] !== 'admin') {
            // count number of institutes where the user is admin
            $query = "SELECT COUNT(*)
                      FROM user_inst
                      WHERE user_id = ? AND inst_perms = 'admin'
                      GROUP BY Institut_id";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);

            // if there are institutes with admin-perms, add error-message and deny change
            if ($count = $statement->fetchColumn()) {
                $this->msg .= 'error§'. sprintf(_('Der Benutzer <em>%s</em> ist Admin in %s Einrichtungen und kann daher nicht in einen anderen Status versetzt werden!'), $this->user_data['auth_user_md5.username'], $count) . '§';
                return false;
            }
        }

        // Is the username correct?
        if (isset($newuser['auth_user_md5.username'])) {
            if ($this->user_data['auth_user_md5.username'] != $newuser['auth_user_md5.username']) {
                if (!$this->validator->ValidateUsername($newuser['auth_user_md5.username'])) {
                    $this->msg .= 'error§' . _('Der gewählte Benutzername ist zu kurz oder enthält unzulässige Zeichen!') . '§';
                    return false;
                }
                $check_uname = StudipAuthAbstract::CheckUsername($newuser['auth_user_md5.username']);
                if ($check_uname['found']) {
                    $this->msg .= 'error§' . _('Der Benutzername wird bereits von einem anderen Benutzer verwendet. Bitte wählen Sie einen anderen Benutzernamen!') . '§';
                    return false;
                } else {
                    //$this->msg .= "info§" . $check_uname['error'] ."§";
                }
            } else
            unset($newuser['auth_user_md5.username']);
        }

        // Can we reach the email?
        if (isset($newuser['auth_user_md5.Email'])) {
            if (!$this->checkMail($newuser['auth_user_md5.Email'])) {
                return false;
            }
        }

        // Store changed values in internal array if allowed
        $old_perms = $this->user_data['auth_user_md5.perms'];
        $auth_plugin = $this->user_data['auth_user_md5.auth_plugin'];
        foreach ($newuser as $key => $value) {
            if (!StudipAuthAbstract::CheckField($key, $auth_plugin)) {
                $this->user_data[$key] = $value;
            } else {
                $this->msg .= 'error§' .  sprintf(_('Das Feld <em>%s</em> können Sie nicht ändern!'), $key) . '§';
                return false;
            }
        }

        if (!$this->storeToDatabase()) {
            $this->msg .= 'info§' . _('Es wurden keine Veränderungen der Grunddaten vorgenommen.') . '§';
            return false;
        }

        $this->msg .= 'msg§' . sprintf(_('Benutzer "%s" verändert.'), $this->user_data['auth_user_md5.username']) . '§';
        if ($auth_plugin !== null) {
            // Automated entering new users, based on their status (perms)
            $result = AutoInsert::instance()->saveUser($this->user_data['auth_user_md5.user_id'], $newuser['auth_user_md5.perms']);
            foreach ($result['added'] as $item) {
                $this->msg .= 'msg§' . sprintf(_('Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt.'), $item) . '§';
            }
            foreach ($result['removed'] as $item) {
                $this->msg .= 'msg§' . sprintf(_('Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt.'), $item) . '§';
            }
            // include language-specific subject and mailbody
            $user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);
            $Zeit = strftime('%x, %X');
            include "locale/{$user_language}/LC_MAILS/change_mail.inc.php";

            // send mail
            StudipMail::sendMessage($this->user_data['auth_user_md5.Email'], $subject, $mailbody);
        }
        // Upgrade to admin or root?
        if (in_array($newuser['auth_user_md5.perms'], ['admin', 'root'])) {
            $this->re_sort_position_in_seminar_user();

            // delete all seminar entries
            $query = "SELECT seminar_id FROM seminar_user WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            $seminar_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

            $query = "DELETE FROM seminar_user WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus Veranstaltungen gelöscht.'), $count) . '§';
                array_map('update_admission', $seminar_ids);
            }
            // delete all entries from waiting lists
            $query = "SELECT seminar_id FROM admission_seminar_user WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            $seminar_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

            $query = "DELETE FROM admission_seminar_user WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus Wartelisten gelöscht.'), $count) . '§';
                array_map('update_admission', $seminar_ids);
            }
            // delete 'Studiengaenge'
            $query = "DELETE FROM user_studiengang WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Zuordnungen zu Studiengängen gelöscht.'), $count) . '§';
            }
            // delete all private appointments of this user
            if ($count = delete_range_of_dates($this->user_data['auth_user_md5.user_id'], false)) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus den Terminen gelöscht.'), $count) . '§';
            }
        }

        if ($newuser['auth_user_md5.perms'] === 'admin') {

            $this->logInstUserDel($this->user_data['auth_user_md5.user_id'], "inst_perms != 'admin'");
            $query = "DELETE FROM user_inst WHERE user_id = ? AND inst_perms != 'admin'";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus MitarbeiterInnenlisten gelöscht.'), $count) . '§';
            }
        }
        if ($newuser['auth_user_md5.perms'] === 'root') {
            $this->logInstUserDel($this->user_data['auth_user_md5.user_id']);

            $query = "DELETE FROM user_inst WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus MitarbeiterInnenlisten gelöscht.'), $count) . '§';
            }
        }

        return true;
    }

    private function logInstUserDel($user_id, $condition = null)
    {
        $query = "SELECT Institut_id FROM user_inst WHERE user_id = ?";
        if (isset($condition)) {
            $query .= ' AND ' . $condition;
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        while ($institute_id = $statement->fetchColumn()) {
            StudipLog::log('INST_USER_DEL', $institute_id, $user_id);
        }
    }

    /**
    * Create a new password and mail it to the user
    *
    * @return   bool Password change successful?
    */
    public function setPassword()
    {
        global $perm;

        // Do we have permission to do so?
        if (!$perm->have_perm('admin')) {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung Accounts zu verändern.') . '§';
            return false;
        }

        if (!$perm->have_perm('root')) {
            if ($this->user_data['auth_user_md5.perms'] === "root") {
                $this->msg .= 'error§' . _('Sie haben keine Berechtigung <em>Root-Accounts</em> zu verändern.') . '§';
                return false;
            }
            if ($perm->is_fak_admin() && $this->user_data['auth_user_md5.perms'] === 'admin') {
                if (!$this->adminOK()) {
                    $this->msg .= 'error§' . _('Sie haben keine Berechtigung diesen Admin-Account zu verändern.') . '§';
                    return false;
                }
            }
        }

        // Can we reach the email?
        if (!$this->checkMail($this->user_data['auth_user_md5.Email'])) {
            return false;
        }

        $password = $this->generate_password(8);
        $this->user_data['auth_user_md5.password'] = self::getPwdHasher()->HashPassword($password);

        if (!$this->storeToDatabase()) {
            $this->msg .= 'info§' . _('Es wurden keine Veränderungen vorgenommen.') . '§';
        }

        $this->msg .= 'msg§' . _('Das Passwort wurde neu gesetzt.') . '§';

        // include language-specific subject and mailbody
        $user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);
        $Zeit = strftime('%x, %X');
        include "locale/$user_language/LC_MAILS/password_mail.inc.php";

        // send mail
        StudipMail::sendMessage($this->user_data['auth_user_md5.Email'], $subject, $mailbody);
        StudipLog::log('USER_NEWPWD', $this->user_data['auth_user_md5.user_id']);

        return true;
    }

    /**
    * Delete an existing user from the database and tidy up
    *
    * @param    bool delete all documents in course context belonging to the user
    * @param    bool delete all course content belonging to the user
    * @param    bool delete all personal documents belonging to the user
    * @param    bool delete all personal content belonging to the user
    * @param    bool delete all names identifying the user
    * @param    bool delete all memberships of the user
    * @return   bool Removal successful?
    */
    public function deleteUser($delete_documents = true, $delete_content_from_course = true, $delete_personal_documents = true, $delete_personal_content = true, $delete_names = true, $delete_memberships = true)
    {
        global $perm;

        // Do we have permission to do so?
        if (!$perm->have_perm('admin')) {
            $this->msg .= 'error§' . _('Sie haben keine Berechtigung Accounts zu löschen.') . '§';
            return FALSE;
        }

        if (!$perm->have_perm('root')) {
            if ($this->user_data['auth_user_md5.perms'] === 'root') {
                $this->msg .= 'error§' . _('Sie haben keine Berechtigung <em>Root-Accounts</em> zu löschen.') . '§';
                return false;
            }
            if ($this->user_data['auth_user_md5.perms'] === 'admin' && !$this->adminOK()) {
                $this->msg .= 'error§' . _('Sie haben keine Berechtigung diesen Admin-Account zu löschen.') . '§';
                return false;
            }
        }

        // active dozent?
        $query = "SELECT COUNT(*)
                  FROM (
                      SELECT 1
                      FROM `seminar_user` AS `su1`
                      -- JOIN seminar_user to check for other teachers
                      INNER JOIN `seminar_user` AS `su2`
                        ON (`su1`.`seminar_id` = `su2`.`seminar_id` AND `su2`.`status` = 'dozent')
                      -- JOIN seminare to check the status for studygroup mode
                      INNER JOIN `seminare`
                        ON (`su1`.`seminar_id` = `seminare`.`seminar_id`)
                      WHERE `su1`.`user_id` = :user_id
                        AND `su1`.`status` = 'dozent'
                        AND `seminare`.`status` NOT IN (
                            -- Select all status ids for studygroups
                            SELECT `id`
                            FROM `sem_classes`
                            WHERE `studygroup_mode` = 1
                        )
                      GROUP BY `su1`.`seminar_id`
                      HAVING COUNT(*) = 1
                      ORDER BY NULL
                  ) AS `sub`";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $this->user_data['auth_user_md5.user_id']);
        $statement->execute();
        $active_count = $statement->fetchColumn() ?: 0;

        if ($active_count && $delete_memberships) {
            $this->msg .= 'error§' . sprintf(_('<em>%s</em> ist Lehrkraft in %s aktiven Veranstaltungen und kann daher nicht gelöscht werden.'), $this->user_data['auth_user_md5.username'], $active_count) . '§';
            return false;
        //founder of studygroup?
        } elseif (Config::get()->STUDYGROUPS_ENABLE) {
            $status = studygroup_sem_types();

            if (empty($status)) {
                $group_ids = [];
            } else {
                $query = "SELECT Seminar_id
                          FROM seminare AS s
                          LEFT JOIN seminar_user AS su USING (Seminar_id)
                          WHERE su.status = 'dozent' AND su.user_id = ? AND s.status IN (?)";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    $this->user_data['auth_user_md5.user_id'],
                    $status,
                ]);
                $group_ids = $statement->fetchAll(PDO::FETCH_COLUMN);
            }

            foreach ($group_ids as $group_id) {
                $sem = Seminar::GetInstance($group_id);
                if (StudygroupModel::countMembers($group_id) > 1) {
                    // check whether there are tutors or even autors that can be promoted
                    $tutors = $sem->getMembers('tutor');
                    $autors = $sem->getMembers('autor');
                    if (count($tutors) > 0) {
                        $new_founder = current($tutors);
                        StudygroupModel::promote_user($new_founder['username'], $sem->getId(), 'dozent');
                        continue;
                    }
                    // if not promote an autor
                    elseif (count($autors) > 0) {
                        $new_founder = current($autors);
                        StudygroupModel::promote_user($new_founder['username'], $sem->getId(), 'dozent');
                        continue;
                    }
                // since no suitable successor was found, we are allowed to remove the studygroup
                } else {
                    $sem->delete();
                }
                unset($sem);
            }
        }

        // store user preferred language for sending mail
        $user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);

        // Load privacy plugins to ensure all event handlers can react to the
        // UserDataDidRemove event
        PluginEngine::getPlugins('PrivacyPlugin');

        // delete user from instituts
        $this->logInstUserDel($this->user_data['auth_user_md5.user_id']);

        if ($delete_memberships) {
            $query = "DELETE FROM user_inst WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus MitarbeiterInnenlisten gelöscht.'), $count) . '§';
            }

            // delete user from Statusgruppen
            if ($count = StatusgruppeUser::deleteBySQL('user_id = ?', [$this->user_data['auth_user_md5.user_id']])) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus Funktionen / Gruppen gelöscht.'), $count) . '§';
            }

            // delete user from archiv
            $query = "DELETE FROM archiv_user WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus den Zugriffsberechtigungen für das Archiv gelöscht.'), $count) . '§';
            }

            // delete 'Studiengaenge'
            $query = "DELETE FROM user_studiengang WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Zuordnungen zu Studiengängen gelöscht.'), $count) . '§';
            }


            // kill all the ressources that are assigned to the user (and all the linked or subordinated stuff!)
            if (Config::get()->RESOURCES_ENABLE) {
                $killAssign = new DeleteResourcesUser($this->user_data['auth_user_md5.user_id']);
                $killAssign->delete();
            }

            $this->re_sort_position_in_seminar_user();

            // delete user from seminars (postings will be preserved)
            $query = "DELETE FROM seminar_user WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if ($count = $statement->rowCount()) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge aus Veranstaltungen gelöscht.'), $count) . '§';
            }

            // delete visibility settings
            Visibility::removeUserPrivacySettings($this->user_data['auth_user_md5.user_id']);

            // delete deputy entries if necessary
            $query = "DELETE FROM deputies WHERE ? IN (user_id, range_id)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            $deputyEntries = $statement->rowCount();
            if ($deputyEntries) {
                $this->msg .= 'info§' . sprintf(_('%s Einträge in den Vertretungseinstellungen gelöscht.'), $deputyEntries) . '§';
            }

            // delete all remaining user data
            $queries = [
                "DELETE FROM user_userdomains WHERE user_id = ?",
            ];
            foreach ($queries as $query) {
                DBManager::get()->execute($query, [$this->user_data['auth_user_md5.user_id']]);
            }
            NotificationCenter::postNotification('UserDataDidRemove', $this->user_data['auth_user_md5.user_id'], 'memberships');
        }

        // delete documents of this user
        if ($delete_documents) {
            $db_filecount = FileRef::deleteBySQL('user_id = ?', [$this->user_data['auth_user_md5.user_id']]);
            if ($db_filecount > 0) {
                $this->msg .= 'info§' . sprintf(_('%s Dateien aus Veranstaltungen und Einrichtungen gelöscht.'), $db_filecount) . '§';
            }
            NotificationCenter::postNotification('UserDataDidRemove', $this->user_data['auth_user_md5.user_id'], 'course_documents');
        }

        // delete all remaining user data in course context if option selected
        if ($delete_content_from_course) {
            $queries = [
                "DELETE FROM questionnaires WHERE user_id = ?",
                "DELETE FROM questionnaire_answers WHERE user_id = ?",
                "DELETE FROM questionnaire_assignments WHERE user_id = ?",
                "DELETE FROM questionnaire_anonymous_answers WHERE user_id = ?",
                "DELETE FROM etask_assignment_attempts WHERE user_id = ?",
                "DELETE FROM etask_responses WHERE user_id = ?",
                "DELETE FROM etask_tasks WHERE user_id = ?",
                "DELETE FROM etask_tests WHERE user_id = ?",
            ];
            foreach ($queries as $query) {
                DBManager::get()->execute($query, [$this->user_data['auth_user_md5.user_id']]);
            }
            NotificationCenter::postNotification('UserDataDidRemove', $this->user_data['auth_user_md5.user_id'], 'course_contents');
        }

        if ($delete_personal_documents) {
            $user_folder = Folder::findTopFolder($this->user->id);
            $this->msg .= 'info§' . _('Persönlicher Dateibereich gelöscht.') . '§';
            $user_folder->delete();
            NotificationCenter::postNotification('UserDataDidRemove', $this->user_data['auth_user_md5.user_id'], 'personal_documents');
        }

        if ($delete_personal_content) {
            $this->msg .= $this->deletePersonalData($this->user_data['auth_user_md5.user_id']);
            NotificationCenter::postNotification('UserDataDidRemove', $this->user_data['auth_user_md5.user_id'], 'personal_contents');
        }

        if ($delete_names) {
            $query = "UPDATE auth_user_md5
                      SET username = ?, Vorname = NULL, Nachname = NULL, Email = NULL
                      WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                md5($this->user_data['auth_user_md5.username']),
                $this->user_data['auth_user_md5.user_id']
            ]);
            if ($statement->rowCount() > 0) {
                $msg .= 'info§' . _('Benutzername anonymisiert.') . '§';
            }
            NotificationCenter::postNotification('UserDataDidRemove', $this->user_data['auth_user_md5.user_id'], 'names');
        }

        if ($delete_personal_documents && $delete_personal_content && $delete_names && $delete_memberships) {
            // delete Stud.IP account
            $query = "DELETE FROM user_info WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);

            $query = "DELETE FROM auth_user_md5 WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user_data['auth_user_md5.user_id']]);
            if (!$statement->rowCount()) {
                $this->msg .= 'error§<em>' . _('Fehler:') . '</em> ' . $query . '§';
                return false;
            } else {
                $this->msg .= 'msg§' . sprintf(_('Benutzer "%s" gelöscht.'), $this->user_data['auth_user_md5.username']) . '§';
            }
            StudipLog::log('USER_DEL', $this->user_data['auth_user_md5.user_id'], null, sprintf('%s %s (%s)', $this->user_data['auth_user_md5.Vorname'], $this->user_data['auth_user_md5.Nachname'], $this->user_data['auth_user_md5.username'])); //log with Vorname Nachname (username) as info string

            // Can we reach the email?
            if ($this->checkMail($this->user_data['auth_user_md5.Email'])) {
                // include language-specific subject and mailbody
                $Zeit = strftime('%x, %X');
                include "locale/$user_language/LC_MAILS/delete_mail.inc.php";

                // send mail
                StudipMail::sendMessage($this->user_data['auth_user_md5.Email'], $subject, $mailbody);
            }

            // Trigger delete on sorm object which will fire notifications
            //
            // TODO: Remove everything from this method that would also be
            //       deleted in User::delete() (TODO!!!)
            $this->user->delete();

            unset($this->user_data);
        }

        return true;
    }

    /**
    * Delete personal userdata
    *
    * @param    string $user_id    the user which should be retrieved
    * @return   string Removal messages
    */
    private function deletePersonalData($user_id)
    {
        $msg = '';

        // delete the datafields
        $localEntries = DataFieldEntry::removeAll($user_id);

        // delete all blubber entrys
        $query = "DELETE blubber, blubber_mentions, blubber_reshares, blubber_streams
                  FROM blubber
                  LEFT JOIN blubber_mentions USING (user_id)
                  LEFT JOIN blubber_reshares USING (user_id)
                  LEFT JOIN blubber_streams USING (user_id)
                  WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        if ($count = $statement->rowCount()) {
            $msg .= 'info§' . sprintf(_('%s Blubber gelöscht.'), $count) . '§';
        }

        // delete user from waiting lists
        $query = "SELECT seminar_id FROM admission_seminar_user WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $seminar_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        $query = "DELETE FROM admission_seminar_user WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        if ($count = $statement->rowCount()) {
            $msg .= 'info§' . sprintf(_('%s Einträge aus Wartelisten gelöscht.'), $count) . '§';
            array_map('update_admission', $seminar_ids);
        }

        // delete all personal news from this user
        if ($count = StudipNews::DeleteNewsByAuthor($user_id)) {
            $msg .= 'info§' . sprintf(_('%s Einträge aus den Ankündigungen gelöscht.'), $count) . '§';
        }
        if ($count = StudipNews::DeleteNewsRanges($user_id)) {
            $msg .= 'info§' . sprintf(_('%s Verweise auf Ankündigungen gelöscht.'), $count) . '§';
        }

        //delete entry in news_rss_range
        StudipNews::UnsetRssId($user_id);

        // delete all private appointments of this user
        if (Config::get()->CALENDAR_ENABLE) {
            $count = CalendarEvent::deleteBySQL('range_id = ?', [$user_id]);
            if ($count) {
                $msg .= 'info§' . sprintf(_('%s Einträge aus den Terminen gelöscht.'), $count) . '§';
            }
            // delete membership in group calendars
            if (Config::get()->CALENDAR_GROUP_ENABLE) {
                $count = CalendarUser::deleteBySQL(
                    'owner_id = :user_id OR user_id = :user_id',
                    [':user_id' => $user_id]
                );
                if ($count) {
                    $msg .= 'info§' . sprintf(_('%s Verknüpfungen mit Gruppenterminkalendern gelöscht.'), $count) . '§';
                }
            }
        }

        // delete all messages send or received by this user
        $messaging = new messaging();
        $messaging->delete_all_messages($user_id);

        // delete user from all foreign adressbooks and empty own adressbook
        $count = Contact::deleteBySQL('user_id = ?', [$user_id]);
        if ($count > 0) {
            $msg .= 'info§' . sprintf(_('%s Einträge aus Adressbüchern gelöscht.'), $count) . '§';
        }
        $count = Contact::deleteBySQL('owner_id = ?', [$user_id]);
        if ($count) {
            $msg .= 'info§' . sprintf(_('Adressbuch mit %d Einträgen gelöscht.'), $count) . '§';
        }

        // delete users groups
        Statusgruppen::deleteBySQL('range_id = ?', [$user_id]);

        // remove user from any groups
        StatusgruppeUser::deleteBySQL('user_id = ?', [$user_id]);

        // delete user config values
        ConfigValue::deleteBySQL('range_id = ?', [$user_id]);

        // delete all remaining user data
        $queries = [
            "DELETE FROM kategorien WHERE range_id = ?",
            "DELETE FROM user_visibility WHERE user_id = ?",
            "DELETE FROM user_online WHERE user_id = ?",
            "DELETE FROM auto_insert_user WHERE user_id = ?",
            "DELETE FROM roles_user WHERE userid = ?",
            "DELETE FROM schedule WHERE user_id = ?",
            "DELETE FROM schedule_seminare WHERE user_id = ?",
            "DELETE FROM termin_related_persons WHERE user_id = ?",
            "DELETE FROM priorities WHERE user_id = ?",
            "DELETE FROM api_oauth_user_mapping WHERE user_id = ?",
            "DELETE FROM api_user_permissions WHERE user_id = ?",
            "DELETE FROM eval_user WHERE user_id = ?",
            "DELETE FROM evalanswer_user WHERE user_id = ?",
            "DELETE FROM help_tour_user WHERE user_id = ?",
            "DELETE FROM personal_notifications_user WHERE user_id = ?",

            "DELETE FROM comments WHERE user_id = ?",
            "DELETE questionnaires FROM questionnaires LEFT JOIN questionnaire_assignments qa USING (`questionnaire_id`) WHERE qa.range_id = ?",
            "DELETE questionnaire_answers FROM questionnaire_answers LEFT JOIN questionnaire_questions USING (`question_id`) LEFT JOIN questionnaire_assignments qa USING (`questionnaire_id`) WHERE qa.range_id = ?",
            "DELETE questionnaire_anonymous_answers FROM questionnaire_anonymous_answers LEFT JOIN questionnaire_assignments qa USING (`questionnaire_id`) WHERE qa.range_id = ?",
            "DELETE FROM questionnaire_assignments WHERE user_id = ?",
            "DELETE etask_assignment_attempts FROM etask_assignment_attempts LEFT JOIN etask_assignments ea ON (`assignment_id` = ea.id) WHERE ea.range_type = 'user' AND user_id = ?",
            "DELETE etask_responses FROM etask_responses LEFT JOIN etask_assignments ea ON (`assignment_id` = ea.id) WHERE ea.range_type = 'user' AND user_id = ?",
            "DELETE etask_tasks FROM etask_tasks LEFT JOIN etask_test_tasks tt ON (etask_tasks.id = tt.task_id) LEFT JOIN etask_assignments ea ON (tt.`test_id` = ea.test_id) WHERE ea.range_type = 'user' AND  user_id = ?",
            "DELETE etask_tests FROM etask_tests LEFT JOIN etask_assignments ea ON (`test_id` = ea.test_id) WHERE ea.range_type = 'user' AND user_id = ?",

            "UPDATE forum_entries SET author = '' WHERE user_id = ?",
            "UPDATE auth_user_md5 SET visible = 'never' WHERE user_id = ?",

            "REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES(?, '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');"
        ];
        foreach ($queries as $query) {
            DBManager::get()->execute($query, [$user_id]);
        }

        // Clean up orphaned items
        $queries = [
            "DELETE FROM personal_notifications WHERE personal_notification_id NOT IN (
                SELECT personal_notification_id FROM personal_notifications_user
            )",
        ];
        foreach ($queries as $query) {
            DBManager::get()->exec($query);
        }

        object_kill_visits($user_id);
        object_kill_views($user_id);

        // delete picture
        $avatar = Avatar::getAvatar($user_id);
        if ($avatar->is_customized()) {
            $avatar->reset();
            $msg .= 'info§' . _('Bild gelöscht.') . '§';
        }

        //delete connected users
        if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
            if (ELearningUtils::initElearningInterfaces()) {
                foreach ($GLOBALS['connected_cms'] as $cms){
                    if ($cms->auth_necessary && $cms->user instanceOf ConnectedUser) {
                        $user_auto_create = $cms->USER_AUTO_CREATE;
                        $cms->USER_AUTO_CREATE = false;
                        $userclass = mb_strtolower(get_class($cms->user));
                        $connected_user = new $userclass($cms->cms_type, $user_id);
                        if ($connected_user->deleteUser() && $connected_user->is_connected) {
                            $msg .= 'info§' . sprintf(_('Der verknüpfte Nutzer %s wurde im System %s gelöscht.'), $connected_user->login, $connected_user->cms_type) . '§';
                        }
                        $cms->USER_AUTO_CREATE = $user_auto_create;
                    }
                }
            }
        }

        return $msg;
    }

    private function adminOK()
    {
        static $ok = null;

        if ($ok === null) {
            $query = "SELECT COUNT(a.Institut_id) = COUNT(c.inst_perms)
                      FROM user_inst AS a
                      LEFT JOIN Institute b ON (a.Institut_id = b.Institut_id AND b.Institut_id != b.fakultaets_id)
                      LEFT JOIN user_inst AS c ON (b.fakultaets_id = c.Institut_id AND c.user_id = ?
                                                  AND c.inst_perms = 'admin')
                      WHERE a.user_id = ? AND a.inst_perms = 'admin'";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $GLOBALS['auth']->auth['uid'],
                $this->user_data['auth_user_md5.user_id'],
            ]);
            $ok = $statement->fetchColumn();
        }

        return $ok;
    }

    private function re_sort_position_in_seminar_user()
    {
        $query = "SELECT Seminar_id, position, status
                  FROM seminar_user
                  WHERE user_id = ? AND status IN ('tutor', 'dozent')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->user_data['auth_user_md5.user_id']]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($row['status'] === 'tutor') {
                re_sort_tutoren($row['Seminar_id'], $row['position']);
            } else if ($row['status'] === 'dozent') {
                re_sort_dozenten($row['Seminar_id'], $row['position']);
            }
        }
    }

    /**
    * Change an existing user password
    *
    * @param string $password
    * @return bool change successful?
    */
    public function changePassword($password)
    {
        global $perm;

        $this->user_data['auth_user_md5.password'] = self::getPwdHasher()->HashPassword($password);
        $this->storeToDatabase();

        $this->msg .= 'msg§' . _('Das Passwort wurde neu gesetzt.') . '§';

        // include language-specific subject and mailbody
        $user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);
        $Zeit = strftime('%x, %X');
        include "locale/$user_language/LC_MAILS/password_mail.inc.php";

        // send mail
        StudipMail::sendMessage($this->user_data['auth_user_md5.Email'], $subject, $mailbody);

        return TRUE;
    }
}
