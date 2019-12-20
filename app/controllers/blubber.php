<?php

class BlubberController extends AuthenticatedController
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Blubber-Messenger'));
    }

    public function index_action($thread_id = null)
    {
        Navigation::activateItem('/community/blubber');

        $this->threads = BlubberThread::findMyGlobalThreads(51);
        if (count($this->threads) > 20) {
            array_pop($this->threads);
            $this->threads_more_down = 1;
        }

        if ($thread_id) {
            $GLOBALS['user']->cfg->store('BLUBBER_DEFAULT_THREAD', $thread_id);
        } else {
            $thread_id = $GLOBALS['user']->cfg->BLUBBER_DEFAULT_THREAD;
        }

        if ($thread_id) {
            $this->thread = BlubberThread::find($thread_id);
            if (!$this->thread || !$this->thread->isReadable()) {
                $this->thread = null;
            } else {
                $this->thread->markAsRead();
            }
        }

        if (!$this->thread) {
            $this->thread = array_pop(array_reverse($this->threads));
        }

        if ($this->thread->getId() === 'global') {
            $condition = "context_type = 'public' AND visible_in_stream = 1
                         ORDER BY mkdate
                         DESC LIMIT 31";
            $global_threads = BlubberThread::findBySQL($condition);
            if (count($global_threads) > 30) {
                array_pop($global_threads);
                $this->stream_more_down = 1;
            } else {
                $this->stream_more_down = 0;
            }

            $this->stream_data = [];
            foreach ($global_threads as $thread) {
                if ($thread->isVisibleInStream()) {
                    $data = $thread->toRawArray();
                    $data['mkdate']    = (int) $data['mkdate'];
                    $data['chdate']    = (int) $data['chdate'];
                    $data['avatar']    = Avatar::getAvatar($thread['user_id'])->getURL(Avatar::MEDIUM);
                    $data['html']      = $thread->getContentTemplate()->render();
                    $data['user_name'] = $thread->user->getFullName();

                    $this->stream_data[] = $data;
                }
            }
        } else {
            $this->thread_data = $this->thread->getJSONData();
        }

        if (!Avatar::getAvatar($GLOBALS['user']->id)->is_customized() && !$_SESSION['already_asked_for_avatar']) {
            $_SESSION['already_asked_for_avatar'] = true;
            PageLayout::postInfo(sprintf(
                _('Wollen Sie ein Avatar-Bild nutzen? %sLaden Sie jetzt ein Bild hoch%s.'),
                '<a href="' . URLHelper::getLink("dispatch.php/avatar/update/user/" . $GLOBALS['user']->id) . '" data-dialog>',
                '</a>'
            ));
        }

        if (Request::isDialog()) {
            PageLayout::setTitle($this->thread->getName());
        }
        $this->buildSidebar();

        if (Request::isDialog()) {
            $this->render_template('blubber/dialog');
        }
    }

    public function compose_action($thread_id = null)
    {
        $this->thread = BlubberThread::find($thread_id);
        if ($this->thread && !$this->thread->isWritable()) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle($this->thread ? _('Blubber bearbeiten') : _('Neuer Blubber'));

        if (Request::isPost() && Request::get('public_blubber')) {
            CSRFProtection::verifySecurityToken();
            if (!$this->thread) {
                $this->thread = new BlubberThread();
                $this->thread['context_type'] = 'public';
                $this->thread['context_id'] = 'global';
                $this->thread['user_id'] = $GLOBALS['user']->id;
                $this->thread['external_contact'] = 0;
                $this->thread['display_class'] = null;
                $this->thread['visible_in_stream'] = 1;
                $this->thread['commentable'] = 1;
            }
            $this->thread['content'] = Request::get('public_blubber');
            $this->thread->store();

            $GLOBALS['user']->cfg->store('BLUBBER_DEFAULT_THREAD', $this->thread->getId());

            $this->redirect('blubber');
            return;
        }

        if (Request::isPost() && count(Request::getArray('user_ids'))) {
            $user_ids = array_filter(Request::getArray('user_ids'));

            if (count($user_ids) === 1) {
                //try to redirect to an existing 2 person thread:
                $query = "SELECT blubber_threads.thread_id
                          FROM blubber_threads
                          JOIN blubber_mentions
                            ON blubber_mentions.thread_id = blubber_threads.thread_id
                          JOIN blubber_mentions AS blubber_mentions_me
                            ON blubber_mentions_me.thread_id = blubber_threads.thread_id
                          JOIN blubber_mentions AS blubber_mentions_friend
                            ON blubber_mentions_friend.thread_id = blubber_threads.thread_id
                          WHERE blubber_threads.context_type = 'private'
                            AND blubber_mentions_me.user_id = :me
                            AND blubber_mentions_friend.user_id = :friend
                          GROUP BY blubber_threads.thread_id
                          HAVING COUNT(blubber_mentions.user_id) = 2
                          ORDER BY blubber_threads.mkdate DESC
                          LIMIT 1";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    'me' => $GLOBALS['user']->id,
                    'friend' => $user_ids[0]
                ]);
                $thread_id = $statement->fetchColumn();
                if ($thread_id) {
                    $this->redirect("blubber/index/{$thread_id}");
                    return;
                }
            }
            $blubber = new BlubberThread();
            $blubber['context_type'] = 'private';
            $blubber['context_id'] = 'global';
            $blubber['user_id'] = $GLOBALS['user']->id;
            $blubber['external_contact'] = 0;
            $blubber['display_class'] = null;
            $blubber['visible_in_stream'] = 1;
            $blubber['commentable'] = 1;
            $blubber['content'] = '';
            $blubber->store();

            $query = "INSERT IGNORE INTO blubber_mentions
                      SET thread_id = :thread_id,
                          user_id = :user_id,
                          external_contact = 0,
                          mkdate = UNIX_TIMESTAMP()";
            $insert = DBManager::get()->prepare($query);

            $user_ids[] = $GLOBALS['user']->id;
            foreach ($user_ids as $user_id) {
                $insert->execute([
                    'thread_id' => $blubber->getId(),
                    'user_id'   => $user_id,
                ]);
            }
            $this->redirect("blubber/index/{$blubber->getId()}");
            return;
        }

        $this->contacts = Contact::findBySQL("JOIN auth_user_md5 USING (user_id) WHERE owner_id = ? ORDER BY auth_user_md5.Nachname ASC, auth_user_md5.Vorname ASC", [
            $GLOBALS['user']->id
        ]);
    }

    public function delete_action($thread_id)
    {
        $this->thread = BlubberThread::find($thread_id);
        if (!$this->thread->isWritable()) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            CSRFProtection::verifySecurityToken();
            $this->thread->delete();
            PageLayout::postSuccess(_('Der Blubber wurde gelöscht.'));
        }
        $this->redirect("blubber/index");
        return;
    }

    public function write_to_action($user_id = null)
    {
        $user_ids = array_filter(Request::getArray('user_ids'));
        if (!$user_ids) {
            $user_ids = [$user_id];
        }

        if (count($user_ids) === 1) {
            //try to redirect to an existing 2 person thread:
            $query = "SELECT blubber_threads.thread_id
                      FROM blubber_threads
                      JOIN blubber_mentions
                        ON blubber_mentions.thread_id = blubber_threads.thread_id
                      JOIN blubber_mentions AS blubber_mentions_me
                        ON blubber_mentions_me.thread_id = blubber_threads.thread_id
                      JOIN blubber_mentions AS blubber_mentions_friend
                        ON blubber_mentions_friend.thread_id = blubber_threads.thread_id
                      WHERE blubber_threads.context_type = 'private'
                        AND blubber_mentions_me.user_id = :me
                        AND blubber_mentions_friend.user_id = :friend
                      GROUP BY blubber_threads.thread_id
                      HAVING COUNT(blubber_mentions.user_id) = 2
                      ORDER BY blubber_threads.mkdate DESC
                      LIMIT 1";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                'me'     => $GLOBALS['user']->id,
                'friend' => $user_ids[0],
            ]);
            $thread_id = $statement->fetchColumn();
            if ($thread_id) {
                $this->redirect("blubber/index/{$thread_id}");
                return;
            }
        }
        $blubber = new BlubberThread();
        $blubber['context_type'] = 'private';
        $blubber['context_id'] = 'global';
        $blubber['user_id'] = $GLOBALS['user']->id;
        $blubber['external_contact'] = 0;
        $blubber['display_class'] = null;
        $blubber['visible_in_stream'] = 1;
        $blubber['commentable'] = 1;
        $blubber['content'] = '';
        $blubber->store();

        $query = "INSERT IGNORE INTO blubber_mentions
                  SET thread_id = :thread_id,
                      user_id = :user_id,
                      external_contact = 0,
                      mkdate = UNIX_TIMESTAMP()";
        $insert = DBManager::get()->prepare($query);

        $user_ids[] = $GLOBALS['user']->id;
        foreach ($user_ids as $user_id) {
            $insert->execute([
                'thread_id' => $blubber->getId(),
                'user_id'   => $user_id,
            ]);
        }
        $this->redirect("blubber/index/{$blubber->getId()}");
    }

    public function to_course_action($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('user', $course_id)) {
            throw new AccessDeniedException();
        }

        $condition = "context_type = 'course'
                      AND context_id = ?
                      AND visible_in_stream = 1
                      AND content IS NULL";
        $thread = BlubberThread::findOneBySQL($condition, [$course_id]);
        if (!$thread) {
            //create the default-thread for this context
            $thread = new BlubberThread();
            $thread['user_id'] = $GLOBALS['user']->id;
            $thread['external_contact'] = 0;
            $thread['context_type'] = 'course';
            $thread['context_id'] = $course_id;
            $thread['visible_in_stream'] = 1;
            $thread['commentable'] = 1;
            $thread->store();
        }
        $this->redirect("blubber/index/{$thread->getId()}");
    }

    /**
     * Saves given files (dragged into the textarea) and returns the link to the
     * file to the user as json.
     * @throws AccessDeniedException
     */
    public function upload_files_action()
    {
        $context = Request::get('context', $GLOBALS['user']->id);
        $context_type = Request::option('context_type');
        if (!Request::isPost()
            || ($context_type === 'course' && !$GLOBALS['perm']->have_studip_perm('autor', $context))
        ) {
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
                         AND folder_type = 'PublicFolder'
                         AND data_content = :content",
                        [
                            'parent_id' => $root_dir->getId(),
                            'content'   => json_encode(['Blubber']),
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

                        if ($file_ref) {
                            $oldbase = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
                            $url = $file_ref->getDownloadURL();
                            URLHelper::setBaseURL($oldbase);
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

                    if (mb_strpos($file['type'], 'image') !== false) {
                        $type = 'img';
                    }
                    if (mb_strpos($file['type'], 'video') !== false) {
                        $type = 'video';
                    }
                    if (mb_strpos($file['type'], 'audio') !== false || mb_strpos($file_ref['name'], '.ogg') !== false) {
                        $type = 'audio';
                    }
                    if ($type) {
                        $output['inserts'][] = "[{$type}]{$url}";
                    } else {
                        $output['inserts'][] = "[{$file_ref['name']}]{$url}";
                    }
                }
            }
        }
        $this->render_json($output);
    }

    public function add_member_to_private_action($thread_id)
    {
        $this->thread = BlubberThread::find($thread_id);
        if (!$this->thread['context_type'] === 'private' || !$this->thread->isReadable()) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Person hinzufügen'));
        if (Request::isPost() && Request::option('user_id')) {
            $query = "INSERT IGNORE INTO blubber_mentions
                      SET thread_id = :thread_id,
                          user_id = :user_id,
                          external_contact = 0,
                          mkdate = UNIX_TIMESTAMP()";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                'thread_id' => $thread_id,
                'user_id'   => Request::option('user_id'),
            ]);
            $this->response->add_header(
                'X-Dialog-Execute',
                'STUDIP.Blubber.refreshThread'
            );
            $this->response->add_header('X-Dialog-Close', '1');
            $this->render_json([
                'thread_id' => $thread_id,
            ]);
        }
    }

    public function private_to_studygroup_action(BlubberThread $thread)
    {
        if (!$this->thread['context_type'] === 'private' || !$this->thread->isReadable()) {
            throw new AccessDeniedException();
        }
        if (Request::isPost() && count(studygroup_sem_types())) {
            $course = new Course();
            $course['name'] = Request::get('name');
            $course['status'] = array_shift(studygroup_sem_types());
            $course['start_time'] = Semester::findCurrent()->beginn;
            $course->store();

            if ($_FILES['avatar'] && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                CourseAvatar::getAvatar($course->getId())->createFromUpload('avatar');
            }

            $query = "SELECT user_id
                      FROM blubber_mentions
                      WHERE thread_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$thread->id]);
            foreach ($statement->fetchFirst() as $user_id) {
                $member = new CourseMember();
                $member['user_id'] = $user_id;
                $member['seminar_id'] = $course->getId();
                $member['status'] = $user_id === $this->thread['user_id'] ? 'dozent' : 'tutor';
                $member->store();
            }

            $this->thread['context_type'] = 'course';
            $this->thread['context_id'] = $course->getId();
            $this->thread['content'] = trim($this->thread['content']) ?: null;
            $this->thread->store();

            PluginManager::getInstance()->setPluginActivated(
                PluginManager::getInstance()->getPlugin('Blubber')->getPluginId(),
                $course->getId(),
                true
            );

            PageLayout::postSuccess(sprintf(_("Studiengruppe '%s' wurde angelegt."), $course['name']));
            $this->redirect(URLHelper::getURL('seminar_main.php', ['auswahl' => $course->getId()]));
        }
    }

    protected function buildSidebar()
    {
        $threads_widget = Sidebar::Get()->addWidget(
            new BlubberThreadsWidget(),
            'threads'
        );
        foreach ($this->threads as $thread) {
            $threads_widget->addThread($thread);
        }

        if ($this->thread) {
            $threads_widget->setActive($this->thread->getId());
        }

        $threads_widget->withComposer();
    }
}
