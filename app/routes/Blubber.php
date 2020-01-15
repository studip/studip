<?php
namespace RESTAPI\Routes;

/**
 * @license GPL 2 or later
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

        if ($thread_id !== 'global') {
            $thread = new \BlubberThread($thread_id);
            $thread = \BlubberThread::upgradeThread($thread);
            if (!$thread->isReadable()) {
                $this->error(401);
                return;
            }

            $json = $thread->getJSONData();
            $thread->markAsRead();

            $this->etag(md5(serialize($json)));

            return $json;
        }

        $this->stream_data = [
            'more_down' => 0,
            'postings' => [],
        ];
        $limit = \Request::int('limit', 30);
        if (\Request::get('modifier') === 'olderthan') {
            $global_threads = \BlubberThread::findBySQL("context_type = 'public' AND visible_in_stream = '1'  AND mkdate <= ? ORDER BY mkdate DESC LIMIT ".($limit + 1), [\Request::int("timestamp")]);
        } else {
            $global_threads = \BlubberThread::findBySQL("context_type = 'public' AND visible_in_stream = '1' ORDER BY mkdate DESC LIMIT ".($limit + 1));
        }
        if (count($global_threads) > $limit) {
            array_pop($global_threads);
            $this->stream_data['more_down'] = 1;
        }
        $GLOBALS['user']->cfg->store("BLUBBERTHREAD_VISITED_global", time());
        foreach ($global_threads as $thread) {
            if ($thread->isVisibleInStream() && $thread->isReadable()) {
                $data = $thread->toRawArray();
                $data['mkdate'] = (int) $data['mkdate'];
                $data['chdate'] = (int) $data['chdate'];
                $data['avatar'] = \Avatar::getAvatar($thread['user_id'])->getURL(\Avatar::MEDIUM);
                $data['html'] = $thread->getContentTemplate()->render();
                $data['user_name'] = $thread->user ? $thread->user->getFullName() : _('unbekannt');
                $this->stream_data['postings'][] = $data;
            }
        }
        return $this->stream_data;
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
            \Request::int('timestamp')
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
     * Write a global blubber
     *
     * @post /blubber/threads
     * @return Array   the blubber as array
     */
    public function postGlobalBlubber()
    {
        if (!$GLOBALS['perm']->have_perm('autor')) {
            $this->error(401);
        }

        if (!trim($this->data['content'])) {
            $this->error(406);
            return false;
        }

        $blubber = new \BlubberThread();
        $blubber['context_type'] = "public";
        $blubber['context_id'] = $GLOBALS['user']->id;
        $blubber['content'] = $this->data['content'];
        $blubber['user_id'] = $GLOBALS['user']->id;
        $blubber['external_contact'] = 0;
        $blubber['visible_in_stream'] = 1;
        $blubber['commentable'] = 1;
        $blubber->store();

        return $blubber->getJSONData();
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

        // sonderfall thread_id === "global"
        if ($thread_id === 'global') {
            $this->error(416);
            return false;
        }

        if (!trim($this->data['content'])) {
            $this->error(406);
            return false;
        }

        $thread = new \BlubberThread($thread_id);
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
        $comment['content'] = $this->data['content'];
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

        //sonderfall thread_id === "global"
        if ($thread_id === 'global') {
            $this->error(416);
            return false;
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
                $output['more_down'] = 1;
            } else {
                $output['more_down'] = 0;
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
                $output['more_up'] = 1;
            } else {
                $output['more_up'] = 0;
            }

            return $output;
        }

        $query = "SELECT blubber_comments.*
                  FROM blubber_comments
                  WHERE blubber_comments.thread_id = :thread_id
                  ORDER BY mkdate ASC";
        $comments = \DBManager::get()->fetchAll($query, [
            'thread_id' => $thread_id,
        ], function ($comment) {
            return \BlubberComment::buildExisting($comment)->getJSONData();
        });

        return compact('comments');
    }



}
