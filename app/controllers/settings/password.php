<?php
/**
 * Settings_PasswordController - Administration of all user and password related
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

class Settings_PasswordController extends Settings_SettingsController
{
    /**
     * Set up this controller.
     *
     * @param String $action         Name of the action to be invoked
     * @param Array $args            Arguments to be passed to the action method
     * @throws AccessDeniedException if the current user is not allowed to
     *                               change the password
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$this->shallChange('auth_user_md5.password', 'password')) {
            throw new AccessDeniedException();
        }

        PageLayout::setHelpKeyword('Basis.HomepagePersönlicheDaten');
        PageLayout::setTitle(_('Passwort ändern'));
        Navigation::activateItem('profile/edit/password');
        SkipLinks::addIndex(_('Passwort ändern'), 'layout_content');
    }

    /**
     * Displays the password change form.
     */
    public function index_action()
    {
    }

    /**
     * Stores a new password for a user.
     */
    public function store_action()
    {
        $this->check_ticket();

        $errors = [];
        $hasher = UserManagement::getPwdHasher();


        $password = Request::get('new_password');
        $confirm  = Request::get('new_password_confirm');
        if (!($hasher->CheckPassword(md5(Request::get('password')), $this->user['password'])
              || $hasher->CheckPassword(Request::get('password'), $this->user['password'])
              || (mb_strlen($this->user['password']) == 32 && md5(Request::get('password')) == $this->user['password']))
        ) {
            $errors[] = _('Das aktuelle Passwort wurde nicht korrekt eingegeben.');
        }
        if (!$this->validator->ValidatePassword($password)) {
            $errors[] = _('Das Passwort ist zu kurz. Es sollte mindestens 8 Zeichen lang sein.');
        } else if ($password !== $confirm) {
            $errors[] = _('Die Wiederholung Ihres Passworts stimmt nicht mit Ihrer Eingabe überein.');
        } else if ($password == $this->user['username']) {
            $errors[] = _('Das Passwort darf nicht mit dem Nutzernamen übereinstimmen.');
        } else if (str_replace(['.', ' '], '', mb_strtolower($password)) == 'studip') {
            $errors[] = _('Aus Sicherheitsgründen darf das Passwort nicht "Stud.IP" oder eine Abwandlung davon sein.');
        }

        if (count($errors) > 0) {
            PageLayout::postError(_('Bitte überprüfen Sie Ihre Eingabe:'), $errors);
        } else {
            $this->user->password = $hasher->HashPassword($password);
            if ($this->user->store()) {
                PageLayout::postSuccess(_('Das Passwort wurde erfolgreich geändert.'));
            }
        }
        $this->redirect('settings/password');
    }
}
