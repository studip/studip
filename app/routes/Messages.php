<?php
namespace RESTAPI\Routes;

/**
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @condition message_id ^[a-f0-9]{32}$
 * @condition user_id ^[a-f0-9]{32}$
 * @condition box ^(inbox|outbox)$
 */
class Messages extends \RESTAPI\RouteMap
{
    /**
     * Liefert die Anzahl der vorhandenen Nachrichten des autorisierten Nutzers
     * zurück. Der Parameter bestimmt je nach Wert, auf welchen Bereich
     * (Posteingang bzw. Postausgang) zugegriffen werden soll.
     * Die Rückgabe beinhaltet jeweils die Anzahl aller Nachrichten sowie die
     * Anzahl der ungelesenen Nachrichten.
     *
     * @head /user/:user_id/:box
     */
    public function indexOfMessages($user_id, $box)
    {
        if ($user_id !== self::currentUser()) {
            $this->error(401);
        }

        return $this->countMessages($user_id, $box);
    }

    /**
     * Liefert die vorhandenen Nachrichten des autorisierten Nutzers zurück.
     *
     * @get /user/:user_id/:box
     */
    public function getMessages($user_id, $box)
    {
        if ($user_id !== self::currentUser()) {
            $this->error(401);
        }

        $ids   = $this->getMessageIds($user_id, $box);
        $total = count($ids);

        $ids = array_slice($ids, $this->offset, $this->limit);

        $messages = [];
        if (count($ids) > 0) {
            \Message::findEachMany(function ($message) use (&$messages) {
                $url = $this->urlf('/message/%s', $message->id);
                $messages[$url] = $this->messageToJSON($message);
            }, $ids, 'ORDER BY mkdate DESC');
        }

        return $this->paginated($messages, $total, compact('user_id', 'box'));
    }

    /**
     * Liefert die Daten der angegebenen Nachricht zurück.
     *
     * @get /message/:message_id
     */
    public function showMessage($message_id)
    {
        $message = $this->requireMessage($message_id);
        $message_json = $this->messageToJSON($message);
        $this->etag(md5(serialize($message_json)));
        return $message_json;
    }


    /**
     * Get the root file folder of a message. The root file folder contains all
     * files that were appended to the message.
     *
     * @get /message/:message_id/file_folder
     */
    public function getTopFolder($message_id)
    {
        //first we check if the user exists:
        $message = \Message::find($message_id);

        $user = \User::findCurrent();

        if (!$user) {
            $this->halt(404, 'User not found!');
        }

        if(!$message->permissionToRead($user->id)) {
            $this->halt(403, 'You are not allowed to read this message or its appended files!');
        }

        //we can get the top folder:
        $top_folder = \Folder::findTopFolder($message->id, 'message');

        if($top_folder) {
            $file_system_api = new FileSystem();
            return $file_system_api->getFolder($top_folder->id);
        } else {
            $this->halt(404, 'Folder not found!');
        }
    }


    /**
     * Schreibt eine neue Nachricht.
     *
     * @post /messages
     */
    public function createMessage()
    {
        if (!mb_strlen($subject = trim($this->data['subject'] ?: ''))) {
            $this->error(400, 'No subject provided');
        }

        if (!mb_strlen($message = trim($this->data['message'] ?: ''))) {
            $this->error(400, 'No message provided');
        }

        $recipients = (array) ($this->data['recipients'] ?: null);
        if (!sizeof($recipients)) {
            $this->error(400, 'No recipient(s) provided');
        }

        $usernames = array_map(function ($id) { $user = \User::find($id); return @$user['username']; }, $recipients);

        if (sizeof($usernames) !== sizeof(array_filter($usernames))) {
            $this->error(400, "Some recipients do not exist.");
        }

        $message = \Message::send($GLOBALS['user']->id, $usernames, $subject, $message);
        if (!$message) {
            $this->error(500, 'Could not create message');
        }

        $this->redirect('message/' . $message->id, 201, "ok");
    }


