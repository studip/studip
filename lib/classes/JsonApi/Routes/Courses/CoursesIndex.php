<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;

class CoursesIndex extends JsonApiController
{
    protected $allowedFilteringParameters = ['q', 'fields', 'semester'];

    protected $allowedIncludePaths = [
        'blubber-threads',
        'end-semester',
        'events',
        'feedback-elements',
        'file-refs',
        'folders',
        'forum-categories',
        'institute',
        'memberships',
        'news',
        'participating-institutes',
        'sem-class',
        'sem-type',
        'start-semester',
        'status-groups',
        'wiki-pages',
    ];

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!Authority::canIndexCourses($user = $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        if ($error = $this->validateFilters()) {
            throw new BadRequestException($error);
        }

        $courseIds = $this->searchCourses($user, $this->getContextFilters());

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            \Course::findMany(array_slice($courseIds, $offset, $limit)),
            count($courseIds)
        );
    }

    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?: [];

        // keyword aka q
        if (isset($filtering['q']) && mb_strlen($filtering['q']) < 3) {
            return 'Search term too short.';
        }

        // fields
        if (isset($filtering['fields'])) {
            $validFields = ['all', 'title_lecturer_number', 'title', 'sub_title', 'lecturer', 'number', 'comment', 'scope'];
            if (!in_array($filtering['fields'], $validFields)) {
                return 'Filter "fields" has to be one of: '.join(', ', $validFields);
            }
        }

        // semester
        if (isset($filtering['semester'])) {
            $semester = \Semester::find($filtering['semester']);
            if (!$semester) {
                return 'Invalid "semester".';
            }
            $semNumber = \Semester::getIndexById($semester->id);
            if ($semNumber === false) {
                return 'Invalid "semester".';
            }
        }
    }

    private function getContextFilters()
    {
        $defaults = [
            'q' => '%%%',
            'fields' => 'all', // Titel, Lehrende...
            'semester' => 'all', // Semester
            'search_sem_category' => null, // SEM_CLASS
            'search_sem_scope_choose' => null, // Studienbereiche
            'search_sem_range_choose' => null, // Einrichtungen,
            'combination' => 'OR', // OR|AND
        ];

        $filtering = $this->getQueryParameters()->getFilteringParameters() ?: [];

        if (isset($filtering['semester'])) {
            $filtering['semester'] = \Semester::getIndexById($filtering['semester']);
        }

        return array_merge($defaults, $filtering);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function searchCourses(\User $user, array $filters)
    {
        require_once 'lib/classes/searchtypes/SearchType.class.php';
        require_once 'lib/classes/searchtypes/SeminarSearch.class.php';

        $visibleOnly = !(is_object($GLOBALS['perm'])
                         && $GLOBALS['perm']->have_perm(\Config::Get()->SEM_VISIBILITY_PERM, $user->id));
        $searchHelper = new \StudipSemSearchHelper();
        $searchHelper->setParams(
            [
                'quick_search' => $filters['q'],
                'qs_choose' => $filters['fields'],
                'sem' => $filters['semester'],
                'category' => $filters['search_sem_category'],
                'scope_choose' => $filters['search_sem_scope_choose'],
                'range_choose' => $filters['search_sem_range_choose'],
            ],
            $visibleOnly
        );
        $searchHelper->doSearch();

        return $searchHelper->getSearchResultAsArray();
    }
}
