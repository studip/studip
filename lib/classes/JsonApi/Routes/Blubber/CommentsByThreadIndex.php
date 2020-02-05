<?php

namespace JsonApi\Routes\Blubber;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all comments of a certain blubber thread.
 */
class CommentsByThreadIndex extends JsonApiController
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

        if (!($thread = \BlubberThread::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlubberThread($this->getUser($request), $thread)) {
            throw new AuthorizationFailedException();
        }

        $filters = $this->getFilters();
        list($total, $comments) = $this->getComments($thread, $filters);

        return $this->getPaginatedContentResponse($comments, $total);
    }

    private function getComments(\BlubberThread $thread, array $filters)
    {
        list($offset, $limit) = $this->getOffsetAndLimit();

        $query = "SELECT comment_id
                  FROM blubber_comments
                  WHERE blubber_comments.thread_id = :thread_id";
        $params = ['thread_id' => $thread->id];

        if (isset($filters['before'])) {
            $query .= ' AND blubber_comments.mkdate <= :before';
            $params['before'] = $filters['before'];
        }

        if (isset($filters['since'])) {
            $query .= ' AND blubber_comments.mkdate >= :since';
            $params['since'] = $filters['since'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND blubber_comments.content LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $query .= ' ORDER BY mkdate ASC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit + 1;
        $params['offset'] = $offset;

        $comments = \BlubberComment::findMany(\DBManager::get()->fetchFirst($query, $params));
        return [count($comments) <= $limit ? count($comments) + $offset : null, array_slice($comments, 0, $limit)];
    }
}
