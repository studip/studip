<?php

namespace JsonApi\Routes\Blubber;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all comments visible to the user.
 */
class CommentsIndex extends JsonApiController
{
    use TimestampTrait, FilterTrait;

    protected $allowedFilteringParameters = ['since', 'before', 'search'];
    protected $allowedIncludePaths = ['author', 'mentions', 'thread'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $this->validateFilters();

        if (!Authority::canIndexComments($user = $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        $filters = $this->getFilters();
        list($total, $comments) = $this->getComments($user, $filters);

        return $this->getPaginatedContentResponse($comments, $total);
    }

    private function getComments(\User $user, array $filters)
    {
        list($offset, $limit) = $this->getOffsetAndLimit();

        $threadIds = array_map(function ($thread) {
            return $thread->id;
        }, \BlubberThread::findMyGlobalThreads(
            99999999,
            $filters['since'],
            $filters['before'],
            $user->id,
            $filters['search']
        ));

        $query = 'thread_id IN (:thread_ids)';
        $params = ['thread_ids' => $threadIds];

        if (isset($filters['before'])) {
            $query .= ' AND mkdate <= :before';
            $params['before'] = $filters['before'];
        }

        if (isset($filters['since'])) {
            $query .= ' AND mkdate >= :since';
            $params['since'] = $filters['since'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND content LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $query .= ' ORDER BY chdate ASC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit + 1;
        $params['offset'] = $offset;

        $comments = \BlubberComment::findBySQL($query, $params);
        return [count($comments) <= $limit ? count($comments) + $offset : null, array_slice($comments, 0, $limit)];
    }
}
