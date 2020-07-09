<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;
use Studip\Activity\Activity as StudipActivity;

class Activity extends SchemaProvider
{
    const TYPE = 'activities';
    const REL_ACTOR = 'actor';
    const REL_CONTEXT = 'context';
    const REL_OBJECT = 'object';

    /**
     * Hier wird der Typ des Schemas festgelegt.
     * {@inheritdoc}
     */
    protected $resourceType = self::TYPE;

    /**
     * Diese Method entscheidet über die JSON-API-spezifische ID von
     * Activity-Objekten.
     * {@inheritdoc}
     */
    public function getId($activity)
    {
        return $activity->id;
    }

    /**
     * Hier können (ausgewählte) Instanzvariablen eines \Activity-Objekts
     * für die Ausgabe vorbereitet werden.
     * {@inheritdoc}
     */
    public function getAttributes($activity)
    {
        if (preg_match('/\\\\([^\\\\]+)Provider$/', $activity->provider, $matches)) {
            $activityType = strtolower($matches[1]);
        } else {
            $activityType = null;
        }

        $result = [
            'title' => $this->createTitle($activity),
            'mkdate' => date('c', $activity->mkdate),
            'content' => $activity->content,
            'verb' => $activity->verb,
            'activity-type' => $activityType,
        ];

        return $result;
    }

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden.
     * {@inheritdoc}
     */
    public function getRelationships($activity, $isPrimary, array $includeList)
    {
        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = [];

        $relationships = $this->getActorRelationship($relationships, $activity, $shouldInclude('actor'));
        $relationships = $this->getObjectRelationship($relationships, $activity, $shouldInclude('object'));
        $relationships = $this->getContextRelationship($relationships, $activity, $shouldInclude('context'));

        return $relationships;
    }

    private function getActorRelationship(array $relationships, StudipActivity $activity, $include)
    {
        $actorType = $activity->actor_type;
        $actorId = $activity->actor_id;

        if ($actorType === 'user') {
            $relationships[self::REL_ACTOR] = [
                self::LINKS => [Link::RELATED => new Link('/users/'.$actorId)],
                self::DATA => $include ? \User::findFull($actorId) : \User::build(['id' => $actorId], false),
            ];
        }

        return $relationships;
    }

    private function getObjectRelationship(array $relationships, StudipActivity $activity, $include)
    {
        $mapping = [
            // TODO: Polishing
            // 'blubber' => \BlubberPosting::class,
            'documents' => \FileRef::class,
            'forum' => \JsonApi\Models\ForumEntry::class,
            'message' => \Message::class,
            'news' => \StudipNews::class,
            'participants' => \Course::class,
            'schedule' => \Course::class,
            'wiki' => \WikiPage::class,
        ];


        if (isset($mapping[$activity->object_type])) {
            $objectMapping = $mapping[$activity->object_type];

            if ($activity->object_type === 'wiki') {
                $data = \WikiPage::findLatestPage($activity->context_id, $activity->object_id);
            } else {
                $data = $include
                      ? call_user_func([$objectMapping, 'find'], $activity->object_id)
                      : call_user_func([$objectMapping, 'build'], ['id' => $activity->object_id], false);
            }

            if ($data) {
                $link = $this->getSchemaContainer()->getSchema($data)->getSelfSubLink($data);
                $relationships[self::REL_OBJECT] = [
                    self::LINKS => [
                        Link::RELATED => $link
                    ],
                    self::DATA => $data,
                ];
            }
        } else {
            $relationships[self::REL_OBJECT] = [
                self::META => [
                    'object-type' => $activity->object_type,
                    'object-id' => $activity->object_id,
                ],
            ];
        }

        return $relationships;
    }

    private function getContextRelationship(array $relationships, StudipActivity $activity, $include)
    {
        if ($data = $this->getContext($activity, $include)) {
            $link = $this->getSchemaContainer()->getSchema($data)->getSelfSubLink($data);
            $relationships[self::REL_CONTEXT] = [
                self::LINKS => [
                    Link::RELATED => $link
                ],
                self::DATA => $data,
            ];
        }

        return $relationships;
    }

    private function getContext($activity, $include)
    {
        $mapping = [
            'course' => \Course::class,
            'institute' => \Institute::class,
            'user' => \User::class,
        ];

        if (!isset($mapping[$activity->context])) {
            return null;
        }

        $context = $mapping[$activity->context];
        return $include
            ? call_user_func([$context, 'find'], $activity->context_id)
            : call_user_func([$context, 'build'], ['id' => $activity->context_id], false);
    }

    private function createTitle($activity)
    {
        // add i18n auto generated title prefix
        $title = '';

        $class = $activity->provider;
        $objectText = $class::getLexicalField();

        if (in_array($activity->actor_id, array('____%system%____', 'system')) !== false) {
            $actor = _('Stud.IP');
        } else {
            $actor = get_fullname($activity->actor_id);
        }
        $contextName = $activity->getContextObject()->getContextFullname();

        switch ($activity->context) {
        case 'course':
            $title = $actor.' '
                   .sprintf($activity->verbToText(),
                             $objectText.sprintf(_(' im Kurs "%s"'), $contextName)
                   );
            break;

        case 'institute':
            $title = $actor.' '
                   .sprintf($activity->verbToText(),
                             $objectText.sprintf(_(' in der Einrichtung "%s"'), $contextName)
                   );
            break;

        case 'system':
            $title = $actor.' '
                   .sprintf($activity->verbToText(), _('allen')).' '
                   .$objectText;
            break;

        case 'user':
            $title = $actor.' '
                   .sprintf($activity->verbToText(), $contextName).' '
                   .$objectText;
            break;
        }

        return $title;
    }
}
