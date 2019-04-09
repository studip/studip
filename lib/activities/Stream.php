<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class Stream implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private $activities;

    /**
     * creates a stream representing the activities for the passed contexts,
     * filter by time (if any)
     *
     * @param array $contexts All contexts that need to be considered
     * @param \Studip\Activity\Filter $filter
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($contexts, Filter $filter)
    {
        if (!is_array($contexts)) {
            $contexts = [$contexts];
        }

        foreach ($contexts as $context) {
            if (!$context instanceof Context) {
                throw new \InvalidArgumentException();
            }
        }

        if (!$filter instanceof Filter) {
            throw new \InvalidArgumentException();
        }

        //fetch avaible contextes in given timespan
        $available_contexts = \DBManager::get()->fetchGroupedPairs(
            "SELECT DISTINCT context,context_id FROM activities WHERE mkdate BETWEEN ? AND ?",
            [$filter->getStartDate(), $filter->getEndDate()]);

        //fetch activities only for contextes with known activities
        $activities = array_flatten(array_values(array_filter(array_map(
            function ($context) use ($filter, $available_contexts) {
                if (isset($available_contexts[$context->getContextType()])
                    && in_array($context->getRangeId(), $available_contexts[$context->getContextType()])) {
                        return $context->getActivities($filter);
                }
            }, $contexts))
        ));

        $new_activities = [];

        foreach ($activities as $activity) {
            // generate an id for the activity, considering some basic object parameters
            $id = md5($activity->provider . $activity->content .
                    $activity->verb . $activity->object_type . $activity->mkdate);

            if ($new_activities[$id]) {
                list($url, $name) = each($activity->object_url);
                $new_activities[$id]->addUrl($url, $name);
            } else {
                $new_activities[$id] = $activity;
            }
        }

        // sort activites by their mkdate
        usort($new_activities, function($a, $b) {
            return $b->mkdate - $a->mkdate;
        });

        $this->activities = $new_activities;
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return isset($this->activities[$offset]);
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this->activities[$offset];
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
        $this->activities[$offset] = $value;
    }

    /**
     * ArrayAccess: unset the value at the given offset (not applicable)
     */
    public function offsetUnset($offset)
    {
        unset($this->activities[$offset]);
    }

    /**
     * IteratorAggregate
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->activities);
    }

    /**
     * Countable
     */
    public function count()
    {
        return sizeof($this->activities);
    }

    /**
     * return representation of the current stream as an array
     *
     * @return array
     */
    public function toArray()
    {
        $activities = [];

        foreach ($this as $key => $activity) {
            $activities[$key] = $activity->toArray();

            // add i18n auto generated title prefix
            $title = '';

            // $class       = '\\Studip\\Activity\\' . ucfirst($activity->provider) . 'Provider';
            $class       = $activity->provider;
            $object_text = $class::getLexicalField();

            if (in_array($activity->actor_id, ['____%system%____', 'system']) !== false) {
                $actor = _('Stud.IP');
            } elseif ($activity->actor_type === 'anonymous') {
                $actor = _('Anonym');
            } else {
                $actor = get_fullname($activity->actor_id);
            }
            $context_name = $activity->getContextObject()->getContextFullname();

            switch ($activity->context) {
                case 'course':
                    $title = $actor .' '
                        . sprintf($activity->verbToText(),
                            $object_text . sprintf(_(' im Kurs "%s"'), $context_name)
                        );
                break;

                case 'institute':
                    $title = $actor .' '
                        . sprintf($activity->verbToText(),
                            $object_text . sprintf(_(' in der Einrichtung "%s"'), $context_name)
                        );
                break;

                case 'system':
                    $title = $actor .' '
                        . sprintf($activity->verbToText(), _('allen')) .' '
                        . $object_text;
                break;

                case 'user':
                    $title = $actor .' '
                        . sprintf($activity->verbToText(), $context_name) .' '
                        . $object_text;
                break;

            }

            $activities[$key]['title'] = $title;
        }

        return $activities;
    }
}
