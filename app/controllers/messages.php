<?php
/**
 * message.php - Message controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author Stud.IP developers
 * @author Moritz Strohm <strohm@data-quest.de> (only code related to the new file area)
 */

require_once 'lib/statusgruppe.inc.php';

class MessagesController extends AuthenticatedController {

    protected $number_of_displayed_messages = 50;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Nachrichten'));
        PageLayout::setHelpKeyword('Basis.InteraktionNachrichten');

        if (in_array($action, ['overview', 'sent'])) {
            $this->tags = Message::getUserTags();
        }

        $this->setupSidebar($action);
    }

    public function overview_action($message_id = null)
    {
        Navigation::activateItem('/messaging/messages/inbox');

        if (Request::get("read_all")) {
            Message::markAllAs($GLOBALS['user']->id, 1);
            PageLayout::postSuccess(_("Alle Nachrichten wurden als gelesen markiert."));
            $this->redirect('messages/overview');
            return;
        }

        if (Request::isPost()) {
            foreach (Request::getArray("bulk") as $message_id) {
                $this->delete_message($message_id);
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%u Nachrichten wurden gelöscht"), count(Request::getArray("bulk")))));
        }

        $this->messages = $this->get_messages(
            true,
            Request::int("limit", $this->number_of_displayed_messages),
            Request::int("offset", 0),
            Request::get("tag"),
            Request::get("search")
        );
        $this->received   = true;
        $this->message_id = $message_id;
        $this->settings   = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;
    }

    public function sent_action($message_id = null)
    {
        Navigation::activateItem('/messaging/messages/sent');

        if (Request::isPost()) {
            foreach (Request::getArray("bulk") as $message_id) {
                $this->delete_message($message_id);
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%u Nachrichten wurden gelöscht"), count(Request::getArray("bulk")))));
        }

        $this->messages = $this->get_messages(
            false,
            Request::int("limit", $this->number_of_displayed_messages),
            Request::int("offset", 0),
            Request::get("tag"),
            Request::get("search")
        );
        $this->received   = false;
        $this->message_id = $message_id;
        $this->settings   = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;

        $this->render_action("overview");
    }

    public function more_action()
    {
        $messages = $this->get_messages(
            Request::int("received") ? true : false,
            Request::int("limit", $this->number_of_displayed_messages) + 1,
            Request::int("offset", 0),
            Request::get("tag"),
            Request::get("search")
        );
        $this->output = array('messages' => array(), "more" => 0);
        if (count($messages) > Request::int("limit")) {
            $this->output["more"] = 1;
            array_pop($messages);
        }
        $this->settings   = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;
        $template_factory = $this->get_template_factory();
        foreach ($messages as $message) {
            $this->output['messages'][] = $template_factory
                                            ->open("messages/_message_row.php")
                                            ->render(array('message'    => $message,
                                                           'controller' => $this,
                                                           'received'   => (bool) Request::int("received"),
                                                           'settings'   => $this->settings
                                                    ));
        }

        $this->render_json($this->output);
    }

    public function read_action($message_id)
    {
        $this->message = new Message($message_id);
        if (!$this->message->permissionToRead()) {
            throw new AccessDeniedException();
        }

        //load the message's top folder (if any):
        $attachment_folder = Folder::findOneByRange_id($this->message->id);
        if ($attachment_folder) {
            $this->attachment_folder = $attachment_folder->getTypedFolder();
        }


        PageLayout::setTitle(_('Betreff') . ': ' . $this->message['subject']);

        if ($this->message['autor_id'] === $GLOBALS['user']->id) {
            Navigation::activateItem('/messaging/messages/sent');
        } else {
            Navigation::activateItem('/messaging/messages/inbox');
        }
        if (Request::isXhr()) {
            $this->response->add_header('X-Tags', json_encode($this->message->getTags()));
            $this->response->add_header('X-All-Tags', json_encode(Message::getUserTags()));
        } else {
            // Try to redirect to overview of recevied/sent messages if
            // controller is not called via ajax to ensure message is loaded
            // in dialog.
            $target = $this->message->autor_id === $GLOBALS['user']->id
                    ? $this->url_for('messages/sent/' . $message_id)
                    : $this->url_for('messages/overview/' . $message_id);

            $script = sprintf('if (STUDIP.Dialog.shouldOpen()) { location.href = "%s"; }', $target);
            PageLayout::addHeadElement('script', [], sprintf(
                'jQuery(function () { %s });',
                $script
            ));
        }
        $this->message->markAsRead($GLOBALS['user']->id);
    }

    /**
     * Lets the user compose a message and send it.
     */
    public function write_action()
    {
        if ($GLOBALS['user']->perms === 'user' && !Request::option('answer_to')) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(_('Neue Nachricht schreiben'));

        //the message-ID for the new message:
        $this->message_id = Request::option('message_id') ?: md5(uniqid('neWMesSagE'));


        $this->to = [];
        $this->default_message = new Message();
        $this->default_message->setId($this->default_message->getNewId());

        //flag to determine if the message is forwarded or not:
        $forward_message = false;
        $quoted_message = false;


        //check if a receiver is given:
        if (Request::username('rec_uname')) {
            $user = new MessageUser();
            $user->setData(['user_id' => get_userid(Request::username('rec_uname')), 'snd_rec' => 'rec']);
            $this->default_message->receivers[] = $user;
        }

        //check if a list of receivers is given:
        if (Request::getArray('rec_uname')) {
            foreach (Request::usernameArray('rec_uname') as $username) {
                $user = new MessageUser();
                $user->setData(['user_id' => get_userid($username), 'snd_rec' => 'rec']);
                $this->default_message->receivers[] = $user;
            }
        }

        //check if the message shall be sent to all members of a status group:
        if (Request::option('group_id')) {
            $this->default_message->receivers = [];
            $group  = Statusgruppen::find(Request::option('group_id'));

            // Exclude hidden course members from mails if not at least tutor
            $hidden = [];
            $course = Course::find($group->range_id);
            if ($course && !$GLOBALS['perm']->have_studip_perm('tutor', $course->id)) {
                $hidden = $course->members->findBy('visible', 'no')->pluck('user_id');
            }

            if ($group['range_id'] === $GLOBALS['user']->id
                || $GLOBALS['perm']->have_studip_perm('autor', $group['range_id']))
            {
                foreach ($group->members as $member) {
                    if (in_array($member->user_id, $hidden)) {
                        continue;
                    }

                    $user = new MessageUser();
                    $user->setData(['user_id' => $member['user_id'], 'snd_rec' => 'rec']);
                    $this->default_message->receivers[] = $user;
                }
            }
        }

        //check if the message shall be sent to all members of an institute:
        if(Request::get('inst_id') && $GLOBALS['perm']->have_perm('admin')) {
            $query = "SELECT user_id FROM user_inst WHERE Institut_id = ? AND inst_perms != 'user'";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, array(Request::option('inst_id')), 'MessageUser::build');
        }

        //check if the message shall be sent to all (or some) members of a course:
        if (Request::get('filter') && Request::option("course_id")) {
            $course = new Course(Request::option('course_id'));
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course->id) || $course->getSemClass()['studygroup_mode'] || CourseConfig::get($course->id)->COURSE_STUDENT_MAILING) {
                $this->default_message->receivers = [];
                if (Request::get('filter') === 'claiming') {
                    $cs = CourseSet::getSetForCourse($course->id);
                    if (is_object($cs) && !$cs->hasAlgorithmRun()) {
                        foreach (AdmissionPriority::getPrioritiesByCourse($cs->getId(), $course->id) as $user_id => $p) {
                            $this->default_message->receivers[] = MessageUser::build(['user_id' => $user_id, 'snd_rec' => 'rec']);
                        }
                    }
                } else {
                    // Exclude hidden course members from mail if not at least tutor
                    $additional = '';
                    if (!$GLOBALS['perm']->have_studip_perm('tutor', $course->id)) {
                        $additonal = " AND seminar_user.visible != 'no'";
                    }

                    $params = [$course->id, Request::option('who')];
                    switch (Request::get('filter')) {
                        case 'send_sms_to_all':
                            $query = "SELECT user_id, 'rec' AS snd_rec
                                      FROM seminar_user
                                      JOIN auth_user_md5 USING (user_id)
                                      WHERE Seminar_id = ? AND status = ? {$additonal}
                                      ORDER BY Nachname, Vorname";
                            break;
                        case 'all':
                            $query = "SELECT user_id, 'rec' AS snd_rec
                                      FROM seminar_user
                                      JOIN auth_user_md5 USING (user_id)
                                      WHERE Seminar_id = ? {$additonal}
                                      ORDER BY Nachname, Vorname";
                            break;
                        case 'prelim':
                            $query = "SELECT user_id, 'rec' AS snd_rec
                                      FROM admission_seminar_user
                                      JOIN auth_user_md5 USING (user_id)
                                      WHERE seminar_id = ? AND status = 'accepted'
                                        {$additonal}
                                      ORDER BY Nachname, Vorname";
                            break;
                        case 'awaiting':
                            $query = "SELECT user_id, 'rec' AS snd_rec
                                      FROM admission_seminar_user
                                      JOIN auth_user_md5 USING (user_id)
                                      WHERE seminar_id = ? AND status = 'awaiting'
                                        {$additonal}
                                      ORDER BY Nachname, Vorname";
                            break;
                        case 'inst_status':
                            $query = "SELECT user_id, 'rec' AS snd_rec
                                      FROM user_inst
                                      JOIN auth_user_md5 USING (user_id)
                                      WHERE Institut_id = ? AND inst_perms = ?
                                        {$additonal}
                                      ORDER BY Nachname, Vorname";
                            break;
                        case 'not_grouped':
                            $query = "SELECT seminar_user.user_id, 'rec' as snd_rec
                                      FROM seminar_user
                                      JOIN auth_user_md5 USING (user_id)
                                      LEFT JOIN statusgruppen ON range_id = seminar_id
                                      LEFT JOIN statusgruppe_user ON statusgruppen.statusgruppe_id = statusgruppe_user.statusgruppe_id
                                        AND seminar_user.user_id = statusgruppe_user.user_id
                                      WHERE seminar_id = ?
                                      GROUP BY seminar_user.user_id
                                      HAVING COUNT(statusgruppe_user.statusgruppe_id) = 0
                                      ORDER BY Nachname, Vorname";
                            break;
                    }
                    $this->default_message->receivers = DBManager::get()->fetchAll($query, $params, 'MessageUser::build');
                }
            }

        }

        if (Request::option('prof_id') && Request::option('deg_id') && $GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT DISTINCT user_id,'rec' as snd_rec
            FROM user_studiengang
            WHERE fach_id = ? AND abschluss_id = ?";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, [
                Request::option('prof_id'),
                Request::option('deg_id')
            ], 'MessageUser::build');
        }

        if (Request::option('sd_id') && $GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT DISTINCT user_id, 'rec' AS snd_rec
                      FROM user_studiengang
                      WHERE abschluss_id = ?";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, [
                Request::option('sd_id')
            ], 'MessageUser::build');
        }

        if (Request::option('sp_id') && $GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT DISTINCT user_id,'rec' as snd_rec
            FROM user_studiengang
            WHERE fach_id = ?";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, [
                Request::option('sp_id')
            ], 'MessageUser::build');
        }

        if (!$this->default_message->receivers->count() && is_array($_SESSION['sms_data']['p_rec'])) {
            $this->default_message->receivers = DBManager::get()->fetchAll("SELECT user_id,'rec' as snd_rec FROM auth_user_md5 WHERE username IN(?) ORDER BY Nachname,Vorname", array($_SESSION['sms_data']['p_rec']), 'MessageUser::build');
            unset($_SESSION['sms_data']);
        }

        //check if the message is a reply or if it shall be forwarded:
        if (Request::option("answer_to")) {
            $this->default_message->receivers = array();
            $old_message = new Message(Request::option("answer_to"));
            if (!$old_message->permissionToRead()) {
                throw new AccessDeniedException("Message is not for you.");
            }
            if (!Request::get('forward')) {
                //message is a reply message
                if (Request::option("quote") === $old_message->getId()) {
                    $quoted_message = true;
                    $message = _("-_-_ Ursprüngliche Nachricht _-_-");
                    $message .= "\n" . _("Betreff") . ": " . $old_message['subject'];
                    $message .= "\n" . _("Datum") . ": " . strftime('%x %X', $old_message['mkdate']);
                    $message .= "\n" . _("Von") . ": " . get_fullname($old_message['autor_id']);
                    $num_recipients = $old_message->getNumRecipients();
                    if ($GLOBALS['user']->id == $old_message->autor_id) {
                        $message .= "\n" . ($num_recipients == 1 ? _('An: Eine Person') : sprintf(_('An: %d Personen'), $num_recipients));
                    } else {
                        $message .= "\n";
                        if($num_recipients > 1) {
                            $message .= sprintf(
                                ngettext(
                                    'An: %1$s (und %2$d weitere/n)',
                                    'An: %1$s (und %2$d weitere)',
                                    $num_recipients
                                ),
                                $GLOBALS['user']->getFullName(),
                                $num_recipients
                            );
                        } else {
                            $message .= sprintf(
                                _('An: %s'),
                                $GLOBALS['user']->getFullName()
                            );
                        }
                    }
                    $message .= "\n\n";
                    if (Studip\Markup::editorEnabled()) {
                        $message = Studip\Markup::markupToHtml($message, false) . Studip\Markup::markupToHtml($old_message['message']);
                    } else if (Studip\Markup::isHtml($old_message['message'])) {
                        $message .= Studip\Markup::removeHtml($old_message['message']);
                    } else {
                        $message .= $old_message['message'];
                    }
                    $this->default_message['message'] = $message;
                }
                $this->default_message['subject'] = mb_substr($old_message['subject'], 0, 4) === "RE: " ? $old_message['subject'] : "RE: ".$old_message['subject'];
                if ($old_message['autor_id'] !== $GLOBALS['user']->id) {
                    $user = new MessageUser();
                    $user->setData(array('user_id' => $old_message['autor_id'], 'snd_rec' => "rec"));
                    $this->default_message->receivers[] = $user;
                } else {
                    foreach ($old_message->receivers as $old_receivers) {
                        $user = new MessageUser();
                        $user->setData(array('user_id' => $old_receivers['user_id'], 'snd_rec' => "rec"));
                        $this->default_message->receivers[] = $user;
                    }
                }
                $this->answer_to = $old_message->id;
            } else {
                //message shall be forwarded
                $forward_message = true;

                $messagesubject = 'FWD: ' . $old_message['subject'];
                $message = _("-_-_ Weitergeleitete Nachricht _-_-");
                $message .= "\n" . _("Betreff") . ": " . $old_message['subject'];
                $message .= "\n" . _("Datum") . ": " . strftime('%x %X', $old_message['mkdate']);
                $message .= "\n" . _("Von") . ": " . get_fullname($old_message['autor_id']);
                $num_recipients = $old_message->getNumRecipients();
                if ($GLOBALS['user']->id == $old_message->autor_id) {
                    $message .= "\n" . ($num_recipients == 1 ? _('An: Eine Person') : sprintf(_('An: %d Personen'), $num_recipients));
                } else {
                    $message .= "\n";
                    if($num_recipients > 1) {
                        $message .= sprintf(
                            ngettext(
                                'An: %1$s (und %2$d weitere/n)',
                                'An: %1$s (und %2$d weitere)',
                                $num_recipients
                            ),
                            $GLOBALS['user']->getFullName(),
                            $num_recipients
                        );
                    } else {
                        $message .= sprintf(
                            _('An: %s'),
                            $GLOBALS['user']->getFullName()
                        );
                    }
                }
                $message .= "\n\n";
                if (Studip\Markup::editorEnabled()) {
                    $message = Studip\Markup::markupToHtml($message, false) . Studip\Markup::markupToHtml($old_message['message']);
                } else if (Studip\Markup::isHtml($old_message['message'])) {
                    $message .= Studip\Markup::removeHtml($old_message['message']);
                } else {
                    $message .= $old_message['message'];
                }
                if ($old_message->getNumAttachments()) {
                    //there is at least one attachment: we must copy it
                    $old_attachment_folder = MessageFolder::findTopFolder($old_message->id);

                    if ($old_attachment_folder) {
                        $new_attachment_folder = MessageFolder::createTopFolder($this->default_message->id);
                        if ($new_attachment_folder) {
                            foreach ($old_attachment_folder->getFiles() as $old_attachment) {
                                $new_attachment = new FileRef();
                                $new_attachment->file_id = $old_attachment->file_id;
                                $new_attachment->folder_id = $new_attachment_folder->getId();
                                $new_attachment->name = $old_attachment->file->name;
                                $new_attachment->description = $old_attachment->description;
                                $new_attachment->content_terms_of_use_id = $old_attachment->content_terms_of_use_id;
                                $new_attachment->user_id = $GLOBALS['user']->id;

                                if ($new_attachment->store()) {
                                    $this->default_attachments[] = [
                                        'icon'        => Icon::create(
                                            FileManager::getIconNameForMimeType(
                                                $new_attachment->file->mime_type
                                            ),
                                            'clickable'
                                        )->asImg(['class' => "text-bottom"]),
                                        'name'        => $new_attachment->name,
                                        'document_id' => $new_attachment->id,
                                        'size'        => relsize($new_attachment->file->size, false)
                                    ];
                                }
                            }
                        }
                    }
                }
                $this->default_message['subject'] = $messagesubject;
                $this->default_message['message'] = $message;
            }
        }
        if (Request::get('default_body')) {
            if (Studip\Markup::editorEnabled()) {
                $this->default_message['message'] = Studip\Markup::markupToHtml(Request::get("default_body"));
            } else {
                $this->default_message['message'] = Studip\Markup::removeHtml(Request::get("default_body"));
            }
        }
        if (Request::get('default_subject')) {
            $this->default_message['subject'] = Request::get("default_subject");
        }
        $settings = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;
        $this->mailforwarding = Request::get('emailrequest') ? true : $settings['request_mail_forward'];
        $this->show_adressees = true;
        if (Request::get('inst_id') || Request::get('course_id') || Request::option('group_id')) {
            $this->show_adressees = false;
        }
        if (trim($settings['sms_sig'])) {
            if (Studip\Markup::editorEnabled()) {
                $sms_sig = Studip\Markup::markAsHtml('<br><br><hr>' . Studip\Markup::markupToHtml($settings['sms_sig']) . '<br><br>');
            } else {
                $sms_sig =  "\n\n--\n" . $settings['sms_sig'] . "\n\n";
            }
            if ($forward_message || $quoted_message) {
                $this->default_message['message'] = $sms_sig . $this->default_message['message'];
            } else {
                $this->default_message['message'] .= $sms_sig;
            }
        }


        //Files that were uploaded earlier and were left unattached
        //are only attached to new messages which are not forwarded.
        //This is because forwarded messages will only have those attachments
        //that were present in the original message.
        if (!$forward_message) {

            //Check if there are files that were uploaded earlier and not attached
            //to a message. These files can be attached to the new message.

            //unattached folders are all folders that are from type 'MessageFolder',
            //belong to the range type 'message', are owned by the current user
            //and whose range-ID does not belong to a message.
            //Background: Attachment folders of messages that haven't been sent
            //have a "provisional" range-ID. When the message is sent this
            //"provisional" range-ID is replaced by the message-ID.
            $unattached_folders = Folder::findBySql(
                "folder_type = 'MessageFolder'
                AND
                range_type = 'message'
                AND
                user_id = :user_id
                AND
                range_id NOT IN (
                    SELECT message_id FROM message
                    WHERE autor_id = :user_id
                )",
                [
                    'user_id' => $GLOBALS['user']->id
                ]
            );

            $unattached_files = [];

            //loop through all unattached folders, retrieve all file_refs,
            //add them to the default_attachments array and store them in a
            //new folder that gets the "provisional" range-ID of this message.
            //After that, delete the old folders.
            foreach ($unattached_folders as $unattached_folder) {
                foreach ($unattached_folder->file_refs as $file_ref) {
                    $unattached_files[] = $file_ref;
                    $this->default_attachments[] = [
                        'icon'        => Icon::create(
                            FileManager::getIconNameForMimeType(
                                $file_ref->file->mime_type
                            ),
                            'clickable'
                        )->asImg(['class' => "text-bottom"]),
                        'name'        => $file_ref->name,
                        'document_id' => $file_ref->id,
                        'size'        => relsize($file_ref->file->size, false)
                    ];
                }

            }

            //we must display a note for the user to avoid sending a message
            //with the wrong attachements attached to it.
            if (count($unattached_files)) {
                PageLayout::postInfo(_('Es wurden Dateianhänge gefunden, welche zwar hochgeladen, aber noch nicht versandt wurden. Diese wurden an diese Nachricht angehängt!'));
                //create an attachment folder for the new message:
                $new_attachment_folder = MessageFolder::createTopFolder($this->default_message->id);

                //"bend" the folder-ID of each unattached file to the new attachment folder's ID:
                foreach ($unattached_files as $file) {
                    $file->folder_id = $new_attachment_folder->getId();
                    $file->store();
                }
            }

            //now we can delete the old unattached folders since we transferred
            //the attachments to a new folder:
            foreach ($unattached_folders as $unattached_folder) {
                $unattached_folder->delete();
            }
        }

        // Create search object for multi person search
        $vis_query = get_vis_query();
        $query = "SELECT DISTINCT
                    auth_user_md5.user_id,
                    {$GLOBALS['_fullname_sql']['full_rev']} AS fullname,
                    username,
                    perms
                  FROM auth_user_md5
                  LEFT JOIN user_info USING (user_id)
                  WHERE (
                      username LIKE :input
                      OR CONCAT(Vorname, ' ', Nachname) LIKE :input
                      OR CONCAT(Nachname, ' ', Vorname) LIKE :input
                      OR CONCAT(Nachname, ', ', Vorname) LIKE :input
                    )
                    AND {$vis_query}
                  ORDER BY Nachname ASC, Vorname ASC";
        $this->mp_search_object = new SQLSearch($query, _('Nutzer suchen'), 'user_id');

        NotificationCenter::postNotification('DefaultMessageForComposerCreated', $this->default_message);
    }

    /**
     * Sends a message and redirects the user.
     */
    public function send_action()
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        PageLayout::setTitle(_('Nachricht verschicken'));

        $recipients = array_filter(Request::getArray('message_to'));

        if (count($recipients) === 0) {
            PageLayout::postError(_('Sie haben nicht angegeben, wer die Nachricht empfangen soll!'));
        } elseif (Request::submitted('message_id') && Message::exists(Request::option('message_id'))) {
            PageLayout::postInfo(_('Diese Nachricht wurde bereits verschickt.'));
        } elseif (Request::submitted('message_body')) {
            $messaging = new messaging();
            $rec_uname = User::findAndMapMany(function ($user) {
                return $user->username;
            }, $recipients);
            $messaging->send_as_email = Request::int('message_mail');
            $messaging->insert_message(
                Studip\Markup::purifyHtml(Request::get('message_body')),
                $rec_uname,
                $GLOBALS['user']->id,
                '',
                Request::option('message_id'),
                '',
                null,
                Request::get('message_subject'),
                '',
                'normal',
                trim(Request::get('message_tags')) ?: null,
                Request::int('show_adressees', 0)
            );
            if (Request::option('answer_to')) {
                $old_message = Message::find(Request::option('answer_to'));
                if ($old_message) {
                    $old_message->markAsAnswered($GLOBALS['user']->id);
                }
            }
            PageLayout::postSuccess(_('Nachricht wurde verschickt.'));
        }

        if (!Request::isXhr()) {
            $this->redirect('messages/overview');
        }
    }

    public function tag_action($message_id)
    {
        if (Request::isPost()) {
            $message = Message::find($message_id);
            if (!$message->permissionToRead()) {
                throw new AccessDeniedException();
            }
            if (Request::get('add_tag')) {
                $message->addTag(Request::get('add_tag'));
            } elseif (Request::get('remove_tag')) {
                $message->removeTag(Request::get('remove_tag'));
            }
        }
        $this->redirect('messages/read/' . $message_id);
    }

    public function print_action($message_id)
    {
        $message = Message::find($message_id);
        if (!$message->permissionToRead()) {
            throw new AccessDeniedException();
        }
        if ($message && $message->permissionToRead($GLOBALS['user']->id)) {
            $this->msg = $message->toArray();
            $this->msg['from'] = $message['autor_id'] === '____%system%____'
                                ? _('Stud.IP')
                                : ($message->getSender()
                                    ? $message->getSender()->getFullname()
                                    : _('unbekannt'));
            $this->msg['to'] = $GLOBALS['user']->id == $message->autor_id ?
                join(', ', $message->getRecipients()->pluck('fullname')) :
                $GLOBALS['user']->getFullname() . ' ' . sprintf(_('(und %d weitere)'), $message->getNumRecipients()-1);

            if ($attachment_folder = Folder::findOneByRange_id($message->id)) {
                $this->msg['attachments'] = $attachment_folder->file_refs->toArray('name size');
            }

            PageLayout::setTitle($this->msg['subject']);
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        } else {
            $this->set_status(400);
            return $this->render_nothing();
        }
    }

    protected function delete_message($message_id)
    {
        $message = Message::find($message_id);
        if ($message) {
            $message->markAsRead($GLOBALS['user']->id);
        }

        $messageuser = new MessageUser(array($GLOBALS['user']->id, $message_id, "snd"));
        $success = 0;
        if (!$messageuser->isNew()) {
            $messageuser['deleted'] = 1;
            $success = $messageuser->store();
        }
        $messageuser = new MessageUser(array($GLOBALS['user']->id, $message_id, "rec"));
        if (!$messageuser->isNew()) {
            $messageuser['deleted'] = 1;
            $success += $messageuser->store();
        }
        return $success;
    }

    public function delete_action($message_id)
    {
        $message = Message::find($message_id);

        $ticket = Request::get('studip-ticket');
        if (Request::isPost() && $ticket && check_ticket($ticket)) {
            $success = $this->delete_message($message_id);
            if ($success) {
                PageLayout::postMessage(MessageBox::success(_('Nachricht gelöscht!')));
            } else {
                PageLayout::postMessage(MessageBox::error(_('Nachricht konnte nicht gelöscht werden.')));
            }
        }

        $redirect = $message->autor_id === $GLOBALS['user']->id
            ? $this->url_for('messages/sent')
            : $this->url_for('messages/overview');

        $this->redirect($redirect);
    }

    protected function get_messages($received = true, $limit = 50, $offset = 0, $tag = null, $search = null)
    {
        if ($tag) {
            $messages_data = DBManager::get()->prepare("
                SELECT message.*
                FROM message_user
                    INNER JOIN message ON (message_user.message_id = message.message_id)
                    INNER JOIN message_tags ON (message_tags.message_id = message_user.message_id AND message_tags.user_id = message_user.user_id)
                WHERE message_user.user_id = :me
                    AND message_user.deleted = 0
                    AND snd_rec = :sender_receiver
                    AND message_tags.tag = :tag
                ORDER BY message_user.mkdate DESC
                LIMIT ".(int) $offset .", ".(int) $limit ."
            ");
            $messages_data->execute(array(
                'me' => $GLOBALS['user']->id,
                'tag' => $tag,
                'sender_receiver' => $received ? "rec" : "snd"
            ));
        } elseif($search) {

            $suchmuster = '/".*"/U';
            preg_match_all($suchmuster, $search, $treffer);
            array_walk($treffer[0], function(&$value) { $value = trim($value, '"'); });

            // remove the quoted parts from $_searchfor
            $_searchfor = trim(preg_replace($suchmuster, '', $search));

            // split the searchstring $_searchfor at every space
            $parts = explode(' ', $_searchfor);
            foreach ($parts as $key => $val) {
                if ($val == '') {
                    unset($parts[$key]);
                }
            }
            if (!empty($parts)) {
                $_searchfor = array_merge($parts, $treffer[0]);
            } else  {
                $_searchfor = $treffer[0];
            }

            if (!Request::int('search_autor') && !Request::int('search_subject') && !Request::int('search_content')) {
                $message = _('Es wurden keine Bereiche angegeben, in denen gesucht werden soll.');
                PageLayout::postMessage(MessageBox::error($message));

                $search_sql = "AND 0";
            } else {
                $search_sql = "";
                foreach ($_searchfor as $val) {
                    $tmp_sql = array();
                    if (Request::get("search_autor")) {
                        $tmp_sql[] = "CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE ".DBManager::get()->quote("%".$val."%")." ";
                    }
                    if (Request::get("search_subject")) {
                        $tmp_sql[] = "message.subject LIKE ".DBManager::get()->quote("%".$val."%")." ";
                    }
                    if (Request::get("search_content")) {
                        $tmp_sql[] = "message.message LIKE ".DBManager::get()->quote("%".$val."%")." ";
                    }
                    $search_sql .= "AND (";
                    $search_sql .= implode(" OR ", $tmp_sql);
                    $search_sql .= ") ";
                }
            }

            $messages_data = DBManager::get()->prepare("
                SELECT message.*
                FROM message_user
                    INNER JOIN message ON (message_user.message_id = message.message_id)
                    LEFT JOIN auth_user_md5 ON (auth_user_md5.user_id = message.autor_id)
                WHERE message_user.user_id = :me
                    AND message_user.deleted = 0
                    AND snd_rec = :sender_receiver
                    $search_sql
                ORDER BY message_user.mkdate DESC
                LIMIT ".(int) $offset .", ".(int) $limit ."
            ");
            $messages_data->execute(array(
                'me' => $GLOBALS['user']->id,
                'sender_receiver' => $received ? "rec" : "snd"
            ));
        } else {
            $messages_data = DBManager::get()->prepare("
                SELECT message.*
                FROM message_user
                    INNER JOIN message ON (message_user.message_id = message.message_id)
                WHERE message_user.user_id = :me
                    AND message_user.deleted = 0
                    AND snd_rec = :sender_receiver
                ORDER BY message_user.mkdate DESC
                LIMIT ".(int) $offset .", ".(int) $limit ."
            ");
            $messages_data->execute(array(
                'me' => $GLOBALS['user']->id,
                'sender_receiver' => $received ? "rec" : "snd"
            ));
        }
        $messages_data->setFetchMode(PDO::FETCH_ASSOC);
        $messages = array();
        foreach ($messages_data as $data) {
            $messages[] = Message::buildExisting($data);
        }
        return $messages;
    }

    public function upload_attachment_action()
    {
        if ($GLOBALS['user']->id === "nobody") {
            throw new AccessDeniedException();
        }
        if (!$GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            throw new AccessDeniedException(_('Mailanhänge sind nicht erlaubt.'));
        }
        $file = $_FILES['file'];
        $output = array(
            'name' => $file['name'],
            'size' => $file['size']
        );

        $message_id = Request::option('message_id');
        $output['message_id'] = $message_id;

        $message_top_folder = MessageFolder::findTopFolder($message_id) ?: MessageFolder::createTopFolder($message_id);

        $error = $message_top_folder->validateUpload($file, $GLOBALS['user']->id);
        if ($error != null) {
            $this->response->set_status(400);
            $this->render_json(compact('error'));
            return;
        }

        $user = User::findCurrent();

        $file_object = new File();
        $file_object->user_id = $user->id;
        $file_object->mime_type = get_mime_type($output['name']);
        $file_object->name = $output['name'];
        $file_object->size = (int)$output['size'];
        $file_object->storage = 'disk';
        $file_object->author_name = $user->getFullName();

        $file_ref = $message_top_folder->createFile($file);

        if (!$file_ref instanceof FileRef) {
            $error = _('Ein Systemfehler ist beim Upload aufgetreten.');

            if ($file_ref instanceof MessageBox) {
                $error .= ' ' . $file_ref->message;
            }
            $this->response->set_status(400);
            $this->render_json(compact('error'));
            return;
        }

        $output['document_id'] = $file_ref->id;

        $output['icon'] = Icon::create(
            FileManager::getIconNameForMimeType(
                $file_ref->file->mime_type
            ),
            'clickable'
        )->asImg(['class' => "text-bottom"]);

        $this->render_json($output);
    }

    public function delete_attachment_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $attachment = FileRef::find(Request::option('document_id'));
        if ($attachment) {
            $attachment->delete();
        }
        $this->render_nothing();
    }

    public function preview_action()
    {
        if (Request::isXhr()) {
            $this->render_text(formatReady(Request::get('text')));
        }
    }

    public function delete_tag_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        DbManager::get()->execute("DELETE FROM message_tags WHERE user_id=? AND tag LIKE ?", array($GLOBALS['user']->id, Request::get('tag')));
        PageLayout::postMessage(MessageBox::success(_('Schlagwort gelöscht!')));
        $this->redirect('messages/overview');
    }

    public function setupSidebar($action)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/mail-sidebar.png');

        $actions = new ActionsWidget();
        if ($GLOBALS['user']->perms !== 'user') {
            $actions->addLink(
                _('Neue Nachricht schreiben'),
                $this->url_for('messages/write'),
                Icon::create('mail+add'),
                ['data-dialog' => 'width=700;height=700']
            );
        }
        if ($action !== 'sent' && MessageUser::hasUnreadByUserId($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Alle als gelesen markieren'),
                $this->url_for('messages/overview', ['read_all' => 1]),
                Icon::create('accept', 'clickable')
            );
        }
        $actions->addLink(
            _('Ausgewählte Nachrichten löschen'),
            '#',
            Icon::create('trash'),
            [
                'onclick' => "if (window.confirm('Wirklich %s Nachrichten löschen?'.toLocaleString().replace('%s', jQuery('#bulk tbody :checked').length))) { jQuery('#bulk').submit(); } return false;"
            ]
        );
        $sidebar->addWidget($actions);

        $search = new SearchWidget(URLHelper::getLink('?'));
        $search->addNeedle(_('Nachrichten durchsuchen'), 'search', true);
        $search->addFilter(_('Betreff'), 'search_subject');
        $search->addFilter(_('Inhalt'), 'search_content');
        $search->addFilter(_('Autor/-in'), 'search_autor');
        $sidebar->addWidget($search);

        $folderwidget = new ViewsWidget();
        $folderwidget->forceRendering();
        $folderwidget->title = _('Schlagworte');
        $folderwidget->id    = 'messages-tags';
        $folderwidget->addLink(
            _('Alle Nachrichten'),
            $this->url_for("messages/{$action}"),
            null,
            ['class' => 'tag all-tags']
        )->setActive(!Request::submitted("tag"));
        if (empty($this->tags)) {
            $folderwidget->style = 'display:none';
        } else {
            foreach ($this->tags as $tag) {
                $folderwidget->addLink(
                    $tag,
                    $this->url_for("messages/{$action}", compact('tag')),
                    null,
                    ['class' => 'tag']
                )->setActive(Request::get('tag') === $tag);
            }
        }

        $sidebar->addWidget($folderwidget);
    }
}
