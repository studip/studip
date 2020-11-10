<?php

namespace JsonApi\Routes\Users;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsersIndex extends JsonApiController
{
    protected $allowedFilteringParameters = ['search'];
    protected $allowedIncludePaths = [
        UserSchema::REL_ACTIVITYSTREAM,
        UserSchema::REL_CONTACTS,
        UserSchema::REL_COURSES,
        UserSchema::REL_COURSE_MEMBERSHIPS,
        UserSchema::REL_EVENTS,
        UserSchema::REL_INSTITUTE_MEMBERSHIPS,
        UserSchema::REL_SCHEDULE,
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!Authority::canIndexUsers($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        $this->validateFilters();
        $filters = $this->getFilters();

        list($offset, $limit) = $this->getOffsetAndLimit();
        $partSQL = \GlobalSearchUsers::getSQL($filters['search'], [], $limit + $offset);
        $users = \User::findMany(array_map(function ($array) {  return $array['user_id']; }, \DBManager::get()->fetchAll($partSQL)));
        $total = (int) \DBManager::get()->fetchColumn('SELECT FOUND_ROWS() as found_rows');

        return $this->getPaginatedContentResponse($users, $total);
    }

    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        if (array_key_exists('search', $filtering)) {
            if (mb_strlen(trim($filtering['search'])) < 3) {
                throw new BadRequestException('Filter `search` should be at least 3 characters long.');
            }
        }
    }

    private function getFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        $filters['search'] = $filtering['search'] ?? '%%%';

        return $filters;
    }
}
