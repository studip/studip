<?php

/**
 * Seminar_Register_Auth.class.php
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2000 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class Seminar_Register_Auth extends Seminar_Auth
{
    /**
     * @var string
     */
    protected $mode = "reg";

    protected static $magic = 'dsdfjhgretha';

    /**
     *
     */
    public function auth_registerform()
    {
        $this->check_environment();

        // load the default set of plugins
        PluginEngine::loadPlugins();

        if (!$_COOKIE[get_class($GLOBALS['sess'])]) {
            $register_template = $GLOBALS['template_factory']->open('nocookies');
        } else {
            $register_template = $GLOBALS['template_factory']->open('register/form');
            $register_template->set_attribute('validator', new email_validation_class());
            $register_template->set_attribute('error_msg', $this->error_msg);
            $register_template->set_attribute('username', Request::get('username'));
            $register_template->set_attribute('Vorname', Request::get('Vorname'));
            $register_template->set_attribute('Nachname', Request::get('Nachname'));
            $register_template->set_attribute('Email', Request::get('Email'));
            $register_template->set_attribute('title_front', Request::get('title_front'));
            $register_template->set_attribute('title_rear', Request::get('title_rear'));
            $register_template->set_attribute('geschlecht', Request::int('geschlecht', 0));
        }
        PageLayout::setHelpKeyword('Basis.AnmeldungRegistrierung');
        PageLayout::setTitle(_('Registrierung'));
        // $header_template = $GLOBALS['template_factory']->open('header');
        // $header_template->current_page = _('Registrierung');

        $layout = $GLOBALS['template_factory']->open('layouts/base.php');
        $register_template->set_layout($layout);

        // include 'lib/include/html_head.inc.php';
        // echo $header_template->render();
        echo $register_template->render();
        // include 'lib/include/html_end.inc.php';
    }

    /**
     * @return bool|string
     */
    public function auth_doregister()
    {
        $this->check_environment();

        $this->error_msg = "";

        $this->auth["uname"] = Request::username('username'); // This provides access for "crcregister.ihtml"

        $validator = new email_validation_class; // Klasse zum Ueberpruefen der Eingaben
        $validator->timeout = 10; // Wie lange warten wir auf eine Antwort des Mailservers?

        if (!Seminar_Session::check_ticket(Request::option('login_ticket'))) {
            return false;
        }

        $username = trim(Request::get('username'));
        $Vorname = trim(Request::get('Vorname'));
        $Nachname = trim(Request::get('Nachname'));

        // accept only registered domains if set
        $cfg = Config::GetInstance();
        $email_restriction = $cfg->getValue('EMAIL_DOMAIN_RESTRICTION');
        if ($email_restriction) {
            $Email = trim(Request::get('Email')) . '@' . trim(Request::get('emaildomain'));
        } else {
            $Email = trim(Request::get('Email'));
        }

        if (!$validator->ValidateUsername($username)) {
            $this->error_msg = $this->error_msg . _("Der gewählte Benutzername ist zu kurz!") . "<br>";
            return false;
        } // username syntaktisch falsch oder zu kurz
        // auf doppelte Vergabe wird weiter unten getestet.

        if (!$validator->ValidatePassword(Request::quoted('password'))) {
            $this->error_msg = $this->error_msg . _("Das Passwort ist zu kurz!") . "<br>";
            return false;
        }

        if (!$validator->ValidateName($Vorname)) {
            $this->error_msg = $this->error_msg . _("Der Vorname fehlt oder ist unsinnig!") . "<br>";
            return false;
        } // Vorname nicht korrekt oder fehlend
        if (!$validator->ValidateName($Nachname)) {
            $this->error_msg = $this->error_msg . _("Der Nachname fehlt oder ist unsinnig!") . "<br>";
            return false; // Nachname nicht korrekt oder fehlend
        }
        if (!$validator->ValidateEmailAddress($Email)) {
            $this->error_msg = $this->error_msg . _("Die E-Mail-Adresse fehlt oder ist falsch geschrieben!") . "<br>";
            return false;
        } // E-Mail syntaktisch nicht korrekt oder fehlend

        $REMOTE_ADDR = $_SERVER["REMOTE_ADDR"];
        $Zeit = date("H:i:s, d.m.Y", time());

        if (!$validator->ValidateEmailHost($Email)) { // Mailserver nicht erreichbar, ablehnen
            $this->error_msg = $this->error_msg . _("Der Mailserver ist nicht erreichbar, bitte überprüfen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken und empfangen können!") . "<br>";
            return false;
        } else { // Server ereichbar
            if (!$validator->ValidateEmailBox($Email)) { // aber user unbekannt. Mail an abuse!
                StudipMail::sendAbuseMessage("Register", "Emailbox unbekannt\n\nUser: $username\nEmail: $Email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
                $this->error_msg = $this->error_msg . _("Die angegebene E-Mail-Adresse ist nicht erreichbar, bitte überprüfen Sie Ihre Angaben!") . "<br>";
                return false;
            } else {
                ; // Alles paletti, jetzt kommen die Checks gegen die Datenbank...
            }
        }

        $check_uname = StudipAuthAbstract::CheckUsername($username);

        if ($check_uname['found']) {
            //   error_log("username schon vorhanden", 0);
            $this->error_msg = $this->error_msg . _("Der gewählte Benutzername ist bereits vorhanden!") . "<br>";
            return false; // username schon vorhanden
        }

        if (count(User::findBySQL("Email LIKE " . DbManager::get()->quote($Email)))) {
            $this->error_msg = $this->error_msg . _("Die angegebene E-Mail-Adresse wird bereits von einem anderen Benutzer verwendet. Sie müssen eine andere E-Mail-Adresse angeben!") . "<br>";
            return false; // Email schon vorhanden
        }

        // alle Checks ok, Benutzer registrieren...
        $hasher = UserManagement::getPwdHasher();
        $new_user = new User();
        $new_user->username = $username;
        $new_user->perms = 'user';
        $new_user->password = $hasher->HashPassword(Request::get('password'));
        $new_user->vorname = $Vorname;
        $new_user->nachname = $Nachname;
        $new_user->email = $Email;
        $new_user->geschlecht = Request::int('geschlecht');
        $new_user->title_front = trim(Request::get('title_front', Request::get('title_front_chooser')));
        $new_user->title_rear = trim(Request::get('title_rear', Request::get('title_rear_chooser')));
        $new_user->auth_plugin = 'standard';
        $new_user->store();
        if ($new_user->user_id) {
            self::sendValidationMail($new_user);
            $this->auth["perm"] = $new_user->perms;
            $this->auth["uname"] = $new_user->username;
            $this->auth["auth_plugin"] = $new_user->auth_plugin;
            return $new_user->user_id;
        }
    }

    public static function get_validation_hash($user_id)
    {
        return md5("$user_id:" . self::$magic);
    }


    /**
     * Send a validation mail to the passed user
     *
     * @param User $user a user-object or id of the user
     *                   to resend the validation mail for
     *
     * @return void
     */
    public static function sendValidationMail($user){
        // if no user-object is given interpret it as a user-id
        if (is_string($user)) {
            $user = new User($user);
        }

        // template-variables for the include partial
        $Zeit     = date("H:i:s, d.m.Y", $user->mkdate);
        $username = $user->username;
        $Vorname  = $user->vorname;
        $Nachname = $user->nachname;
        $Email    = $user->email;

        // (re-)send the confirmation mail
        $to     = $user->email;
        $secret = md5($user->user_id .':'. self::$magic);
        $url    = $GLOBALS['ABSOLUTE_URI_STUDIP'] . "email_validation.php?secret=" . $secret;
        $mail   = new StudipMail();
        $abuse  = $mail->getReplyToEmail();

        $lang_path = getUserLanguagePath($user->id);

        // include language-specific subject and mailbody
        include_once("locale/$lang_path/LC_MAILS/register_mail.inc.php");

        // send the mail
        $mail->setSubject($subject)
            ->addRecipient($to)
            ->setBodyText($mailbody)
            ->send();
    }
}
