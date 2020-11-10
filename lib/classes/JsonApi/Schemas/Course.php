<?php

namespace JsonApi\Schemas;

use JsonApi\Routes\Files\Authority as FilesAuth;
use Neomerx\JsonApi\Document\Link;

class Course extends SchemaProvider
{
    const TYPE = 'courses';

    const REL_BLUBBER = 'blubber-threads';
    const REL_END_SEMESTER = 'end-semester';
    const REL_EVENTS = 'events';
    const REL_FEEDBACK = 'feedback-elements';
    const REL_FILES = 'file-refs';
    const REL_FOLDERS = 'folders';
    const REL_FORUM_CATEGORIES = 'forum-categories';
    const REL_INSTITUTE = 'institute';
    const REL_MEMBERSHIPS = 'memberships';
    const REL_NEWS = 'news';
    const REL_PARTICIPATING_INSTITUTES = 'participating-institutes';
    const REL_SEM_CLASS = 'sem-class';
    const REL_SEM_TYPE = 'sem-type';
    const REL_START_SEMESTER = 'start-semester';
    const REL_STATUS_GROUPS = 'status-groups';
    const REL_WIKI_PAGES = 'wiki-pages';

    protected $resourceType = self::TYPE;

    public function getId($course)
    {
        return $course->seminar_id;
    }

    public function getAttributes($course)
    {
        $stringOrNull = function ($item) {
            return trim($item) != '' ? (string) $item : null;
        };

        return [
            'course-number' => $stringOrNull($course->veranstaltungsnummer),

            'title' => (string) $course->name,
            'subtitle' => $stringOrNull($course->untertitel),
            'course-type' => (int) $course->status,
            'description' => $stringOrNull($course->beschreibung),
            'location' => $stringOrNull($course->ort),
            'miscellaneous' => $stringOrNull($course->sonstiges),

            // 'read-access' => (int) $course->lesezugriff,
            // 'write-access' => (int) $course->schreibzugriff,
        ];
    }

    public function getRelationships($course, $isPrimary, array $includeList)
    {
        $relationships = [];

        $relationships[self::REL_INSTITUTE] = $this->getInstitute($course, in_array(self::REL_INSTITUTE, $includeList));

        if ($semester = $this->getStartSemester($course)) {
            $relationships[self::REL_START_SEMESTER] = $semester;
        }
        if ($semester = $this->getEndSemester($course)) {
            $relationships[self::REL_END_SEMESTER] = $semester;
        }

        $relationships = $this->getParticipatingInstitutes($relationships, $course, $includeList);
        $relationships = $this->getFilesRelationship($relationships, $course);
        $relationships = $this->getForumCategoriesRelationship($relationships, $course, $includeList);
        $relationships = $this->getBlubberRelationship($relationships, $course, $includeList);
        $relationships = $this->getEventsRelationship($relationships, $course, $includeList);
        $relationships = $this->getFeedbackRelationship($relationships, $course, $includeList);
        $relationships = $this->getMembershipsRelationship($relationships, $course, $includeList);
        $relationships = $this->getNewsRelationship($relationships, $course, $includeList);
        $relationships = $this->getSemClassRelationship($relationships, $course, $includeList);
        $relationships = $this->getSemTypeRelationship($relationships, $course, $includeList);
        $relationships = $this->getStatusGroupsRelationship($relationships, $course, $includeList);
        $relationships = $this->getWikiPagesRelationship($relationships, $course, $includeList);

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getInstitute(\Course $course, $shouldInclude)
    {
        return [
            self::LINKS => [
                Link::RELATED => new Link('/institutes/'.$course->institut_id),
            ],
            self::DATA => $course->home_institut,
        ];
    }

    private function getStartSemester(\Course $course)
    {
        if (!$semester = \Semester::findByTimestamp($course->start_time)) {
            return null;
        }

        return [
            self::LINKS => [
                Link::RELATED => new Link('/semesters/'.$semester->id),
            ],
            self::DATA => $semester,
        ];
    }

    private function getEndSemester(\Course $course)
    {
        if (!$semester = \Semester::findByTimestamp($course->end_time)) {
            return null;
        }

        return [
            self::LINKS => [
                Link::RELATED => new Link('/semesters/'.$semester->id),
            ],
            self::DATA => $semester,
        ];
    }

    private function getFilesRelationship(array $relationships, \Course $resource)
    {
        $user = $this->getDiContainer()->get('studip-current-user');

        if ($user && FilesAuth::canShowFileArea($user, $resource)) {
            $filesLink = $this->getRelationshipRelatedLink($resource, self::REL_FILES);

            $relationships[self::REL_FILES] = [
                self::LINKS => [
                    Link::RELATED => $filesLink,
                ],
            ];

            $foldersLink = $this->getRelationshipRelatedLink($resource, self::REL_FOLDERS);
            $relationships[self::REL_FOLDERS] = [
                self::LINKS => [
                    Link::RELATED => $foldersLink,
                ],
            ];
        }

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getForumCategoriesRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_FORUM_CATEGORIES] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_FORUM_CATEGORIES)
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getBlubberRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_BLUBBER] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_BLUBBER),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getEventsRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_EVENTS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_EVENTS)
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getFeedbackRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {

        if (\Feedback::isActivated($course->id)) {
            $relationships[self::REL_FEEDBACK] = [
                self::LINKS => [
                    Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_FEEDBACK)
                ],
            ];
        }

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getMembershipsRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_MEMBERSHIPS] = [
            self::SHOW_SELF => true,
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_MEMBERSHIPS)
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getNewsRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_NEWS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_NEWS)
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getWikiPagesRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_WIKI_PAGES] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($course, self::REL_WIKI_PAGES)
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getParticipatingInstitutes(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $institutes = $course->institutes->filter(
            function ($institute) use ($course) {
                return $institute->id != $course->institut_id;
            }
        );

        $relationships[self::REL_PARTICIPATING_INSTITUTES] = [
            self::DATA => $institutes
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getSemClassRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_SEM_CLASS] = [
            self::DATA => $course->getSemClass()
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getSemTypeRelationship(
        array $relationships,
        \Course $course,
        $includeData
    ) {
        $relationships[self::REL_SEM_TYPE] = [
            self::DATA => $course->getSemType()
        ];

        return $relationships;
    }

    private function getStatusGroupsRelationship(
        array $relationships,
        \Course $resource,
        $includeData
    ) {
        $relation = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_STATUS_GROUPS),
            ]
        ];
        if (in_array(self::REL_STATUS_GROUPS, $includeData)) {
            $related = \Statusgruppen::findBySeminar_id($resource->id);
            $relation[self::DATA] = $related;
        }

        return array_merge($relationships, [self::REL_STATUS_GROUPS => $relation]);
    }
}
