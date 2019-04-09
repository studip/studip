<?php
namespace RESTAPI\Routes;

/**
 * @license GPL 2 or later
 *
 * @condition course_id ^[a-f0-9]{32}$
 * @condition stream_id ^(global|[a-f0-9]{32})$
 * @condition user_id ^[a-f0-9]{32}$
 * @condition blubber_id ^[a-f0-9]{32}$
 */
class Blubber extends \RESTAPI\RouteMap
{

    /**
     * Some inclusions before the routes can be prcessed
     */
    public static function before()
    {
        require_once 'public/plugins_packages/core/Blubber/models/BlubberPosting.class.php';
        require_once 'public/plugins_packages/core/Blubber/models/BlubberStream.class.php';
        require_once 'public/plugins_packages/core/Blubber/models/BlubberUser.class.php';
    }

    /**
     * List blubber in a course
     *
     * @get /course/:course_id/blubber
     *
     * @param string $course_id   id of the course
     *
     * @return Array   the blubber as array('collection' => array(...), 'pagination' => array())
     */
    public function getCourseBlubber($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm("autor", $course_id)) {
            $this->error(401);
        }
        $stream  = \BlubberStream::getCourseStream($course_id);
        return $this->getStreamBlubberRestResource($stream, compact("course_id"));
    }

    /**
     * Create a blubber in a course and redirects to the new blubber-route
     *
     * @post /course/:course_id/blubber
     * @param $course_id id of the course
     *
     * @param string : content   the content of the blubber
     */
    public function createCourseBlubber($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm("autor", $course_id)) {
            $this->error(401);
        }

        if (!mb_strlen(trim($this->data['content']))) {
            $this->error(400, 'No content provided');
        }

        $blubber = new \BlubberPosting();
        $blubber['user_id']          = $GLOBALS['user']->id;
        $blubber['external_contact'] = 0;
        $blubber['author_host']      = $_SERVER['REMOTE_ADDR'];
        $blubber['context_type']     = "course";
        $blubber['seminar_id']       = $course_id;
        $blubber->setId($blubber->getNewId());
        $blubber['root_id']          = $blubber->getId();
        $blubber['parent_id']        = 0;

        \BlubberPosting::$mention_posting_id = $blubber->getId();
        \StudipTransformFormat::addStudipMarkup("mention1", '@\"[^\n\"]*\"', "", "\BlubberPosting::mention");
        \StudipTransformFormat::addStudipMarkup("mention2", '@[^\s]*[\d\w_]+', "", "\BlubberPosting::mention");
        $content = \transformBeforeSave($this->data['content']);
        $blubber['name'] = $blubber['description'] = $content;

        $blubber->store();

        $this->redirect('blubber/posting/' . $blubber->getId(), 201, "ok");
    }


    /**
     * List blubber in a user's profile
     *
     * @get /user/:user_id/blubber
     *
     * @param string $user_id   id of the user
     *
     * @return Array   the blubber of a user as array('collection' => array(...), 'pagination' => array())
     */
    public function getProfileBlubber($user_id)
    {
        $stream  = \BlubberStream::getProfileStream($user_id);
        return $this->getStreamBlubberRestResource($stream, ["user_id" => $user_id]);
    }


    /**
     * Create a blubber in a user's profile and redirects to the new blubber-route
     *
     * @post /user/:user_id/blubber
     * @param string $user_id   id of the
     *
     * @param string content   the content of the blubber
     */
    public function createUserBlubber($user_id)
    {
        if ($user_id !== $GLOBALS['user']->id) {
            $this->error(401);
        }

        if (!mb_strlen(trim($this->data['content']))) {
            $this->error(400, 'No content provided');
        }

        $blubber = new \BlubberPosting();
        $blubber['user_id']          = $GLOBALS['user']->id;
        $blubber['external_contact'] = 0;
        $blubber['author_host']      = $_SERVER['REMOTE_ADDR'];
        $blubber['context_type']     = "public";
        $blubber['seminar_id']       = $GLOBALS['user']->id;
        $blubber->setId($blubber->getNewId());
        $blubber['root_id']          = $blubber->getId();
        $blubber['parent_id']        = 0;

        \BlubberPosting::$mention_posting_id = $blubber->getId();
        \StudipTransformFormat::addStudipMarkup("mention1", '@\"[^\n\"]*\"', "", "\BlubberPosting::mention");
        \StudipTransformFormat::addStudipMarkup("mention2", '@[^\s]*[\d\w_]+', "", "\BlubberPosting::mention");
        $content = \transformBeforeSave($this->data['content']);
        $blubber['name'] = $blubber['description'] = $content;

        $blubber->store();

        $this->redirect('blubber/posting/' . $blubber->getId(), 201, "ok");
    }

    /**
     * List blubber in a custom stream
     *
     * @get /blubber/stream/:stream_id
     *
     * @param string $stream_id   id of the stream or "global" if you want to access the global stream.
     *
     * @return array the collection as array('collection' => array(...), 'pagination' => array())
     */
    public function getCustomStreamBlubber($stream_id)
    {
        if ($stream_id === "global") {
            $stream = \BlubberStream::getGlobalStream();
        } else {
            $stream = new \BlubberStream($stream_id);
            if ($stream['user_id'] !== $GLOBALS['user']->id) {
                $this->error(401);
            }
        }
        return $this->getStreamBlubberRestResource($stream, ['user_id' => $stream['user_id'], 'stream_id' => $stream_id]);
    }

    /**
     * Returns the rest resource of the stream regarding a stream a context-id and the parameters
     * stream-time, limit and offset (all in $this).
     *
     * @param BlubberStream $stream : the stream
     * @param $parameter : an array of context-parameter i.e. array('user_id' => $user_id)
     *
     * @return Array('collection' => array(...), 'pagination' => array())
     */
    private function getStreamBlubberRestResource($stream, $parameter)
    {
        $total   = $stream->fetchNumberOfThreads();
        $threads = $stream->fetchThreads((int) $this->offset, (int) $this->limit ?: null, $this->stream_time ?: null);

        $json = [];

        foreach ($threads as $thread) {
            $url = $this->urlf('/blubber/posting/%s', [$thread->getId()]);
            $json[$url] = $this->blubberPostingtoJSON($thread);
        }

        $this->etag(md5(serialize($json)));

        return $this->paginated($json, $total, $parameter);
    }

    /**
     * Displays all data to a special blubber
     *
     * @get /blubber/posting/:blubber_id
     * @get /blubber/comment/:blubber_id
     *
     * @param string blubber_id   id of a blubber thread
     *
     * @return array   array of blubber data
     */
    public function getBlubberData($blubber_id)
    {
        $blubber = new \BlubberPosting($blubber_id);
        $this->requireReadAccessTo($blubber);

        if ($this->requestedRouteMatches('/posting/') xor $blubber->isThread()) {
            $this->notFound();
        }

        $json = $this->blubberPostingtoJSON($blubber);
        $this->etag(md5(serialize($json)));
        return $json;
    }

    /**
     * Create a new blubber. POST-Parameters are blubbercontent, context_type,
     * course_id, private_adressees.  Redirects to the new blubber afterwards.
     *
     * @post /blubber/postings
     *
     * @param string       content           : content of the blubber. Can have {@@}mentions if you want.
     * @param string       context_type      : "public", "private" or "course". If set to "course" you need to define the parameter course_id.
     * @param string|null  course_id         : id of the seminar, the blubber should be in. Leave away if context_type is not "course".
     * @param array|null   private_adressees : array of user_ids of people that should receive the private blubber. Remember that mentioned users will also see the private blubber, so it's your choice if the user should be mentioned or in this array. Leave blank if context_type is not "private".
     */
    public function createNewBlubber()
    {
        if (!mb_strlen(trim($this->data['content']))) {
            $this->error(400, 'No content provided');
        }

        $blubber = new \BlubberPosting();

        $blubber['user_id']          = $GLOBALS['user']->id;
        $blubber['external_contact'] = 0;
        $blubber['author_host']      = $_SERVER['REMOTE_ADDR'];

        switch ($this->data['context_type']) {

            case "course":
                if (!$this->data['course_id']) {
                    $this->error(400, 'No course_id provided');
                }

                if (!$GLOBALS['perm']->have_studip_perm("autor", $this->data['course_id'])) {
                    $this->error(401);
                }

                $blubber['context_type'] = 'course';
                $blubber['seminar_id']   = $this->data['course_id'];
                break;

        case "private":
                $blubber['context_type'] = "private";
                // TODO: relate users
                break;

        default:
        case "public":
                $blubber['context_type'] = "public";
                break;
        }

        $blubber->setId($blubber->getNewId());
        $blubber['root_id']   = $blubber->getId();
        $blubber['parent_id'] = 0;

        \BlubberPosting::$mention_posting_id = $blubber->getId();
        \StudipTransformFormat::addStudipMarkup("mention1", '@\"[^\n\"]*\"', "", "\BlubberPosting::mention");
        \StudipTransformFormat::addStudipMarkup("mention2", '@[^\s]*[\d\w_]+', "", "\BlubberPosting::mention");
        $content = \transformBeforeSave($this->data['content']);
        $blubber['name'] = $blubber['description'] = $content;

        $blubber->store();


        if ($blubber['context_type'] === "private") {

            $statement = \DBManager::get()->prepare(
                "INSERT IGNORE INTO blubber_mentions " .
                "SET user_id = :user_id, " .
                "topic_id = :thread_id, " .
                "mkdate = UNIX_TIMESTAMP() " .
            "");

            $statement->execute([
                'user_id' => $GLOBALS['user']->id,
                'thread_id' => $blubber->getId()
            ]);

            if (is_array($this->data['private_adressees'])) {
                foreach ($this->data['private_adressees'] as $user_id) {
                    $statement->execute([
                        'user_id' => $user_id,
                        'thread_id' => $blubber->getId()
                    ]);
                }
            }
        }

        $this->redirect('blubber/posting/' . $blubber->getId(), 201, "ok");
    }


    /**
     * Returns all comments of the blubber starting with the newest.
     * Returns an empty array if blubber_id is from a comment.
     *
     * @get /blubber/posting/:blubber_id/comments
     *
     * @param string $blubber_id  id of the thread
     *
     * @return array   an collection array('collection' => array(...), 'pagination' => array())
     */
    public function getComments($blubber_id)
    {
        $thread = new \BlubberPosting($blubber_id);

        $this->requireReadAccessTo($thread);

        \BlubberPosting::$course_hashes = $thread['context_type'] === "course" ? $thread['seminar_id'] : false;

        $comments = $thread->getChildren($this->offset, $this->limit);

        $json = [];

        foreach ($comments as $comment) {
            $url = $this->urlf('/blubber/comment/%s', [$comment->getId()]);
            $json[$url] = $this->blubberPostingtoJSON($comment);
        }

        $this->etag(md5(serialize($json)));

        return $this->paginated($json, $thread->getNumberOfChildren(), ['blubber_id' => $blubber_id]);
    }

    /**
     * Create a comment to a blubber
     *
     * @post /blubber/posting/:blubber_id/comments
     *
     * @param string $blubber_id   id of the blubber
     *
     * @param string blubbercontent   content of the comment.
     */
    public function createComment($blubber_id)
    {
        if (!mb_strlen(trim($this->data['content']))) {
            $this->error(400, 'No content provided');
        }

        $thread = new \BlubberPosting($blubber_id);

        $this->requireReadAccessTo($thread);

        $blubber = new \BlubberPosting();
        $blubber['root_id']          = $thread['root_id'];
        $blubber['parent_id']        = $thread->getId();
        $blubber['user_id']          = $GLOBALS['user']->id;
        $blubber['external_contact'] = 0;
        $blubber['author_host']      = $_SERVER['REMOTE_ADDR'];
        $blubber['context_type']     = $thread['context_type'];
        $blubber['seminar_id']       = $thread['seminar_id'];
        $blubber->setId($blubber->getNewId());

        \BlubberPosting::$mention_posting_id = $blubber->getId();
        \StudipTransformFormat::addStudipMarkup("mention1", '@\"[^\n\"]*\"', "", "\BlubberPosting::mention");
        \StudipTransformFormat::addStudipMarkup("mention2", '@[^\s]*[\d\w_]+', "", "\BlubberPosting::mention");
        $content = \transformBeforeSave($this->data['content']);
        $blubber['name'] = $blubber['description'] = $content;

        $blubber->store();

        $this->redirect('blubber/comment/' . $blubber->getId(), 201, "ok");
    }

    /**
     * Edits the content of a blubber. Sends a message of the change to the author, if the editing user is not
     * the author of the blubber, to inform him/her about the change.
     * If the content is empty the blubber is going to be deleted, because we don't want empty
     * blubber in the system.
     *
     * @put /blubber/posting/:blubber_id
     * @put /blubber/comment/:blubber_id
     *
     * @param string $blubber_id
     *
     * @param string content  new content for the blubber
     */
    public function editBlubberPosting($blubber_id)
    {
        if (!mb_strlen(trim($this->data['content']))) {
            $this->error(400, 'No content provided');
        }

        $blubber = new \BlubberPosting($blubber_id);
        $this->requireWriteAccessTo($blubber);

        if ($this->requestedRouteMatches('/posting/') xor $blubber->isThread()) {
            $this->notFound();
        }

        $old_content = $blubber['description'];

        \BlubberPosting::$mention_posting_id = $blubber->getId();
        \StudipTransformFormat::addStudipMarkup("mention1", '@\"[^\n\"]*\"', "", "\BlubberPosting::mention");
        \StudipTransformFormat::addStudipMarkup("mention2", '@[^\s]*[\d\w_]+', "", "\BlubberPosting::mention");
        $content = \transformBeforeSave($this->data['content']);
        $blubber['name'] = $blubber['description'] = $content;

        $blubber->store();

        if ($blubber['user_id'] !== $GLOBALS['user']->id) {
            $this->sendEditMail(
                $blubber,
                sprintf(
                    _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum editiert.\n\nDie alte Version des Beitrags lautete:\n\n%s\n\nDie neue lautet:\n\n%s\n"),
                    get_fullname(), $old_content, $blubber['description']
                ),
                _("Änderungen an Ihrem Posting.")
            );
        }

        $this->status(204);
    }

    /**
     * Deletes the blubber and informs the author of the blubber if
     * the current user is not the author of the blubber.
     *
     * @delete /blubber/posting/:blubber_id
     * @delete /blubber/comment/:blubber_id
     *
     * @param string $blubber_id
     */
    public function deleteBlubberPosting($blubber_id)
    {
        $blubber = new \BlubberPosting($blubber_id);
        $this->requireWriteAccessTo($blubber);

        if ($this->requestedRouteMatches('/posting/') xor $blubber->isThread()) {
            $this->notFound();
        }

        if ($blubber['user_id'] !== $GLOBALS['user']->id) {
            $this->sendEditMail(
                $blubber,
                sprintf(
                    _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum gelöscht.\n\nDer alte Beitrag lautete:\n\n%s\n"),
                    get_fullname(), $blubber['description']
                ),
                _("Ihr Posting wurde gelöscht.")
            );
        }

        $blubber->delete();
        $this->status(204);
    }

    /**
     * Returns all data of this blubber that are relevant as a rest-resource
     * including reshares and html-content
     * @param  $posting BlubberPosting  the posting to transform
     * @return array of JSON data of this blubber.
     */
    private function blubberPostingtoJSON($posting)
    {
        $result = [
            'blubber_id'   => $posting->getId(),
            'root_id'      => $posting['root_id'],
            'author'       => User::getMiniUser($this, $posting->getUser()),
            'context_type' => $posting['context_type'],
            'content'      => $posting['description'],
            'content_html' => formatReady($posting['description']),
            'mkdate'       => $posting['mkdate'],
            'chdate'       => $posting['chdate']
        ];

        if ($posting->isThread()) {

            $sharer_ids = [];
            foreach ($posting->getSharingUsers() as $sharer) {
                $sharer_ids[] = $this->urlf('/user/%s', [$sharer['user_id']]);
            }

            $result = array_merge($result, [
                'comments'       => $this->urlf('/blubber/posting/%s/comments', [$posting->getId()]),
                'comments_count' => $posting->getNumberOfChildren(),
                'reshares'       => $sharer_ids,
                'tags'           => $posting->getTags()
            ]);

            if($posting['context_type'] == 'course') {
                $result['course_id'] = $posting->seminar_id;
            }
        }

        return $result;
    }

    // check read perm or cancel request with an error
    private function requireReadAccessTo($posting)
    {
        if ($posting->isNew()) {
            $this->notFound();
        }

        switch ($posting['context_type']) {

        case 'course':
            if (!$GLOBALS['perm']->have_studip_perm('autor', $posting['seminar_id'])) {
                $this->error(401);
            }
            break;

        case 'private':
            if (!$posting->isRelated()) {
                $this->error(401);
            }
            break;
        }
    }

    // check write perm or cancel request with an error
    private function requireWriteAccessTo($posting)
    {
        if ($posting->isNew()) {
            $this->notFound();
        }

        if ($GLOBALS['user']->perms === 'root') {
            return true;
        }

        if (($posting['context_type'] === 'course' && !$GLOBALS['perm']->have_studip_perm('tutor', $posting['seminar_id']))
            || ($posting['user_id'] !== $GLOBALS['user']->id)
            || $posting['external_contact'])
        {
            $this->error(401);
        }
    }

    // send a mail to the original author of a blubber
    private function sendEditMail($blubber, $subject, $message)
    {
        $messaging = new \messaging();
        setTempLanguage($blubber['user_id']);
        $messaging->insert_message(
            $message,
            get_username($blubber['user_id']),
            $GLOBALS['user']->id,
            null, null, null, null,
            $subject
        );
        restoreLanguage();
    }

    // match the actual requested route against a pattern
    private function requestedRouteMatches($test)
    {
        return preg_match($test, $this->route['uri_template']->uri_template);
    }
}