    /**
     * Eine Nachricht als (un)gelesen markieren.
     *
     * @put /message/:message_id
     */
    public function updateMessage($message_id)
    {

        $message = $this->requireMessage($message_id);
        $user_id = $this->currentUser();

        if (isset($this->data['unread'])) {
            if ($this->data['unread']) {
                $message->markAsUnread($user_id);
            } else {
                $message->markAsRead($user_id);
            }
        }

        $this->halt(204);
    }

    /**
     * Löscht eine Nachricht.
     *
     * @delete /message/:message_id
     */
    public function destroyMessage($message_id)
    {
        $message = $this->requireMessage($message_id);

        $msgin = new \messaging();
        if (!$msgin->delete_message($message_id, self::currentUser(), true)) {
            $this->error(500);
        }

        $this->status(204);
    }

    /**************************************************/
    /* PRIVATE HELPER METHODS                         */
    /**************************************************/

    private static function currentUser()
    {
        return $GLOBALS['user']->id;
    }

    private function requireMessage($message_id)
    {
        if (!$message = \Message::find($message_id)) {
            $this->notFound("Message not found");
        }

        $current_user = self::currentUser();
        $message_user = $message->originator->user_id === $current_user
                      ? $message->originator
                      : $message->receivers->findOneBy('user_id', $current_user);

        if (!$message_user) {
            $this->error(401);
        }

        if ($message_user->deleted) {
            $this->notFound("Message not found");
        }

        return $message;
    }

    private function messageToJSON($message)
    {
        $user_id = self::currentUser();

        $my_mu = $message->receivers->filter(function ($mu) use ($user_id) {
            return $mu->user_id === $user_id;
        });
        if ($message->originator->user_id === $user_id) {
            $my_mu[] = $message->originator;
        }

        $my_roles = [
            'snd' => $message->autor_id === $user_id,
            'rec' => in_array('rec', $my_mu->pluck('snd_rec')),
        ];

        $json = $message->toArray(words('message_id subject message mkdate priority'));

        // formatted message
        $json['message_html'] = formatReady($json['message']) ?: '';

        // Tags
        $json['tags'] = $message->getTags($user_id);

        // sender
        $sender = $message->getSender();
        $json['sender'] = $this->urlf('/user/%s', [$message->author->id]);

        // recipients
        if ($my_roles['snd']) {
            $json['recipients'] = [];
            foreach ($message->getRecipients() as $r) {
                $json['recipients'][] = $this->urlf('/user/%s', [$r->user_id]);
            }
        } else {
            $json['recipients'] = [$this->urlf('/user/%s', [$user_id])];
        }

        // attachments
        if ($message->attachment_folder && count($message->attachment_folder->file_refs) > 0) {
            $json['attachments'] = [];
            foreach ($message->attachment_folder->file_refs as $ref) {
                $json['attachments'][] = $this->urlf('/file/%s', [$ref->id]);
            }
        }

        // unread only if in inbox
        if ($my_roles['rec']) {
            foreach ($my_mu as $mu) {
                if ($mu->snd_rec === 'rec') {
                    $json['unread'] = !$mu->readed;
                    break;
                }
            }
        }

        return $json;
    }

    private function countMessages($user_id, $box)
    {
        $condition = 'user_id = ? AND snd_rec = ? AND deleted = 0';
        $params    = [$user_id, $box === 'inbox' ? 'rec' : 'snd'];

        $total  = \MessageUser::countBySQL($condition, $params);
        $unread = \MessageUser::countBySQL(
            $condition . ' AND readed = 0',
            $params
        );

        return compact('total', 'unread');
    }

    private function getMessageIds($user_id, $box)
    {
        return \MessageUser::findAndMapBySQL(function ($row) {
            return $row->message_id;
        }, 'user_id = ? AND snd_rec = ? AND deleted = 0 ORDER BY mkdate DESC', [
            $user_id, $box === 'inbox' ? 'rec' : 'snd'
        ]);
    }

}
