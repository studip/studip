<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class InstituteContext extends Context
{
    private $institute;

    /**
     * create new institute-context
     *
     * @param string $institute_id
     */
    public function __construct($institute, $observer)
    {
        $this->institute = $institute;
        $this->observer = $observer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $institute = $this->institute;

            $module_names = ['forum', 'documents', 'wiki', 'literature'];

            // get list of possible providers by checking the activated plugins and modules for the current institute
            $modules = new \Modules();
            $activated_modules = array_keys(array_filter($modules->getLocalModules($institute->institut_id, 'inst', $institute->modules, $institute->type ? : 1)));

            // check modules
            foreach (array_intersect($module_names, $activated_modules) as $name) {
                $this->addProvider('Studip\Activity\\'. ucfirst($name) .'Provider');
            }

            //news
            $this->addProvider('Studip\Activity\NewsProvider');

            // add blubber-provider
            $this->addProvider('Studip\Activity\BlubberProvider');

            $sem_class = \SemClass::getDefaultInstituteClass($institute->type ?: 1);

            //plugins
            $standard_plugins = \PluginManager::getInstance()->getPlugins("StandardPlugin", $institute->id);
            foreach ($standard_plugins as $plugin) {
                if (!$sem_class->isSlotModule(get_class($plugin))) {
                    if ($plugin instanceof \Studip\ActivityProvider) {
                        $this->provider[$plugin->getPluginName()] = $plugin;
                    }
                }
            }
        }

        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getRangeId()
    {
        return $this->institute->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextType()
    {
        return \Context::INSTITUTE;
    }

        /**
     * {@inheritdoc}
     */
    public function getContextFullname($format = 'default')
    {
        return $this->institute->getFullname($format);
    }
}
