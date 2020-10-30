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
        $template->unfollowed = $this->isUnfollowed();
        return $template;
    }

    public function notifyUsersForNewComment($comment)
    {
        $query = "
            SELECT auth_user_md5.user_id
            FROM auth_user_md5
                LEFT JOIN blubber_threads_unfollow ON (blubber_threads_unfollow.user_id = auth_user_md5.user_id AND blubber_threads_unfollow.thread_id = :thread_id)
            WHERE auth_user_md5.user_id != :me
                AND blubber_threads_unfollow.thread_id IS NULL
        ";
        $user_ids = DBManager::get()->fetchFirst($query, [
            'me'         => $GLOBALS['user']->id,
            'thread_id'  => $this->getId()
        ]);
        PersonalNotifications::add(
            $user_ids,
            $this->getURL(),
            sprintf(_('%s hat eine Nachricht geschrieben.'), get_fullname()),
            'blubberthread_' . $this->getId(),
            Icon::create('blubber'),
            true
        );
    }

    public function getAvatar()
    {
        return Icon::create('blubber')->asImagePath();
    }
}
