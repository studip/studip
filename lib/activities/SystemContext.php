<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class SystemContext extends Context
{
    /**
     * create new user-context
     *
     * @param string $user_id
     */
    public function __construct($observer)
    {
        $this->observer = $observer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        $this->addProvider('Studip\Activity\NewsProvider');
        $this->addProvider('Studip\Activity\BlubberProvider');

        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getRangeId()
    {
        return 'system';
    }

    /**
     * {@inheritdoc}
     */
    public function getContextType()
    {
        return 'system';
    }

    /**
     * {@inheritdoc}
     */
    public function getContextFullname($format = 'default')
    {
        return _('Stud.IP');
    }
}
