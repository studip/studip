<?php

namespace JsonApi\Routes\Blubber;

use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Displays all blubber threads of a certain context ID.
 */
class ThreadsIndex extends JsonApiController
{
    use TimestampTrait, FilterTrait;

    protected $allowedFilteringParameters = ['since', 'before', 'search'];
    protected $allowedIncludePaths = ['author', 'comments', 'context', 'mentions'];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $this->validateFilters();

        $contextType = $args['type'];
        if (!in_array($contextType, ['all', 'public', 'private', 'course', 'institute'])) {
            throw new BadRequestException('Wrong context type.');
        }

        switch ($contextType) {
            case 'all':
                list($threads, $total) = $this->getAllThreads($this->getUser($request));
                break;

            case 'public':
                list($threads, $total) = $this->getPublicThreads($this->getUser($request));
                break;

            case 'private':
                list($threads, $total) = $this->getPrivateThreads($this->getUser($request), $args['id']);
                break;

            case 'course':
                list($threads, $total) = $this->getCourseThreads($this->getUser($request), $args['id']);
                break;

            case 'institute':
                list($threads, $total) = $this->getInstituteThreads($this->getUser($request), $args['id']);
                break;
        }

        return $this->getPaginatedContentResponse($threads, $total);
    }

    private function getAllThreads(\User $observer)
    {
        $filters = $this->getFilters();
        list($offset, $limit) = $this->getOffsetAndLimit();

        $threads = \BlubberThread::findMyGlobalThreads(
            $offset + $limit + 1,
            $filters['since'],
            $filters['before'],
            $observer->id,
            $filters['search']
        );
        $hasMore = count($threads) < $offset + $limit + 1;
        $total = $hasMore ? count($threads) : null;

        return [array_slice($threads, $offset, $limit), $total];
    }

    private function getPublicThreads(\User $observer)
    {
        return $this->paginateThreads(
            $this->upgradeAndFilterThreads($observer, \BlubberThread::findBySQL('context_type = "public"'))
        );
    }

    private function getPrivateThreads(\User $observer, string $userID)
    {
        if (!($user = \User::find($userID))) {
            throw new RecordNotFoundException();
        }

        $query = 'SELECT a.thread_id FROM blubber_mentions a
                    JOIN blubber_mentions b
                      ON a.thread_id = b.thread_id
                   WHERE a.user_id = ? AND b.user_id = ?
                     AND a.external_contact = 0 AND b.external_contact = 0';
        $statement = \DBManager::get()->prepare($query);
        $statement->execute([$observer->id, $user->id]);
        $threadIDs = $statement->fetchAll(\PDO::FETCH_COLUMN, 0);
        $threads = $this->upgradeAndFilterThreads($observer, \BlubberThread::findMany($threadIDs, 'ORDER BY mkdate'));

        return $this->paginateThreads($threads);
    }

    private function getCourseThreads(\User $observer, string $courseID)
    {
        if (!($course = \Course::find($courseID))) {
            throw new RecordNotFoundException();
        }

        return $this->paginateThreads(\BlubberThread::findBySeminar($course->id, false, $observer->id));
    }

    private function getInstituteThreads(\User $observer, string $instituteID)
    {
        if (!($institute = \Institute::find($instituteID))) {
            throw new RecordNotFoundException();
        }

        return $this->paginateThreads(\BlubberThread::findByInstitut($institute->id, false, $observer->id));
    }

    private function upgradeAndFilterThreads(\User $user, array $threads)
    {
        return array_filter(
            array_map(function ($thread) {
                return \BlubberThread::upgradeThread($thread);
            }, $threads),
            function ($thread) use ($user) {
                return $thread->isVisibleInStream() && $thread->isReadable($user->id);
            }
        );
    }

    private function paginateThreads($threads)
    {
        list($offset, $limit) = $this->getOffsetAndLimit();
        $total = count($threads);
        $threads = array_slice($threads, $offset, $limit);

        return [$threads, $total];
    }
}
