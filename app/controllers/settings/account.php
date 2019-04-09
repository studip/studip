<?php
/**
 * Settings_AccountController - Administration of all user account related
 * settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'settings.php';

class Settings_AccountController extends Settings_SettingsController
{
    /**
     * Set up this controller
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.HomepagePersönlicheDaten');
        PageLayout::setTitle(_('Persönliche Angaben bearbeiten'));
        Navigation::activateItem('/profile/edit/profile');
        SkipLinks::addIndex(_('Persönliche Angaben bearbeiten'), 'layout_content');

        Sidebar::get()->setImage('sidebar/person-sidebar.png');
    }

    /**
     * Display the account information of a user
     */
    public function index_action()
    {
        $this->locked_info = LockRules::CheckLockRulePermission($this->user['user_id'])
                           ? LockRules::getObjectRule($this->user['user_id'])->description
                           : false;

        $auth = StudipAuthAbstract::GetInstance($this->user->auth_plugin ?: 'standard');
        $this->is_sso = $auth instanceOf StudipAuthSSO;
    }

    /**
     * Stores the account informations of a user
     */
    public function store_action()
    {
        $this->check_ticket();

        $errors = $info = $success = [];
        $logout = false;

        //erstmal die "unwichtigen" Daten
        $geschlecht = Request::int('geschlecht');
        if ($this->shallChange('user_info.geschlecht', 'gender', $geschlecht)) {
            $this->user->geschlecht = $geschlecht;
            $success[] = _('Ihr Geschlecht wurde geändert');
        }

        $title_front = Request::get('title_front') ?: Request::get('title_front_chooser');
        if ($this->shallChange('user_info.title_front', 'title', $title_front)) {
            $this->user->title_front = $title_front;
            $success[] = _('Ihr Titel wurde geändert');
        }

        $title_rear = Request::get('title_rear') ?: Request::get('title_rear_chooser');
        if ($this->shallChange('user_info.title_rear', 'title', $title_rear)) {
            $this->user->title_rear = $title_rear;
            $success[] = _('Ihr nachgestellter Titel wurde geändert');
        }

        if ($this->user->store()) {
            // Inform the user about this change
            setTempLanguage($this->user->user_id);
            $this->postPrivateMessage(_("Ihre persönlichen Daten wurden geändert.\n"));
            restoreLanguage();
        }

        //nur nötig wenn der user selbst seine daten ändert
        if (!$this->restricted) {
            // Vorname verändert ?
            $vorname = trim(Request::get('vorname'));
            if ($this->shallChange('auth_user_md5.Vorname', 'name', $vorname)) {
                // Vorname nicht korrekt oder fehlend
                if (!$this->validator->ValidateName($vorname)) {
                    $errors[] = _('Der Vorname fehlt oder ist unsinnig!');
                } else {
                    $this->user->Vorname = $vorname;
                    $success[] = _('Ihr Vorname wurde geändert!');
                }
            }

            // Nachname verändert ?
            $nachname = trim(Request::get('nachname'));
            if ($this->shallChange('auth_user_md5.Nachname', 'name', $nachname)) {
                // Nachname nicht korrekt oder fehlend
                if (!$this->validator->ValidateName($nachname)) {
                    $errors[] = _('Der Nachname fehlt oder ist unsinnig!');
                } else {
                    $this->user->Nachname = $nachname;
                    $success[] = _('Ihr Nachname wurde geändert!');
                }
            }

            // Username
            $new_username = trim(Request::get('new_username'));
            if ($this->shallChange('auth_user_md5.username', 'username', $new_username)) {
                if (!$this->validator->ValidateUsername($new_username)) {
                    $errors[] = _('Der gewählte Benutzername ist nicht lang genug!');
                } else if (User::countBySql('username = ?', [$new_username]) > 0) {
                    $errors[] =  _('Der Benutzername wird bereits von einem anderen Benutzer verwendet. Bitte wählen Sie einen anderen Benutzernamen!');
                } else {
                    $this->user->username = $new_username;
                    $success[] = _('Ihr Benutzername wurde geändert!');

                    URLHelper::addLinkParam('username', $this->user->username);

                    $logout = true;
                }
            }

            // Email
            $email1 = trim(Request::get('email1'));
            $email2 = trim(Request::get('email2'));
            if ($this->shallChange('auth_user_md5.Email', 'email', $email1)) {
                $auth   = StudipAuthAbstract::GetInstance($this->user->auth_plugin ?: 'standard');
                $is_sso = $auth instanceOf StudipAuthSSO;

                if (!$is_sso && !$auth->isAuthenticated($this->user->username, Request::get('password'))) {
                    $errors[] = _('Das aktuelle Passwort wurde nicht korrekt eingegeben.');
                } elseif ($email1 !== $email2) {
                    $errors[] = _('Die Wiederholung der E-Mail-Adresse stimmt nicht mit Ihrer Eingabe überein.');
                } elseif ($this->user->changeEmail($email1)) {
                    $this->user->email = $email1;
                }
            }

            $this->user->store();
        }


        if (count($errors) > 0) {
            PageLayout::postError(_('Bitte überprüfen Sie Ihre Eingaben:'), $errors);
        } else if (count($success) > 0) {
            PageLayout::postSuccess(_('Ihre persönlichen Angaben wurden geändert.'), $success);
            if (count($info) > 0) {
                PageLayout::postInfo(_('Bitte beachten Sie:'), $info);
            }
        }

        if ($logout) {
            $token = uniqid('logout', true);
            $this->flash['logout-token'] = $token;
            $this->redirect('settings/account/logout?token=' . $token);
        } else {
            $this->redirect('settings/account');
        }
    }

    /**
     * Display an information page that the user has been logged out
     */
    public function logout_action()
    {
        // Check whether this is a valid logout request
        if ($this->flash['logout-token'] !== Request::get('token')) {
            $this->redirect('settings/account');
        }
        $this->username = Request::username('username', $GLOBALS['user']->username);
    }
}
