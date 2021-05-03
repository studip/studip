<?php
namespace RESTAPI\Routes;

/**
 * @license GPL 2 or later
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 *
 * @condition course_id ^[a-f0-9]{1,32}$
 * @condition stream_id ^(global|[a-f0-9]{1,32})$
 * @condition user_id ^[a-f0-9]{1,32}$
 * @condition blubber_id ^[a-f0-9]{1,32}$
 */
class Blubber extends \RESTAPI\RouteMap
{

    /**
     * Get content and some comments for a blubber-thread or for the "global" thread all "public" threads.
     *
     * @get /blubber/threads/:thread_id
     * @param string $thread_id   id of the blubber thread or "global" if you want public threads (not comments). Remind the global thread is a virtual thread with a special behaviour.
     * @return Array   the blubber as array
     */
    public function getThreadData($thread_id)
    {
        if (!$GLOBALS['perm']->have_perm('autor')) {
            $this->error(401);
        }
        $GLOBALS['user']->cfg->store('BLUBBER_DEFAULT_THREAD', $thread_id);

        $thread = new \BlubberThread($thread_id);
        $thread = \BlubberThread::upgradeThread($thread);
        if (!$thread->isReadable()) {
            $this->error(401);
            return;
        }

        $json = $thread->getJSONData(50, null, \Request::get("search"));
        $thread->markAsRead();

        $this->etag(md5(serialize($json)));

        return $json;
    }

    /**
     * Get threads
     *
     * @get /blubber/threads
     * @return Array   the stream as array
     */
    public function getMyThreads()
    {
        $threads_data = [
            'threads'   => [],
            'more_down' => 0,
        ];
        $limit = \Request::int('limit', 50);

        $threads = \BlubberThread::findMyGlobalThreads(
            $limit + 1,
            null,
            \Request::int('timestamp'),
            null,
            \Request::get("search") ?: null
        );
        if (count($threads) > $limit) {
            array_pop($threads);
            $threads_data['more_down'] = 1;
        }
        foreach ($threads as $thread) {
            $threads_data['threads'][] = [
                'thread_id' => $thread->getId(),
                'avatar'    => $thread->getAvatar(),
                'name'      => $thread->getName(),
                'timestamp' => (int) $thread->getLatestActivity(),
            ];
        }
        return $threads_data;
    }

    /**
     * Write a comment to a thread
     *
     * @post /blubber/threads/:thread_id/comments
     * @param string $thread_id   id of the blubber thread
     * @return Array   the comment as array
     */
    public function postComment($thread_id)
    {
        if (!$GLOBALS['perm']->have_perm('autor')) {
            $this->error(401);
        }

        if (!trim($this->data['content'])) {
            $this->error(406);
            return false;
        }

        $thread = \BlubberThread::find($thread_id);
        if (!$thread->isCommentable()) {
            $this->error(401);
            return;
        }

        $comment = new \BlubberComment();
        $comment['thread_id'] = $thread_id;
        $comment['content'] = $this->data['content'];
        $comment['user_id'] = $GLOBALS['user']->id;
        $comment['external_contact'] = 0;
        $comment->store();

        $GLOBALS['user']->cfg->store("BLUBBERTHREAD_VISITED_".$thread_id, time());

        return $comment->getJSONData();
    }

    /**
     * Write a comment to a thread
     *
     * @put /blubber/threads/:thread_id/comments/:comment_id
     *
     * @param string $thread_id   id of the blubber thread
     * @param string $comment     id of the comment
     *
     * @return Array   the comment as array
     */
    public function editComment($thread_id, $comment_id)
    {
        $comment = \BlubberComment::find($comment_id);
        if (!$comment->isWritable()) {
            $this->error(401);
            return;
        }
        $old_content = $comment['content'];
        $comment['content'] = $this->data['content'];

        if ($comment['user_id'] !== $GLOBALS['user']->id) {
            $messaging = new \messaging();
            $message = sprintf(
                _("%s hat als Moderator gerade Ihren Beitrag in Blubber editiert.\n\nDie alte Version des Beitrags lautete:\n\n%s\n\nDie neue lautet:\n\n%s\n"),
                get_fullname(), $old_content, $comment['content']
            );

            $message .= "\n\n";

            $message .= '[' . _('Link zu diesem Beitrag') . ']';
            $message .= \URLHelper::getURL(
                "{$GLOBALS['ABSOLUTE_URI_STUDIP']}dispatch.php/blubber/index/{$comment->thread_id}",
                [],
                true
            );

            $messaging->insert_message(
                $message,
                get_username($comment['user_id']),
                $GLOBALS['user']->id,
                null, null, null, null,
                _("Änderungen an Ihrem Blubber.")
            );
        }

        if (!trim($this->data['content'])) {
            $data = $comment->getJSONData();
            $comment->delete();
        } else {
            $comment->store();
            $data = $comment->getJSONData();
        }
        return $data;
    }

