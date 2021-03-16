<?php

class BlubberGlobalThread extends BlubberThread
{
    public function isReadable(string $user_id = null)
    {
        return true;
    }

    public function getName()
    {
        return _("Globaler Blubber");
    }

    public function getContextTemplate()
    {
        $template = $GLOBALS['template_factory']->open('blubber/global_context');
        $template->thread = $this;
        $template->hashtags = $this->getHashtags(time() - 86400 * 365);
        $template->unfollowed = !$this->isFollowed();
        return $template;
    }

    public function notifyUsersForNewComment($comment)
    {
        $query = "SELECT auth_user_md5.user_id
                  FROM auth_user_md5
                  JOIN blubber_threads_follow ON (
                      blubber_threads_follow.thread_id = :thread_id
                      AND blubber_threads_follow.user_id = auth_user_md5.user_id
                  )
                  WHERE auth_user_md5.user_id != :user_id";
        $parameters = [
            ':user_id'   => $GLOBALS['user']->id,
            ':thread_id' => $this->id,
        ];

        DBManager::get()->fetchAll(
            $query,
            $parameters,
            function ($row) {
                $user_id = $row['user_id'];

                setTempLanguage($user_id);

                PersonalNotifications::add(
                    $user_id,
                    $this->getURL(),
                    sprintf(_('%s hat eine Nachricht geschrieben.'), get_fullname()),
                    'blubberthread_' . $this->id,
                    Icon::create('blubber'),
                    true
                );

                restoreLanguage();
            }
        );
    }

    public function getAvatar()
    {
        return Icon::create('blubber')->asImagePath();
    }
}
