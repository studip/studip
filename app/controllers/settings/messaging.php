<?php
/*
 * SettingsController - Controller for all setting related pages (formerly edit_about)
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

class Settings_MessagingController extends Settings_SettingsController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.MyStudIPMessaging');
        PageLayout::setTitle(_('Einstellungen des Nachrichtensystems anpassen'));
        Navigation::activateItem('/profile/settings/messaging');
        SkipLinks::addIndex(_('Einstellungen des Nachrichtensystems anpassen'), 'layout_content', 100);

        $this->settings = $this->config->MESSAGING_SETTINGS;
    }

    public function index_action()
    {
        if (Request::submitted('store')) {
            $this->check_ticket();

            if (Request::get('new_smsforward_rec')) {
                $this->user->smsforward_rec  = get_userid(Request::get('new_smsforward_rec'));
                $this->user->smsforward_copy = 1;
            } else if (Request::int('smsforward_copy') && !$this->user->smsforward_copy) {
                $this->user->smsforward_copy = 1;
            } else if (!Request::int('smsforward_copy') && $this->user->smsforward_copy) {
                $this->user->smsforward_copy = 0;
            }

            $this->user->email_forward = Request::int('send_as_email');
            $this->user->store();

            // write to user config table
            $this->config->store('ONLINE_NAME_FORMAT', Request::option('online_format'));
            $this->config->store('MAIL_AS_HTML', Request::int('mail_format'));

            $settings = $this->settings;

            $settings['sms_sig']              = Request::get('sms_sig');
            $settings['logout_markreaded']    = Request::int('logout_markreaded');
            $settings['save_snd']             = Request::int('save_snd', 2);
            $settings['request_mail_forward'] = Request::int('request_mail_forward', 0);

            $this->config->store('MESSAGING_SETTINGS', $settings);

            PageLayout::postSuccess(_('Ihre Einstellungen wurden erfolgreich gespeichert.'));
            $this->redirect('settings/messaging');
        }

        if (!$this->user->smsforward_rec && Request::submitted('gosearch')) {
            $vis_query = get_vis_query('auth_user_md5');
            $query = "SELECT user_id, username, {$GLOBALS['_fullname_sql']['full_rev']} AS fullname, perms
                      FROM auth_user_md5
                      LEFT JOIN user_info USING (user_id)
                      WHERE (username LIKE CONCAT('%', :needle, '%') OR
                             Vorname LIKE CONCAT('%', :needle, '%') OR
                             Nachname LIKE CONCAT('%', :needle, '%'))
                        AND user_id != :user_id AND {$vis_query}
                      ORDER BY Nachname ASC";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':needle', Request::get('search_exp'));
            $statement->bindValue(':user_id', $this->user->user_id);
            $statement->execute();
            $matches = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $matches = false;
        }

        $this->matches = $matches;
    }

    public function verify_action($action)
    {
        if ($action === 'reset') {
            $question = _('Durch das Zurücksetzen werden die persönliche Messaging-Einstellungen '
                         .'auf die Startwerte zurückgesetzt und die persönlichen Nachrichten-Ordner '
                         .'gelöscht. ' . "\n\n" . 'Nachrichten werden nicht entfernt.');
            PageLayout::postQuestion(
                $question,
                $this->url_for('settings/messaging/reset/reset/1')
            )->includeTicket();
        } elseif ($action === 'forward_receiver') {
            PageLayout::postQuestion(
                _('Wollen Sie wirklich die eingestellte Weiterleitung entfernen?'),
                $this->url_for('settings/messaging/reset/forward_receiver/1')
            )->includeTicket();
        }

        $this->redirect('settings/messaging');
    }

    public function reset_action($action = 'reset', $verified = false)
    {
        if ($verified) {
            $this->check_ticket();

            if ($action === 'reset') {
                $this->user->smsforward_rec  = '';
                $this->user->smsforward_copy = 0;
                $this->user->store();

                $query = "UPDATE message_user SET folder = 0 WHERE user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array($this->user->user_id));

                $this->config->delete('MESSAGING_SETTINGS');

                PageLayout::postSuccess(_('Ihre Einstellungen wurden erfolgreich zurückgesetzt.'));
            } else if ($action === 'forward_receiver') {
                $this->user->smsforward_rec  = '';
                $this->user->smsforward_copy = 0;
                $this->user->store();

                PageLayout::postSuccess(_('Empfänger und Weiterleitung wurden erfolgreich gelöscht'));
            }
        }
        $this->redirect('settings/messaging');
    }
}
