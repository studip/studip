<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class User extends SchemaProvider
{
    const TYPE = 'users';

    const REL_ACTIVITYSTREAM = 'activitystream';
    const REL_BLUBBER = 'blubber-threads';
    const REL_CONFIG_VALUES = 'config-values';
    const REL_CONTACTS = 'contacts';
    const REL_COURSES = 'courses';
    const REL_COURSE_MEMBERSHIPS = 'course-memberships';
    const REL_EVENTS = 'events';
    const REL_FILES = 'file-refs';
    const REL_FOLDERS = 'folders';
    const REL_INBOX = 'inbox';
    const REL_INSTITUTE_MEMBERSHIPS = 'institute-memberships';
    const REL_NEWS = 'news';
    const REL_OUTBOX = 'outbox';
    const REL_SCHEDULE = 'schedule';

    /**
     * Hier wird der Typ des Schemas festgelegt.
     * {@inheritdoc}
     */
    protected $resourceType = self::TYPE;

    /**
     * Diese Method entscheidet über die JSON-API-spezifische ID von
     * \User-Objekten.
     * {@inheritdoc}
     */
    public function getId($user)
    {
        return $user->id;
    }

    /**
     * Hier können (ausgewählte) Instanzvariablen eines \User-Objekts
     * für die Ausgabe vorbereitet werden.
     * {@inheritdoc}
     */
    public function getAttributes($user)
    {
        $attrs = [
            'username' => $user->username,
            'formatted-name' => trim($user->getFullName()),
            'family-name' => $user->nachname,
            'given-name' => $user->vorname,
            'name-prefix' => $user->title_front,
            'name-suffix' => $user->title_rear,
            'permission' => $user->perms,
            'email' => get_visible_email($user->id),
        ];

        return $attrs + iterator_to_array($this->getProfileAttributes($user));
    }

    private function getProfileAttributes(\User $user)
    {
        $visibilities = $this->getVisibilities($user);
        $observer = $this->getDiContainer()->get('studip-current-user');

        $fields = [
            ['phone', 'privatnr', 'private_phone'],
            ['homepage', 'Home', 'homepage'],
            ['address', 'privadr', 'privadr'],
        ];

        foreach ($fields as list($attr, $field, $vis)) {
            $value = ($user[$field] && is_element_visible_for_user($observer->id, $user->id, $visibilities[$vis]))
                   ? strip_tags((string) $user[$field]) : null;
            yield $attr => $value;
        }
    }

    private function getVisibilities(\User $user)
    {
        $visibilities = get_local_visibility_by_id($user->id, 'homepage');
        if (is_array(json_decode($visibilities, true))) {
            return json_decode($visibilities, true);
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryMeta($resource)
    {
        $avatar = \Avatar::getAvatar($resource->id);

        return [
            'avatar' => [
                'small'    => $avatar->getURL(\Avatar::SMALL),
                'medium'   => $avatar->getURL(\Avatar::MEDIUM),
                'normal'   => $avatar->getURL(\Avatar::NORMAL),
                'original' => $avatar->getURL(\Avatar::ORIGINAL)
            ]
         ];
    }

    /**
     * @inheritdoc
     */
    public function getInclusionMeta($resource)
    {
        return $this->getPrimaryMeta($resource);
    }

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden. In diesem Beispiel kleben die Kontakte
     * eines Nutzers bei Bedarf am \User.
     * {@inheritdoc}
     */
    public function getRelationships($user, $isPrimary, array $includeList)
    {
        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = [];
        if ($isPrimary) {
            $relationships = $this->getActivityStreamRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_ACTIVITYSTREAM)
            );
            $relationships = $this->getBlubberRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_BLUBBER)
            );
            $relationships = $this->getConfigValuesRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_CONTACTS)
            );
            $relationships = $this->getContactsRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_CONTACTS)
            );
            $relationships = $this->getCoursesRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_COURSES)
            );
            $relationships = $this->getCourseMembershipsRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_COURSE_MEMBERSHIPS)
            );
            $relationships = $this->getEventsRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_EVENTS)
            );
            $relationships = $this->getFileRefsRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_FILES)
            );
            $relationships = $this->getFoldersRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_FOLDERS)
            );
            $relationships = $this->getInboxRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_INBOX)
            );
            $relationships = $this->getInstituteMembershipsRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_INSTITUTE_MEMBERSHIPS)
            );
            $relationships = $this->getNewsRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_NEWS)
            );
            $relationships = $this->getOutboxRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_OUTBOX)
            );
            $relationships = $this->getScheduleRelationship(
                $relationships,
                $user,
                $shouldInclude(self::REL_SCHEDULE)
            );
        }

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getActivityStreamRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_ACTIVITYSTREAM] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_ACTIVITYSTREAM),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getBlubberRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_BLUBBER] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_BLUBBER),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getConfigValuesRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_CONFIG_VALUES] = [
            self::SHOW_SELF => true,
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_CONFIG_VALUES),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getContactsRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_CONTACTS] = [
            self::SHOW_SELF => true,
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_CONTACTS),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getCoursesRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_COURSES] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_COURSES),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getCourseMembershipsRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_COURSE_MEMBERSHIPS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_COURSE_MEMBERSHIPS),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getFileRefsRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_FILES] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_FILES),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getFoldersRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_FOLDERS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_FOLDERS),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getInboxRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_INBOX] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_INBOX),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getInstituteMembershipsRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_INSTITUTE_MEMBERSHIPS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_INSTITUTE_MEMBERSHIPS),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getEventsRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_EVENTS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_EVENTS),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getNewsRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_NEWS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_NEWS),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getOutboxRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_OUTBOX] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_OUTBOX),
            ],
        ];

        return $relationships;
    }


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getScheduleRelationship(
        array $relationships,
        \User $user,
        $includeData
    ) {
        $relationships[self::REL_SCHEDULE] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($user, self::REL_SCHEDULE),
            ],
        ];

        return $relationships;
    }
}
