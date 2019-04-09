<?php
/**
 * ForumBulkMail.php - Experimental mailer to handle large amounts of mails at high speed
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

require_once 'lib/messaging.inc.php';

class ForumBulkMail extends Messaging {
    var $bulk_mail;

    /**
     * Overwrites the parent method. This method combines messages with the same
     * content and prepares them for sending them as a mail with multiple 
     * recepients instead of one mail for each recipient.
     * The actual sending task is done bulkSend().
     * 
     * @global object $user
     * 
     * @param string $rec_user_id  user_id of recipient
     * @param string $snd_user_id  user_id of sender
     * @param string $message      the message
     * @param string $subject      subject for the message
     * @param string $message_id   the message_id in the database
     */
    function sendingEmail($rec_user_id, $snd_user_id, $message, $subject, $message_id)
    {
        $receiver = User::find($rec_user_id);
        
        if ($receiver && $receiver->email) {
            $rec_fullname = 'Sie';

            setTempLanguage($receiver->id);

            if (empty($this->bulk_mail[md5($message)][getenv('LANG')])) {

                $title = "[Stud.IP - " . Config::get()->UNI_NAME_CLEAN . "] ".stripslashes(kill_format(str_replace(["\r","\n"], '', $subject)));

                if ($snd_user_id != "____%system%____") {
                    $sender = User::find($snd_user_id);
                    $reply_to = $sender->email;
                }

                $template = $GLOBALS['template_factory']->open('mail/text');
                $template->message      = kill_format(stripslashes($message));
                $template->rec_fullname = $reciver->getFullname();
                $mailmessage = $template->render();

                $template = $GLOBALS['template_factory']->open('mail/html');
                $template->lang         = getUserLanguagePath($rec_user_id);
                $template->message      = stripslashes($message);
                $template->rec_fullname = $receiver->getFullname();
                $mailhtml = $template->render();

                $this->bulk_mail[md5($message)][getenv('LANG')] = [
                    'text'       => $mailmessage,
                    'html'       => $mailhtml,
                    'title'      => $title,
                    'reply_to'   => $reply_to,
                    'message_id' => $message_id,
                    'users'      => []
                ];
            }

            $this->bulk_mail[md5($message)][getenv('LANG')]['users'][$receiver->id] = $receiver->email;

            restoreLanguage();
        }
    }
    

    /**
     * Sends the collected messages from sendingMail as e-mail.
     */
    function bulkSend()
    {
        // if nothing to do, return
        if (empty($this->bulk_mail)) return;

        // send a mail, for each language one
        foreach ($this->bulk_mail as $lang_data) {
            foreach ($lang_data as $data) {
                $mail = new StudipMail();
                $mail->setSubject($data['title']);

                foreach ($data['users'] as $user_id => $to) {
                    $mail->addRecipient($to, get_fullname($user_id), 'Bcc');
                }
                
                $mail->setReplyToEmail('')
                ->setBodyText($data['text']);

                if (mb_strlen($data['reply_to'])) {
                    $mail->setSenderEmail($data['reply_to'])
                         ->setSenderName($snd_fullname);
                }
                
                $user_cfg = UserConfig::get($user_id);
                if ($user_cfg->getValue('MAIL_AS_HTML')) {
                    $mail->setBodyHtml($mailhtml);
                }

                if($GLOBALS["ENABLE_EMAIL_ATTACHMENTS"]){
                    $message = Message::find($data['message_id']);
                    
                    $current_user = User::findCurrent();
                    
                    $message_folder = MessageFolder::findMessageTopFolder(
                        $message->id,
                        $current_user->id
                    );
                    
                    $message_folder = $message_folder->getTypedFolder();
                    
                    $attachments = FileManager::getFolderFilesRecursive(
                        $message_folder,
                        $current_user->id
                    );
                    
                    
                    foreach($attachments as $attachment) {
                        $mail->addStudipAttachment($attachment);
                    }
                }
                $mail->send();
            }
        }
    }
}
