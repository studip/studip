<?php

namespace JsonApi\Routes;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use Studip\Activity\SystemContext;
use Studip\Activity\CourseContext;
use Studip\Activity\Filter;
use Studip\Activity\InstituteContext;
use Studip\Activity\Stream;
use Studip\Activity\UserContext;

function canShowActivityStream(\User $observer, $userId)
{
    if (!$GLOBALS['perm']->have_perm('root', $observer->id)) {
        return true;
    }

    return $observer->id == $userId;
}

class ActivityStreamShow extends JsonApiController
{
    protected $allowedIncludePaths = ['actor', 'context', 'object'];

    protected $allowedFilteringParameters = ['start', 'end', 'activity-type'];

    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!canShowActivityStream($this->getUser($request), $userId = $args['id'])) {
            throw new AuthorizationFailedException();
        }

        if (!$user = \User::find($userId)) {
            throw new RecordNotFoundException();
        }

        $urlFilter = $this->getUrlFilter($request);
        $contexts = $this->createContexts($user);
        $filter = $this->createFilter($urlFilter);

        try {
            if (!$stream = $this->createStream($contexts, $filter)) {
                $data = [];
                $total = 0;
            } else {
                list($offset, $limit) = $this->getOffsetAndLimit();
                $total = count($stream);
                $data = array_slice($stream->getIterator()->getArrayCopy(), $offset, $limit);
            }
        } catch (\Exception $exception) {
            $error = new \Neomerx\JsonApi\Document\Error(
                'internal-server-error',
                null,
                500,
                'internal-server-error',
                $exception->getMessage()
            );
            throw new \Neomerx\JsonApi\Exceptions\JsonApiException($error, 500);
        }

        $meta = ['filter' => $urlFilter];

        return $this->getPaginatedContentResponse($data, $total, 200, null, $meta);
    }

    private function getUrlFilter()
    {
        $params = $this->getQueryParameters();
        $filtering = $params->getFilteringParameters();

        $filter = [
            'start' => strtotime('-6 months'),
            'end' => time(),
            'activity-type' => null,
        ];

        $filter = array_reduce(
            words('start end'),
            function ($filter, $key) use ($filtering) {
                if (isset($filtering[$key])) {
                    $filter[$key] = (int) $filtering[$key];
                }

                return $filter;
            },
            $filter
        );

        if (isset($filtering['activity-type'])) {
            $filter['activity-type'] = $filtering['activity-type'];
        }

        return $filter;
    }

    private function createContexts(\User $user)
    {
        $contexts = [
            new SystemContext($user),
            new UserContext($user, $user),
        ];

        $user->contacts->each(function ($anotherUser) use (&$contexts, $user) {
            $contexts[] = new UserContext($anotherUser, $user);
        });

        if (!in_array($user->perms, ['admin', 'root'])) {
            // create courses and institutes context
            foreach (\Course::findMany($user->course_memberships->pluck('seminar_id')) as $course) {
                $contexts[] = new CourseContext($course, $user);
            }

            foreach (\Institute::findMany($user->institute_memberships->pluck('institut_id')) as $institute) {
                $contexts[] = new InstituteContext($institute, $user);
            }
        }

        return $contexts;
    }

    private function createFilter($urlFilter)
    {
        $filter = new Filter();

        if (!empty($urlFilter['activity-type'])) {
            $types = array_filter(
                explode(',', $urlFilter['activity-type']),
                function ($word) {
                    return in_array(
                        $word,
                        [
                            'activity',
                            // TODO: Polishing
                            // 'blubber',
                            'documents',
                            'forum',
                            'message',
                            'news',
                            'participants',
                            'schedule',
                            'wiki'
                        ]
                    );
                }
            );

            if (count($types)) {
                $filter->setType((object) array_fill_keys(['course', 'institute', 'system', 'user'], $types));
            }
        }

        $filter->setStartDate($urlFilter['start']);
        $filter->setEndDate($urlFilter['end']);

        return $filter;
    }

    private function createStream($contexts, $filter)
    {
        return new Stream($contexts, $filter);
    }
}
