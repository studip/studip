<?php

namespace JsonApi\Routes\Messages;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Liefert den Posteingang eines Nutzers zurück.
 */
abstract class BoxController extends JsonApiController
{
    protected $allowedIncludePaths = ['sender', 'recipients'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    abstract public function __invoke(Request $request, Response $response, $args);

    protected function getBoxResponse($request, $args, $sndrec, $onlyUnread = false)
    {
        if (!$otherUser = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!MessageAuthority::canShowMessagesOfUser($this->getUser($request), $otherUser)) {
            throw new AuthorizationFailedException();
        }

        $ids = self::folder($sndrec, $otherUser, $onlyUnread);
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice(self::load($ids, $otherUser), $offset, $limit),
            count($ids)
        );
    }

    private static function folder($sndrec, \User $user, $onlyUnread)
    {
        if ($onlyUnread) {
            $query = 'SELECT message_id
                      FROM message_user
                      WHERE snd_rec = ? AND user_id = ? AND deleted = 0 AND readed = 0
                      ORDER BY mkdate DESC';
        } else {
            $query = 'SELECT message_id
                      FROM message_user
                      WHERE snd_rec = ? AND user_id = ? AND deleted = 0
                      ORDER BY mkdate DESC';
        }

        $statement = \DBManager::get()->prepare($query);
        $statement->execute([$sndrec, $user->id]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    private static function load(array $ids, \User $user)
    {
        if (empty($ids)) {
            return [];
        }

        $query = 'SELECT DISTINCT m.*
                  FROM message AS m
                  WHERE m.message_id IN (:ids)
                  ORDER BY m.mkdate DESC';

        $statement = \DBManager::get()->prepare($query);
        $statement->execute(
            [
                ':ids' => $ids,
                ':user_id' => $user->id,
            ]
        );

        return array_map('Message::buildExisting', $statement->fetchAll(\PDO::FETCH_ASSOC));
    }
}
