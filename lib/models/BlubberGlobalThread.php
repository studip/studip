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
        $template->unfollowed = !$this->isFollowedByUser();
        return $template;
    }

    /**
     * Lets a user follow a thread
     *
     * @param string|null $user_id Id of the user (optional, defaults to current user
     */
    public function addFollowingByUser($user_id = null)
    {
        if (Config::get()->BLUBBER_GLOBAL_THREAD_OPTOUT) {
            return parent::addFollowingByUser($user_id);
        }

        $query = "REPLACE INTO `blubber_threads_followstates`
                  VALUES (:thread_id, :user_id, 'followed', UNIX_TIMESTAMP())";
        DBManager::get()->execute($query, [
            ':thread_id' => $this->id,
            ':user_id'   => $user_id ?? $GLOBALS['user']->id,
        ]);
    }

    /**
     * Lets a user unfollow a thread
     *
     * @param string|null $user_id Id of the user (optional, defaults to current user
     */
    public function removeFollowingByUser($user_id = null)
    {
        if (Config::get()->BLUBBER_GLOBAL_THREAD_OPTOUT) {
            return parent::removeFollowingByUser($user_id);
        }

        $query = "DELETE FROM `blubber_threads_followstates`
                  WHERE `thread_id` = :thread_id
                    AND `user_id` = :user_id";
        DBManager::get()->execute($query, [
            ':thread_id' => $this->id,
            ':user_id'   => $user_id ?? $GLOBALS['user']->id,
        ]);
    }

    /**
     * Returns whether a user follows a thread.
     *
     * @param string|null $user_id Id of the user (optional, defaults to current user
     * @return bool
     */
    public function isFollowedByUser($user_id = null)
    {
        if (Config::get()->BLUBBER_GLOBAL_THREAD_OPTOUT) {
            return parent::isFollowedByUser($user_id);
        }

        $query = "SELECT 1
                  FROM `blubber_threads_followstates`
                  WHERE `thread_id` = :thread_id
                    AND `user_id` = :user_id
                    AND `state` = 'followed'";
        $followed = (bool) DBManager::get()->fetchColumn($query, [
            ':thread_id' => $this->getId(),
            ':user_id'   => $user_id ?? $GLOBALS['user']->id,
        ]);

        return $followed;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNotificationUsersQueryAndParameters()
    {
        $parameters = [
            ':user_id'   => $GLOBALS['user']->id,
            ':thread_id' => $this->id,
        ];

        if (Config::get()->BLUBBER_GLOBAL_THREAD_OPTOUT) {
            $query = "SELECT `auth_user_md5`.`user_id`
                      FROM `auth_user_md5`
                      LEFT JOIN `blubber_threads_followstates` ON (
                          `blubber_threads_followstates`.`thread_id` = :thread_id
                          AND `blubber_threads_followstates`.`user_id` = `auth_user_md5`.`user_id`
                          AND `blubber_threads_followstates`.`state` = 'unfollowed'
                      )
                      WHERE auth_user_md5.user_id != :user_id
                        AND `blubber_threads_followstates`.`user_id` IS NULL";
            return compact('query', 'parameters');
        }

        $query = "SELECT `auth_user_md5`.`user_id`
                  FROM `auth_user_md5`
                  JOIN `blubber_threads_followstates` ON (
                      `blubber_threads_followstates`.`thread_id` = :thread_id
                      AND `blubber_threads_followstates`.`user_id` = `auth_user_md5`.`user_id`
                      AND `blubber_threads_followstates`.`state` = 'followed'
                  )
                  WHERE auth_user_md5.user_id != :user_id";

        return compact('query', 'parameters');
    }

    public function getAvatar()
    {
        return Icon::create('blubber')->asImagePath();
    }
}
