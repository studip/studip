<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class Activity extends \SimpleORMap
{
    public
        $object_url,
        $object_route;

    private $context_object;

    const GC_MAX_DAYS = 366; // Garbage collector removes activities after 366 days

    private static $allowed_verbs = [
        'answered',
        'attempted',
        'attended',
        'completed',
        'created',
        'deleted',
        'edited',
        'experienced',
        'failed',
        'imported',
        'interacted',
        'passed',
        'shared',
        'sent',
        'voided'
    ];

    /**
     * {@inheritdoc}
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'activities';
        $config['additional_fields']['object_url'] = ['get' => 'getUrlList'];
        $config['additional_fields']['object_route'] = ['get' => 'getRoute'];

        parent::configure($config);
    }

    /**
     * return a string representation for this activity
     *
     * @return string
     */
    public function __toString()
    {
        return $this->content['content'];
    }

    /**
     * set one of the allowed verbs
     *
     * @param string $verb
     *
     * @throws \InvalidArgumentException
     */
    public function setVerb($verb)
    {
        if (is_null($verb)) {
            return;
        }

        if (in_array($verb, self::$allowed_verbs) === false) {
            throw new \InvalidArgumentException("That verb is not allowed.");
        }

        $this->content['verb'] = $verb;
    }

    /**
     * Add a url to the list of urls
     *
     * @param type $url
     * @param type $name
     */
    public function addUrl($url, $name)
    {
        $this->object_url[$url] = $name;
    }

    /**
     * Return assoc array of urls
     * [[url => description]]
     *
     * @return Array
     */
    public function getUrlList()
    {
        return $this->object_url ?: [];
    }

    /**
     * Return api route of the content object
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->object_route;
    }

    public function setContextObject(Context $context)
    {
        $this->context_object = $context;
    }

    public function getContextObject()
    {
        return $this->context_object;
    }

    /**
     * Returns a format string as placeholder for the object in question
     * (in a grammatical / lexical sense)
     *
     * @return string
     */
    public function verbToText()
    {
        $translation = [
            'answered'    => _('beantwortete %s'),
            'attempted'   => _('versuchte %s'),
            'attended'    => _('nahm teil an %s'),
            'completed'   => _('beendete %s'),
            'created'     => _('erstellte %s'),
            'deleted'     => _('löschte %s'),
            'edited'      => _('bearbeitete %s'),
            'experienced' => _('erlebte %s'),
            'failed'      => _('verfehlte %s'),
            'imported'    => _('importierte %s'),
            'interacted'  => _('interagierte mit %s'),
            'passed'      => _('bestand %s'),
            'shared'      => _('teilte %s'),
            'sent'        => _('sendete %s'),
            'voided'      => _('löschte %s')
        ];

        return ($translation[$this->verb]);
    }

    /**
     * Garbage collector for the activities.
     * Removes all activites older than GC_MAX_DAYS (default: 366).
     */
    public static function doGarbageCollect()
    {
        $stmt = \DBManager::get()->prepare('DELETE FROM activities WHERE mkdate < ?');

        $stmt->execute([
            time() - self::GC_MAX_DAYS * 24 * 60 * 60]
        );

        //Expire Cache
        \StudipCacheFactory::getCache()->expire('activity/oldest_activity');
    }

    /**
     * Returns the oldest existing activity
     *
     * @return Array
     */
    public static function getOldestActivity()
    {
        $cache = \StudipCacheFactory::getCache();
        $cache_key = 'activity/oldest_activity';

        if (!$activity = unserialize($cache->read($cache_key))) {
            $activity = self::findBySQL('1 ORDER BY mkdate ASC LIMIT 1');

            if (!empty($activity)) {
                $cache->write($cache_key, serialize($activity));
            } else {
                return false;
            }
        }

        return $activity;
    }


}
