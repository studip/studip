<?php

/**
 * [NewPassword description]
 */

class NewPasswordController extends StudipController
{
    protected $with_session = true;  //we do need to have a session for this controller
    protected $allow_nobody = true;  //nobody is allowed

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!(Config::get()->ENABLE_REQUEST_NEW_PASSWORD_BY_USER
            && in_array('Standard', $GLOBALS['STUDIP_AUTH_PLUGIN'])
        )) {
            PageLayout::postError(_("Das Anfordern eines neuen Passwortes durch den Benutzer ist in dieser Stud.IP-Installation nicht möglich."));
            $this->redirect('start');
            return;
        }

        if ($GLOBALS['auth'] && $GLOBALS['auth']->auth["uid"] != "nobody") {
            PageLayout::postError(_("Sie können kein neues Passwort anfordern, wenn Sie bereits eingeloggt sind."));
            $this->redirect('start');
            return;
        }

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        PageLayout::setTitle('Passwort zurücksetzen');
        PageLayout::setHelpKeyword('Basis.AnmeldungPasswortAnfrage');
    }

    public function index_action()
    {

    }

    public function send_mail_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $users = User::findByEmail(Request::get('mail'));

        if (sizeof($users) == 1) {
            $user = $users[0];
        } else if (sizeof($users) > 1) {
            setTempLanguage($users[0]->id);

            // there are mutliple accounts with this mail addresses!
            $subject = sprintf(
                _("[Stud.IP - %s] Passwortänderung angefordert"),
                Config::get()->UNI_NAME_CLEAN
            );

            $mailbody = sprintf(
                _("Dies ist eine Informationsmail des Stud.IP-Systems\n"
                    ."(Studienbegleitender Internetsupport von Präsenzlehre)\n- %s -\n\n"
                    . "Für die Mail-Adresse %s wurde ein Link angefordert\n"
                    . "um das Passwort zurückzusetzen.\n"
                    . "Dieser Mail-Adresse sind jedoch mehrere Zugänge zugeordnet,\n"
                    . "deshalb ist es nicht möglich, das Passwort hierüber zurückzusetzen.\n"
                    . "Wenden sie sich bitte stattdessen an\n%s"
                ),
                Config::get()->UNI_NAME_CLEAN,
                $users[0]->email,
                $GLOBALS['UNI_CONTACT']
            );

            StudipMail::sendMessage($users[0]->email, $subject, $mailbody);

            restoreLanguage();
        }

        if ($user) {
            // spam/abuse-protection
            // if there are more than 5 tokens present, do NOT send another mail

            $present_tokens = sizeof(Token::findByUser_id($user->user_id));

            if ($present_tokens < 5) {
                UserManagement::sendPasswordMail($user);
            }
        }

        PageLayout::postSuccess(_('Sofern die von ihnen eingegebene Mail-Adresse korrekt ist, erhalten sie in Kürze eine Mail '
            . 'mit Anweisungen, wie sie ihr Passwort neu setzen können. Sehen sie auch in ihrem Spam-Ordern nach.'));
    }

    public function set_action($token_id)
    {

        $token = Token::findOneById($token_id);

        if ($token && !$token->isExpired()) {
            $user_id = $token->user_id;
        } else {
            PageLayout::postError('Der Link ist abgelaufen oder wurde bereits verwendet. Fordern sie eine neue Mail an!');
            $this->redirect('start');
            return;
        }

        $this->token_id = $token_id;
        $requesting_user = User::find($user_id);

        if ($requesting_user && Request::get('new_password')) {
            CSRFProtection::verifyUnsafeRequest();

            $validator = new email_validation_class();
            $user_management = new UserManagement($requesting_user['user_id']);

            $password = Request::get('new_password');
            $confirm  = Request::get('new_password_confirm');

            if (!$validator->ValidatePassword($password)) {
                $errors[] = _('Das Passwort ist zu kurz. Es sollte mindestens 8 Zeichen lang sein.');
            } else if ($password !== $confirm) {
                $errors[] = _('Die Wiederholung Ihres Passworts stimmt nicht mit Ihrer Eingabe überein.');
            } else if ($password == $requesting_user->username) {
                $errors[] = _('Das Passwort darf nicht mit dem Nutzernamen übereinstimmen.');
            } else if (str_replace(['.', ' '], '', mb_strtolower($password)) == 'studip') {
                $errors[] = _('Aus Sicherheitsgründen darf das Passwort nicht "Stud.IP" oder eine Abwandlung davon sein.');
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    PageLayout::postError($error);
                }
            } else {
                // TODO: remove token, it's now used
                $pw_changed = $user_management->changePassword($password);
                if ($pw_changed) {
                    PageLayout::postSuccess(_("Das Passwort wurde erfolgreich geändert. Sie können sich nun mit dem neuen Passwort einloggen."));
                    StudipLog::USER_NEWPWD($requesting_user['user_id'], null, 'Passwort neu gesetzt', null, $requesting_user['user_id']);

                    // delete used token and for security reasons all other tokens for this user as well
                    foreach (Token::findByUser_id($user_id) as $t) {
                        $t->delete();
                    }

                    $this->redirect('start');
                } else {
                    PageLayout::postError(_('Das Passwort konnte nicht gesetzt werden. Bitte wiederholen Sie den Vorgang.'));
                }
            }
        }
    }
}