    /**
     * Write a comment to a thread
     *
     * @get /blubber/threads/:thread_id/comments
     *
     * @param string $thread_id   id of the blubber thread
     *
     * @return Array   the comments as array
     */
    public function getComments($thread_id)
    {
        if (!$GLOBALS['perm']->have_perm('autor')) {
            $this->error(401);
        }

        $thread = new \BlubberThread($thread_id);
        if (!$thread->isReadable()) {
            $this->error(401);
            return;
        }

        $modifier = \Request::get('modifier');
        if ($modifier === 'olderthan') {
            $limit = \Request::int('limit', 50);

            $query = "SELECT blubber_comments.*
                      FROM blubber_comments
                      WHERE blubber_comments.thread_id = :thread_id
                        AND blubber_comments.mkdate <= :timestamp
                      ORDER BY mkdate DESC
                      LIMIT :limit";
            $result = \DBManager::get()->fetchAll($query, [
                'thread_id' => $thread_id,
                'timestamp' => \Request::int('timestamp', time()),
                'limit'     => $limit + 1,
            ]);

            $output = ['comments' => []];

            if (count($result) > $limit) {
                array_pop($result);
                $output['more_up'] = 1;
            } else {
                $output['more_up'] = 0;
            }
            foreach ($result as $data) {
                $comment = \BlubberComment::buildExisting($data);
                $output['comments'][] = $comment->getJSONData();
            }
            return $output;
        }

        if ($modifier === 'newerthan') {
            $limit = \Request::int('limit', 50);

            $query = "SELECT blubber_comments.*
                      FROM blubber_comments
                      WHERE blubber_comments.thread_id = :thread_id
                        AND blubber_comments.mkdate >= :timestamp
                      ORDER BY mkdate ASC
                      LIMIT :limit";
            $comments = \DBManager::get()->fetchAll($query, [
                'thread_id' => $thread_id,
                'timestamp' => \Request::int('timestamp', time()),
                'limit'     => $limit + 1,
            ], function ($comment) {
                return \BlubberComment::buildExisting($comment)->getJSONData();
            });

            $output = ['comments' => $comments];

            if (count($comments) > $limit) {
                array_pop($output['comments']);
                $output['more_down'] = 1;
            } else {
                $output['more_down'] = 0;
            }

            return $output;
        }

        $query = "SELECT blubber_comments.*
                  FROM blubber_comments
                  WHERE blubber_comments.thread_id = :thread_id ";
        $parameters = ['thread_id' => $thread_id];

        if (\Request::get('search')) {
            $query .= " AND blubber_comments.content LIKE :search ";
            $parameters['search'] = '%'.\Request::get('search').'%';
        }
        $query .= " ORDER BY mkdate ASC ";

        $output['comments'] = \DBManager::get()->fetchAll($query, $parameters, function ($comment) {
            return \BlubberComment::buildExisting($comment)->getJSONData();
        });
        $output['more_up'] = 0;
        $output['more_down'] = 0;

        return $output;
    }

    /**
     * Does the current user follow the thread?
     *
     * @get /blubber/threads/:thread_id/follow
     */
    public function threadIsFollowed($thread_id)
    {
        return $this->requireThread($thread_id)->isFollowedByUser();
    }

    /**
     * User follows a thread.
     *
     * @post /blubber/threads/:thread_id/follow
     *
     * @param string $thread_id   id of the blubber thread
     */
    public function followThread($thread_id)
    {
        $this->requireThread($thread_id)->addFollowingByUser();
    }

    /**
     * User unfollows a thread.
     *
     * @delete /blubber/threads/:thread_id/follow
     *
     * @param string $thread_id   id of the blubber thread
     */
    public function unfollowThread($thread_id)
    {
        $this->requireThread($thread_id)->removeFollowingByUser();
    }

    /**
     * Returns a blubber thread and checks permissions.
     *
     * @param string $thread_id Id of the blubber thread
     * @return \BlubberThread
     */
    private function requireThread($thread_id)
    {
        if (!$GLOBALS['perm']->have_perm('autor')) {
            $this->error(401);
        }

        $thread = new \BlubberThread($thread_id);
        if (!$thread->isReadable()) {
            $this->error(401);
        }

        return \BlubberThread::upgradeThread($thread);
    }
}
