<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * Controller for displaying streams in Blubber and write and edit Blubber-postings
 */
class StreamsController extends PluginController {

    protected $max_threads = 10; //how many threads should be displayed in infinity-scroll-stream before it should reload

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->assets_url = $this->plugin->getPluginURL() . '/assets/';

        PageLayout::setHelpKeyword("Basis/InteraktionBlubber");
    }

    /**
     * Displays global-stream
     */
    public function global_action() {
        PageLayout::setTitle(_("Globaler Blubberstream"));

        if (Navigation::hasItem('/community/blubber')) {
            Navigation::activateItem("/community/blubber");
        } else {
            throw new AccessDeniedException();
        }

        if (Request::get("delete_stream")) {
            $stream = new BlubberStream(Request::option("delete_stream"));
            if ($stream['user_id'] === $GLOBALS['user']->id) {
                $stream->delete();
                PageLayout::postMessage(MessageBox::success(_("Stream wurde erfolgreich gelöscht.")));
                Navigation::removeItem("/community/blubber/".Request::option("delete_stream"));
            }
        }

        $globalstream = BlubberStream::getGlobalStream();
        $this->tags = $globalstream->fetchTags();
        if (Request::get("hash")) {
            $this->search = Request::get("hash");
            $globalstream = new BlubberStream();
            $globalstream->filter_hashtags = [Request::get("hash")];
        }
        $this->threads = $globalstream->fetchThreads(0, $this->max_threads + 1);
        $this->more_threads = count($this->threads) > $this->max_threads;
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
        $this->contact_groups = DBManager::get()->query(
            "SELECT statusgruppen.* " .
            "FROM statusgruppen " .
            "WHERE statusgruppen.range_id = ".DBManager::get()->quote($GLOBALS['user']->id)." " .
            "ORDER BY name ASC " .
        "")->fetchAll(PDO::FETCH_ASSOC);
        $this->favourite_tags = $globalstream->fetchTags(time() - (86400 * 30), 50);
    }

    /**
     * Displays a blubber-stream for a course.
     * @throws AccessDeniedException if user has no access to course
     */
    public function forum_action() {
        object_set_visit(Context::getId(), "forum");
        if (Context::isCourse()) {
            $seminar = new Seminar(Context::get());
            $this->commentable = ($seminar->read_level == 0 || $GLOBALS['perm']->have_studip_perm("autor", Context::getId()));
        } else {
            $this->commentable = true;
        }
        if (!$this->commentable) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(Context::getHeaderLine()." - ".$this->plugin->getDisplayTitle());
        Navigation::getItem("/course/blubberforum")->setImage(Icon::create('blubber', 'info'));
        Navigation::activateItem("/course/blubberforum");
        $coursestream = BlubberStream::getCourseStream(Context::getId());
        $this->tags = $coursestream->fetchTags();
        if (Request::get("hash")) {
            $this->search = "#".Request::get("hash");
            $coursestream->filter_hashtags = [Request::get("hash")];
        }
        $this->threads = $coursestream->fetchThreads(0, $this->max_threads + 1);
        $this->more_threads = count($this->threads) > $this->max_threads;
        $this->course_id = Context::getId();
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
    }

    /**
     * Displays the profile-stream with all threads by the given user.
     */
    public function profile_action() {
        Navigation::activateItem("/profile/blubber");
        if (Request::get('extern')) {
            $this->user = BlubberExternalContact::find(Request::option('user_id'));
        } elseif (Request::option('user_id')) {
            $this->user = new BlubberUser(Request::option('user_id'));
        } elseif (Request::username('username')) {
            $this->user = BlubberUser::findByUsername(Request::username('username'));
        } else {
            $this->user = BlubberUser::find(User::findCurrent()->id);
        }
        PageLayout::setTitle($this->user->getName()." - Blubber");

        $profilestream = BlubberStream::getProfileStream($this->user->getId());
        $this->tags = $profilestream->fetchTags();
        $this->threads = $profilestream->fetchThreads(0, $this->max_threads + 1);
        $this->more_threads = count($this->threads) > $this->max_threads;
        $this->course_id = Context::getId();
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
        if ($this->user->id !== $GLOBALS['user']->id) {
            $this->isBuddy = is_a($this->user, "BlubberExternalContact")
                           ? $this->user->isFollowed()
                           : (User::findCurrent() && User::findCurrent()->isFriendOf($this->user));
        }
        if (count($this->threads) === 0 && $this->user->id !== $GLOBALS['user']->id) {
            PageLayout::postInfo(_('Dieser Nutzer hat noch nicht öffentlich bzw. auf sein Profil geblubbert.'));
        }
    }

    /**
     * Displays more comments (up to 20), rendered within a json-object.
     * @throws AccessDeniedException if context is a thread to which the user as no access
     */
    public function more_comments_action() {
        $thread = new BlubberPosting(Request::option("thread_id"));
        if ($thread['context_type'] === "course" && Context::isCourse()) {
            $seminar = new Seminar($thread['Seminar_id']);
            if ($seminar->read_level > 0 && !$GLOBALS['perm']->have_studip_perm("autor", $thread['Seminar_id'])) {
                throw new AccessDeniedException();
            }
        }
        BlubberPosting::$course_hashes = $thread['context_type'] === "course" ? $thread['Seminar_id'] : false;

        $output = [
            'more' => false,
            'comments' => []
        ];


        if (Request::option('count') !== 'all') {
            $count = Request::int("count", 20);
            $already_there = Request::int("already_there");
            $comments = $thread->getChildren($already_there, $count);
            $number_of_comments = $thread->getNumberOfChildren();
            $more = $number_of_comments - $already_there - $count;
            if ($more > 0) {
                $output['more'] = sprintf(ngettext('%u weiterer Kommentar', '%u weitere Kommentare', $more), $more);
            }
        } else {
            $comments = $thread->getChildren();
        }

        $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
        foreach ($comments as $posting) {
            $template = $factory->open("streams/comment.php");
            $template->set_attribute('posting', $posting);
            $template->set_attribute('course_id', $thread['Seminar_id']);
            $output['comments'][] = [
                'content' => $template->render(),
                'mkdate' => $posting['mkdate'],
                'posting_id' => $posting->getId()
            ];
        }
        $this->render_json($output);
    }

    /**
     * Displays more postings in the same stream (global, forum oder profile-stream).
     * This action is used by infinity-scroll and displays a maximum of
     * $this->max_threads threads.
     * @throws AccessDeniedException
     */
    public function more_postings_action() {
        $context_id = Request::option("context_id");
        switch (Request::get("stream")) {
            case "global":
                $stream = BlubberStream::getGlobalStream();
                break;
            case "course":
                $stream = BlubberStream::getCourseStream($context_id);
                break;
            case "profile":
                $stream = BlubberStream::getProfileStream($context_id);
                break;
            case "custom":
                $stream = new BlubberStream($context_id);
                break;
        }
        $output = [];
        $offset = $this->max_threads * Request::int("offset");
        $limit = $this->max_threads + 1;
        $stream_time = Request::int("stream_time");

        $threads = $stream->fetchThreads($offset, $limit, $stream_time);
        $output['more'] = count($threads) > $this->max_threads;
        if ($output['more']) {
            $threads = array_slice($threads, 0, $this->max_threads);
        }
        $output['threads'] = [];
        $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
        foreach ($threads as $posting) {
            $template = $factory->open("streams/_blubber.php");
            $template->set_attribute('thread', $posting);
            $template->set_attribute('course_id', Context::getId());
            $template->set_attribute('controller', $this);
            $output['threads'][] = [
                'content' => $template->render(),
                'discussion_time' => $posting['discussion_time'],
                'mkdate' => $posting['mkdate'],
                'posting_id' => $posting->getId()
            ];
        }
        $this->render_json($output);
    }

    /**
     * Writes a new thread and returns the metadata of the new posting as json.
     */
    public function new_posting_action() {
        if (!Request::isPost()) {
            throw new Exception("GET not supported");
        }
        $context = Request::option("context");
        $context_type = Request::option("context_type");
        if ($context_type === "course" && Context::isCourse()) {
            $seminar = new Seminar($context);
            if ($seminar->write_level > 0 && !$GLOBALS['perm']->have_studip_perm("autor", $context)) {
                throw new AccessDeniedException();
            }
        }
        BlubberPosting::$course_hashes = ($context_type === "course" ? $context : false);
        $output = [];
        $thread = new BlubberPosting(Request::option("thread"));
        $thread['seminar_id'] = $context_type === "course" ? $context : $GLOBALS['user']->id;
        $thread['context_type'] = $context_type;
        $thread['parent_id'] = 0;

        if ($GLOBALS['user']->id !== "nobody") {
            $thread['user_id']     = $GLOBALS['user']->id;
        } else {
            $thread['author_host'] = $_SERVER['REMOTE_ADDR'];

            if (Request::get("anonymous_security") === $_SESSION['blubber_anonymous_security']) {
                $contact_user = BlubberExternalContact::findByEmail(Request::get("anonymous_email"));
                $_SESSION['anonymous_email'] = Request::get("anonymous_email");
                $_SESSION['anonymous_name'] = $contact_user['name'] = Request::get("anonymous_name");
                $contact_user->store();
                $thread['user_id'] = $contact_user->getId();
                $thread['external_contact'] = 1;
            } else {
                throw new AccessDeniedException("No permission to write posting.");
            }
        }

        $thread['description'] = Request::get("content");
        $thread->store();

        $content = $this->transformMentions($thread['description'], $thread);

        if (mb_strpos($content, "\n") !== false) {
            $thread['name'] = mb_substr($content, 0, mb_strpos($content, "\n"));
            $thread['description'] = $content;
        } else {
            if (mb_strlen($content) > 255) {
                $thread['name'] = "";
            } else {
                $thread['name'] = $content;
            }
            $thread['description'] = $content;
        }
        if ($thread->store()) {
            if ($context_type === "private") {
                $statement = DBManager::get()->prepare(
                    "INSERT IGNORE INTO blubber_mentions " .
                    "SET user_id = :user_id, " .
                        "topic_id = :thread_id, " .
                        "mkdate = UNIX_TIMESTAMP() " .
                "");
                $statement->execute([
                    'user_id' => $GLOBALS['user']->id,
                    'thread_id' => $thread->getId()
                ]);
                $contact_groups = Request::getArray("contact_groups");
                foreach ($contact_groups as $gruppe_id) {
                    $users = DBManager::get()->query(
                        "SELECT user_id " .
                        "FROM statusgruppe_user " .
                            "INNER JOIN statusgruppen ON (statusgruppe_user.statusgruppe_id = statusgruppen.statusgruppe_id) " .
                        "WHERE statusgruppen.range_id = ".DBManager::get()->quote($GLOBALS['user']->id)." " .
                            "AND statusgruppe_user.statusgruppe_id = ".DBManager::get()->quote($gruppe_id)." " .
                    "")->fetchAll(PDO::FETCH_COLUMN, 0);
                    foreach ($users as $user_id) {
                        $statement->execute([
                            'user_id' => $user_id,
                            'thread_id' => $thread->getId()
                        ]);
                        //Meldung oder nicht Meldung, das ist hier die Frage.
                    }
                }
            }
            $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
            $template = $factory->open("streams/_blubber.php");
            $template->set_attribute('thread', $thread);
            $template->set_attribute('controller', $this);
            $output['content'] = $template->render();
            $output['discussion_time'] = time();
            $output['mkdate'] = time();
            $output['posting_id'] = $thread->getId();
        } else {
            $thread->delete();
        }
        $this->render_json($output);
    }

    /**
     * Returns the source-description of a blubber-posting, so it can be edited.
     */
    public function get_source_action() {
        $posting = new BlubberPosting(Request::get("topic_id"));
        $thread = new BlubberPosting($posting['root_id']);
        if ((($thread['context_type'] === "course" && !$GLOBALS['perm']->have_studip_perm("autor", $posting['Seminar_id']))
                or ($thread['context_type'] === "private" && !$thread->isRelated()))
                && !$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        $this->render_text($posting['description']);
    }

    /**
     * Edits a given posting and returns the new metadata as json.
     * @throws AccessDeniedException
     */
    public function edit_posting_action () {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException("GET not supported");
        }
        $posting = new BlubberPosting(Request::get("topic_id"));
        $thread = new BlubberPosting($posting['root_id']);
        if (($posting['user_id'] !== $GLOBALS['user']->id)
                && (!$GLOBALS['perm']->have_studip_perm("tutor", $posting['Seminar_id']))
                && !$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        $old_content = $posting['description'];
        $messaging = new messaging();

        $new_content = Request::get('content');
        $new_content = $this->transformMentions($new_content, $posting);

        if ($new_content && $old_content !== $new_content) {
            $posting['description'] = $new_content;
            if ($posting['topic_id'] === $posting['root_id']) {
                if (mb_strpos($new_content, "\n") !== false) {
                    $posting['name'] = mb_substr($new_content, 0, mb_strpos($new_content, "\n"));
                } else {
                    if (mb_strlen($new_content) > 255) {
                        $posting['name'] = "";
                    } else {
                        $posting['name'] = $new_content;
                    }
                }
            }
            $posting->store();
            if ($posting['user_id'] !== $GLOBALS['user']->id) {
                $message = sprintf(
                    _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum editiert.\n\nDie alte Version des Beitrags lautete:\n\n%s\n\nDie neue lautet:\n\n%s\n"),
                    get_fullname(), $old_content, $posting['description']
                );

                $message .= "\n\n";

                $message .= '[' . _('Link zu diesem Beitrag') . ']';
                $message .= URLHelper::getURL(
                    "{$GLOBALS['ABSOLUTE_URI_STUDIP']}plugins.php/blubber/streams/thread/{$posting->root_id}",
                    array_filter(['cid' => $posting->seminar_id]),
                    true
                );

                $messaging->insert_message(
                    $message,
                    get_username($posting['user_id']),
                    $GLOBALS['user']->id,
                    null, null, null, null,
                    _("Änderungen an Ihrem Posting.")
                );
            }
        } elseif(!$new_content) {
            if ($posting['user_id'] !== $GLOBALS['user']->id) {
                setTempLanguage($posting['user_id']);
                $messaging->insert_message(
                    sprintf(
                        _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum gelöscht.\n\nDer alte Beitrag lautete:\n\n%s\n"),
                        get_fullname(), $old_content
                    ),
                    get_username($posting['user_id']),
                    $GLOBALS['user']->id,
                    null, null, null, null,
                    _("Ihr Posting wurde gelöscht.")
                );
                restoreLanguage();
            }
            $posting->delete();
        }
        BlubberPosting::$course_hashes = ($thread['user_id'] !== $thread['Seminar_id'] ? $thread['Seminar_id'] : false);
        $this->render_text(BlubberPosting::format($posting['description']));
    }

    /**
     * Outputs the metadata for a posting. Used if a user cancels editing a posting.
     * @throws AccessDeniedException
     */
    public function refresh_posting_action() {
        $posting = new BlubberPosting(Request::get("topic_id"));
        $thread = new BlubberPosting($posting['root_id']);
        if (($thread['context_type'] === "course" && !$GLOBALS['perm']->have_studip_perm("autor", $posting['Seminar_id']))
                or ($thread['context_type'] === "private" && !$thread->isRelated())) {
            throw new AccessDeniedException();
        }
        BlubberPosting::$course_hashes = ($thread['context_type'] === "course" ? $thread['Seminar_id'] : false);
        $this->render_text(BlubberPosting::format($posting['description']));
    }

    /**
     * Writes a comment on a thread and outputs the metadata of new comment as json.
     * @throws AccessDeniedException
     */
    public function comment_action() {
        if (!Request::isPost()) {
            throw new Exception("GET not supported");
        }
        $context = Request::option("context");
        $thread = new BlubberPosting(Request::option("thread"));
        if ($thread['context_type'] === "course" && Context::isCourse()) {
            $seminar = new Seminar($context);
            if ($seminar->write_level > 0 && !$GLOBALS['perm']->have_studip_perm("autor", $context)) {
                throw new AccessDeniedException();
            }
        }
        BlubberPosting::$course_hashes = ($thread['context_type'] === "course" ? $thread['Seminar_id'] : false);
        if (!$thread->isNew() && $thread['Seminar_id'] === $context) {
            $output = [];
            $posting = new BlubberPosting();
            $posting['context_type'] = $thread['context_type'];
            $posting['seminar_id'] = $thread['Seminar_id'];
            $posting['root_id'] = $posting['parent_id'] = $thread->getId();
            $posting['name'] = "Re: ".$thread['name'];
            if ($GLOBALS['user']->id !== "nobody") {
                $posting['user_id'] = $GLOBALS['user']->id;
            } else {
                $posting['author_host'] = $_SERVER['REMOTE_ADDR'];

                if (Request::get("anonymous_security") === $_SESSION['blubber_anonymous_security']) {
                    $contact_user = BlubberExternalContact::findByEmail(Request::get("anonymous_email"));
                    $_SESSION['anonymous_email'] = Request::get("anonymous_email");
                    $_SESSION['anonymous_name'] = $contact_user['name'] = Request::get("anonymous_name");
                    $contact_user->store();
                    $posting['user_id'] = $contact_user->getId();
                    $posting['external_contact'] = 1;
                } else {
                    throw new AccessDeniedException("No permission to write posting.");
                }
            }
            $posting['description'] = Request::get("content");
            $posting->store();

            $content = Request::get("content");
            $content = $this->transformMentions($content, $posting);
            $posting['description'] = $content;
            $posting->store();

            $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views/streams");
            $template = $factory->open("comment.php");
            $template->set_attribute('posting', $posting);
            $template->set_attribute('course_id', $thread['Seminar_id']);
            $output['content'] = $template->render($template->render());
            $output['mkdate'] = time();
            $output['posting_id'] = $posting->getId();

            //Notifications:
            $user_ids = [];
            if ($thread['user_id'] && $thread['user_id'] !== $GLOBALS['user']->id) {
                $user_ids[] = $thread['user_id'];
            }
            foreach ((array) $thread->getChildren() as $comment) {
                if ($comment['user_id'] && ($comment['user_id'] !== $GLOBALS['user']->id) && (!$comment['external_contact'])) {
                    $user_ids[] = $comment['user_id'];
                }
            }
            $user_ids = array_unique($user_ids);
            foreach ($user_ids as $user_id) {
                setTempLanguage($user_id);
                $avatar = Visibility::verify('picture', $GLOBALS['user']->id, $user_id)
                        ? Avatar::getAvatar($GLOBALS['user']->id)
                        : Avatar::getNobody();
                PersonalNotifications::add(
                    $user_id,
                    PluginEngine::getURL(
                        $this->plugin,
                        ['cid' => $thread['context_type'] === "course" ? $thread['Seminar_id'] : null],
                        "streams/thread/".$thread->getId()
                    ),
                    sprintf(_("%s hat einen Kommentar geschrieben"), get_fullname()),
                    "posting_".$posting->getId(),
                    $avatar->getURL(Avatar::MEDIUM)
                );
                restoreLanguage();
            }

            $this->render_json($output);
        } else {
            $this->render_json([
                'error' => "Konnte thread nicht zuordnen."
            ]);
        }
    }

    /**
     * Saves given files (dragged into the textarea) and returns the link to the
     * file to the user as json.
     * @throws AccessDeniedException
     */
    public function post_files_action() {
        $context = Request::option("context") ? Request::get("context") : $GLOBALS['user']->id;
        $context_type = Request::option("context_type");
        if (!Request::isPost()
                || ($context_type === "course" && !$GLOBALS['perm']->have_studip_perm("autor", $context))) {
            throw new AccessDeniedException();
        }

        $output = [];

        foreach ($_FILES as $file) {

            $newfile = null; //is filled below
            $file_ref = null; //is also filled below


            if ($file['size']) {
                $document['user_id'] = $GLOBALS['user']->id;
                $document['filesize'] = $file['size'];

                try {
                    $root_dir = Folder::findTopFolder($GLOBALS['user']->id);
                    $root_dir = $root_dir->getTypedFolder();
                    $blubber_directory = Folder::findOneBySql(
                        "parent_id = :parent_id
                        AND
                        folder_type = 'PublicFolder'
                        AND
                        data_content = '[\"Blubber\"]'",
                        [
                            'parent_id' => $root_dir->getId()
                        ]
                    );


                    if ($blubber_directory) {
                        $blubber_directory = $blubber_directory->getTypedFolder();
                    } else {
                        //blubber directory not found: create it
                        $blubber_directory = FileManager::createSubFolder(
                            $root_dir,
                            $GLOBALS['user']->getAuthenticatedUser(),
                            'PublicFolder',
                            'Blubber',
                            _('Ihre Dateien aus Blubberstreams')
                        );

                        if (!$blubber_directory instanceof FolderType) {
                            throw new Exception($blubber_directory[0]);
                        }

                        $blubber_directory->data_content = ['Blubber'];
                        $blubber_directory->store();
                    }

                    if ($blubber_directory) {
                        //ok, blubber directory exists: we can handle the uploaded file

                        $error_string = $blubber_directory->validateUpload(
                            $file,
                            $GLOBALS['user']->id
                        );

                        if ($error_string) {
                            throw new Exception($error_string);
                        }


                        $file['tmp_path'] = $file['tmp_name'];

                        $file_ref = $blubber_directory->createFile($file);

                        if($file_ref) {
                            //we can't use the FileRef's getDownloadURL() here,
                            //because the getDownloadURL method does not provide
                            //the full URL!
                            $url = mb_substr($GLOBALS['ABSOLUTE_URI_STUDIP'], 0, -1) . URLHelper::getUrl(
                                '/sendfile.php',
                                [
                                    'type' => '0',
                                    'file_id' => $file_ref->id,
                                    'file_name' => $file_ref->name
                                ],
                                true
                            );
                            $success = true;
                        } else {
                            throw new Exception('File cannot be created!');
                        }

                    }
                } catch (Exception $e) {
                    $output['errors'][] = $e->getMessage();
                    $success = false;
                }


                if ($success) {
                    $type = null;

                    mb_strpos($file['type'], 'image') === false || $type = "img";
                    mb_strpos($file['type'], 'video') === false || $type = "video";
                    if (mb_strpos($file['type'], 'audio') !== false || mb_strpos($file_ref['name'], '.ogg') !== false) {
                         $type = "audio";
                    }
                    if ($type) {
                        $output['inserts'][] = "[".$type."]".$url;
                    } else {
                        $output['inserts'][] = "[".$file_ref['name']."]".$url;
                    }
                }
            }
        }
        $this->render_json($output);
    }

    /**
     * Displays a single thread.
     * @param string $thread_id
     */
    public function thread_action($thread_id)
    {
        $this->thread = new BlubberPosting($thread_id);
        if ($this->thread['context_type'] === "private") {
            if (!in_array($GLOBALS['user']->id, $this->thread->getRelatedUsers())) {
                throw new AccessDeniedException("Kein Zugriff auf diesen Blubb.");
            }
        } elseif ($this->thread['context_type'] === "course") {
            if (!$GLOBALS['perm']->have_studip_perm("user", $this->thread['Seminar_id'])) {
                throw new AccessDeniedException("Kein Zugriff auf diesen Blubb.");
            }
        }
        if ($this->thread['context_type'] === "course") {
            PageLayout::setTitle(Context::getHeaderLine()." - ".$this->plugin->getDisplayTitle());
        } elseif($this->thread['context_type'] === "public") {
            PageLayout::setTitle(get_fullname($this->thread['user_id'])." - Blubber");
        } elseif($this->thread['context_type'] === "private") {
            PageLayout::setTitle(_("Privater Blubber"));
        }

        if ($this->thread['context_type'] === "course") {
            Navigation::getItem("/course/blubberforum")->setImage(Icon::create('blubber', 'info'));
            Navigation::activateItem('/course/blubberforum');
        } elseif($this->thread['context_type'] === "public") {
            if (Navigation::hasItem('/profile/blubber')) {
                Navigation::activateItem('/profile/blubber');
            } elseif (Navigation::hasItem('/community/blubber')) {
                Navigation::activateItem('/community/blubber');
            }
        } else {
            if (Navigation::hasItem('/community/blubber')) {
                Navigation::activateItem('/community/blubber');
            }
        }

        $this->course_id     = Context::getId();
        $this->single_thread = true;
        BlubberPosting::$course_hashes = ($this->thread['user_id'] !== $this->thread['Seminar_id'] ? $this->thread['Seminar_id'] : false);
    }

    /**
     * Current user is going to follow (add as buddy) the given user, who could
     * also be an external contact.
     */
    public function follow_user_action() {
        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException();
        }
        if (Request::get("external_contact")) {
            $user = BlubberExternalContact::find(Request::option("user_id"));
        } else {
            $user = new BlubberUser(Request::option("user_id"));
        }
        if (!$user->isNew()) {
            if (is_a($user, "BlubberExternalContact")) {
                $statement = DBManager::get()->prepare(
                    "INSERT IGNORE INTO blubber_follower " .
                    "SET studip_user_id = :user_id, " .
                        "external_contact_id = :contact_id, " .
                        "left_follows_right = '1' " .
                "");
                $success = $statement->execute([
                    'user_id' => $GLOBALS['user']->id,
                    'contact_id' => $user->getId()
                ]);
                if ($success) {
                    NotificationCenter::postNotification('BlubberExternalContactDidAdd', $user);
                }
            } else {
                Contact::import([
                'owner_id' => User::findCurrent()->id,
                'user_id' => $user->id]
                    )->store();
            }
        }
        $this->render_json([
            'success' => 1,
            'message' => (string) MessageBox::success(_("Kontakt hinzugefügt"))
        ]);
    }

    public function custom_action($stream_id)
    {
        $this->stream = new BlubberStream($stream_id);

        if (Request::get('hash')) {
            $this->search = Request::get('hash');
            $this->stream->filter_hashtags = [$this->search];
        }

        $this->tags = $this->stream->fetchTags();
        if ($this->stream['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException("Not your stream.");
        }
        $this->threads = $this->stream->fetchThreads(0, $this->max_threads + 1);
        $this->more_threads = count($this->threads) > $this->max_threads;
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
    }

    /**
     * Create a new or edit an existing stream.
     * @param string,null $stream_id
     */
    public function edit_action($stream_id = null) {
        $this->stream = new BlubberStream($stream_id);
        if ($GLOBALS['user']->id === "nobody") {
            throw new AccessDeniedException();
        }
        if ($stream_id) {
            Navigation::activateItem("/community/blubber/".$stream_id);
        }
        if ($this->stream['user_id'] && $this->stream['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException("Not allowed to edit stream");
        }
        $this->contact_groups = DBManager::get()->query(
            "SELECT statusgruppen.* " .
            "FROM statusgruppen " .
            "WHERE statusgruppen.range_id = ".DBManager::get()->quote($GLOBALS['user']->id)." " .
            "ORDER BY name ASC " .
        "")->fetchAll(PDO::FETCH_ASSOC);
        if (Request::isPost()) {
            $new = $this->stream->isNew();
            $this->stream['name'] = Request::get("name");
            $this->stream['user_id'] = $GLOBALS['user']->id;
            $this->stream['sort'] = Request::get("sort");
            $this->stream['defaultstream'] = Request::int("defaultstream", 0);

            //Pool-rules
            $this->stream['pool_courses'] = Request::get("pool_courses_check")
                ? (in_array("all", Request::getArray("pool_courses")) ? ["all"] : Request::getArray("pool_courses"))
                : null;
            $this->stream['pool_groups'] = Request::get("pool_groups_check")
                ? (in_array("all", Request::getArray("pool_groups")) ? ["all"] : Request::getArray("pool_groups"))
                : null;
            $this->stream['pool_hashtags'] = Request::get("pool_hashtags_check")
                ? preg_split("/\s+/", Request::get("pool_hashtags"), null, PREG_SPLIT_NO_EMPTY)
                : null;
            if (is_array($this->stream['pool_hashtags'])) {
                $this->stream['pool_hashtags'] = array_map(function ($tag) {
                    while ($tag[0] === "#") {
                        $tag = mb_substr($tag, 1);
                    }
                    return $tag;
                }, $this->stream['pool_hashtags']);
            }

            //Filter-rules
            $this->stream['filter_type'] = Request::get("filter_type_check")
                ? Request::getArray("filter_type")
                : null;
            $this->stream['filter_courses'] = Request::get("filter_courses_check")
                ? (in_array("all", Request::getArray("filter_courses")) ? ["all"] : Request::getArray("filter_courses"))
                : null;
            $this->stream['filter_groups'] = Request::get("filter_groups_check")
                ? (in_array("all", Request::getArray("filter_groups")) ? ["all"] : Request::getArray("filter_groups"))
                : null;
            $this->stream['filter_hashtags'] = Request::get("filter_hashtags_check")
                ? preg_split("/\s+/", Request::get("filter_hashtags"), null, PREG_SPLIT_NO_EMPTY)
                : null;
            if (is_array($this->stream['filter_hashtags'])) {
                $this->stream['filter_hashtags'] = array_map(function ($tag) {
                    while ($tag[0] === "#") {
                        $tag = mb_substr($tag, 1);
                    }
                    return $tag;
                }, $this->stream['filter_hashtags']);
            }
            $this->stream['filter_nohashtags'] = Request::get("filter_nohashtags_check")
                ? preg_split("/\s+/", Request::get("filter_nohashtags"), null, PREG_SPLIT_NO_EMPTY)
                : null;
            if (is_array($this->stream['filter_nohashtags'])) {
                $this->stream['filter_nohashtags'] = array_map(function ($tag) {
                    while ($tag[0] === "#") {
                        $tag = mb_substr($tag, 1);
                    }
                    return $tag;
                }, $this->stream['filter_nohashtags']);
            }

            $this->stream->store();
            if ($_FILES['image']['size']) {
                StreamAvatar::getAvatar($this->stream->getId())->createFromUpload("image");
            }
            if ($new) {
                $this->redirect(PluginEngine::getURL($this->plugin, [], "streams/custom/".$this->stream->getId()));
            } else {
                PageLayout::postMessage(MessageBox::success(_("Stream wurde gespeichert.")));
            }
        }
    }

    public function delete_action($stream_id = null)
    {
        $this->stream = new BlubberStream($stream_id);
        if ($GLOBALS['user']->id === "nobody") {
            throw new AccessDeniedException();
        }
        if ($stream_id) {
            Navigation::activateItem("/community/blubber/" . $stream_id);
        }
        if ($this->stream['user_id'] && $this->stream['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException("Not allowed to edit stream");
        }
        $this->stream->delete();
        PageLayout::postMessage(MessageBox::success(_("Blubberstream gelöscht.")));
        $path = "streams/global";
        foreach (BlubberStream::findMine() as $stream) {
            if ($stream['defaultstream']) {
                $path = "streams/custom/".$stream->getId();
            }
        }
        $this->redirect(PluginEngine::getURL($this->plugin, [], $path));
    }

    public function get_streams_threadnumber_action() {
        $stream = new BlubberStream();
        //Pool-rules
        $stream['pool_courses'] = Request::get("pool_courses_check")
            ? (in_array("all", Request::getArray("pool_courses")) ? ["all"] : Request::getArray("pool_courses"))
            : null;
        $stream['pool_groups'] = Request::get("pool_groups_check")
            ? (in_array("all", Request::getArray("pool_groups")) ? ["all"] : Request::getArray("pool_groups"))
            : null;
        $stream['pool_hashtags'] = Request::get("pool_hashtags_check")
            ? preg_split("/\s+/", Request::get("pool_hashtags"), null, PREG_SPLIT_NO_EMPTY)
            : null;
        if (is_array($stream['pool_hashtags'])) {
            $stream['pool_hashtags'] = array_map(function ($tag) {
                while ($tag[0] === "#") {
                    $tag = mb_substr($tag, 1);
                }
                return $tag;
            }, $stream['pool_hashtags']);
        }

        //Filter-rules
        $stream['filter_type'] = Request::get("filter_type_check")
            ? Request::getArray("filter_type")
            : null;
        $stream['filter_courses'] = Request::get("filter_courses_check")
            ? (in_array("all", Request::getArray("filter_courses")) ? ["all"] : Request::getArray("filter_courses"))
            : null;
        $stream['filter_groups'] = Request::get("filter_groups_check")
            ? (in_array("all", Request::getArray("filter_groups")) ? ["all"] : Request::getArray("filter_groups"))
            : null;
        $stream['filter_hashtags'] = Request::get("filter_hashtags_check")
            ? preg_split("/\s+/", Request::get("filter_hashtags"), null, PREG_SPLIT_NO_EMPTY)
            : null;
        if (is_array($stream['filter_hashtags'])) {
            $stream['filter_hashtags'] = array_map(function ($tag) {
                while ($tag[0] === "#") {
                    $tag = mb_substr($tag, 1);
                }
                return $tag;
            }, $stream['filter_hashtags']);
        }
        $stream['filter_nohashtags'] = Request::get("filter_nohashtags_check")
            ? preg_split("/\s+/", Request::get("filter_nohashtags"), null, PREG_SPLIT_NO_EMPTY)
            : null;
        if (is_array($stream['filter_nohashtags'])) {
            $stream['filter_nohashtags'] = array_map(function ($tag) {
                while ($tag[0] === "#") {
                    $tag = mb_substr($tag, 1);
                }
                return $tag;
            }, $stream['filter_nohashtags']);
        }

        $this->render_text($stream->fetchNumberOfThreads());
    }

    public function reshare_action($thread_id) {
        if (!Request::isPost()) {
            throw new Exception("Wrong method for this action - use POST instead");
        }
        $this->thread = new BlubberPosting($thread_id);
        $success = $this->thread->reshare();

        $template = $this->get_template_factory()->open("streams/_blubber.php");
        $template->set_attributes($this->get_assigned_variables());
        $template->set_layout(null);
        $output = $template->render();
        $this->render_text($output);
    }

    public function unshare_action($thread_id) {
        if (!Request::isPost()) {
            throw new Exception("Wrong method for this action - use POST instead");
        }
        $this->thread = new BlubberPosting($thread_id);
        $success = $this->thread->unreshare();

        $template = $this->get_template_factory()->open("streams/_blubber.php");
        $template->set_attributes($this->get_assigned_variables());
        $template->set_layout(null);
        $output = $template->render();
        $this->render_text($output);
    }

    public function public_panel_action() {
        $thread_id = Request::option("thread_id");
        $this->thread = new BlubberPosting($thread_id);
        if ($this->thread['context_type'] !== "public") {
            throw new AccessDeniedException("No public posting.");
        }
        $template = $this->get_template_factory()->open("streams/public_panel.php");
        $template->set_attributes($this->get_assigned_variables());
        $template->set_layout(null);
        $output = $template->render();
        $this->render_text($output);
    }

    public function private_panel_action() {
        $thread_id = Request::option("thread_id");
        $this->thread = new BlubberPosting($thread_id);
        if ($this->thread['context_type'] !== "private") {
            throw new AccessDeniedException("No public posting.");
        }
        $template = $this->get_template_factory()->open("streams/private_panel.php");
        $template->set_attributes($this->get_assigned_variables());
        $template->set_layout(null);
        $output = $template->render();
        $this->render_text($output);
    }

    public function get_possible_mentions_action() {
        $output = [
            ['id' => 1, 'name' => "Rasmus", "avatar" => null]
        ];
        $this->render_json($output);
    }

    public function addTagCloudWidgetToSidebar($tags, $context = 'global')
    {
        if (count($tags) && $tags[0]) {
            $cloud = new LinkCloudWidget();
            $cloud->setTitle(_('Hashtags'));
            $maximum = $tags[0]['counter'];
            //$average = ceil(array_sum(array_filter($tags, function ($val) { return $val['counter']; })) / count($tags));
            foreach ($tags as $tag) {
                $cloud->addLink(
                    "#".$tag['tag'],
                    URLHelper::getURL("plugins.php/blubber/streams/$context", ['hash' => $tag['tag']]),
                    ceil(10 * $tag['counter'] / $maximum)
                );
            }
            Sidebar::get()->addWidget($cloud, 'tagcloud');
        }
    }

    private function transformMentions($content, BlubberPosting $source)
    {
        BlubberPosting::$mention_posting_id = $source->getId();
        StudipTransformFormat::addStudipMarkup(
            'mention1',
            '(?:^|\W)(@\"[^\n\"]*\")',
            '',
            'BlubberPosting::mention'
        );
        StudipTransformFormat::addStudipMarkup(
            'mention2',
            '(?:^|\W)(@[^\s]*[\d\w_]+)',
            '',
            'BlubberPosting::mention'
        );
        return transformBeforeSave($content);
    }
}
