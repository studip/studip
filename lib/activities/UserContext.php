<?php

/**
 * @author      André Klaßen <klassen@elan-ev.de>
 * @author      Till Glöggler <gloeggler@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class UserContext extends Context
{
    private $user;

    /**
     * create new user-context
     *
     * @param string $user_id
     */
    public function __construct($user, $observer)
    {
        $this->user = $user;
        $this->observer = $observer;
    }

    /**
     * {@inheritdoc}
     */
    public function getRangeId()
    {
        return $this->user->id;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {

        if (!$this->provider) {
            $this->addProvider('Studip\Activity\NewsProvider');

            if ($this->user->id === $this->observer->id) {
                $this->addProvider('Studip\Activity\MessageProvider');
                $this->addProvider('Studip\Activity\BlubberProvider');
            }

            if (get_config('LITERATURE_ENABLE')) {
                $this->addProvider('Studip\Activity\LiteratureProvider');
            }

            $homepage_plugins = \PluginEngine::getPlugins('HomepagePlugin');
            foreach ($homepage_plugins as $plugin) {
                if ($plugin->isActivated($this->user->id, 'user')) {
                    if ($plugin instanceof \Studip\ActivityProvider) {
                        $this->provider[] = $plugin;
                    }
                }
            }
        }

        return $this->provider;
    }

    /**
    * {@inheritdoc}
    */
    public function getContextType()
    {
        return \Context::USER;
    }

    /**
    * {@inheritdoc}
    */
    public function getContextFullname($format = 'default')
    {
        return $this->user->getFullname($format);
    }
}
