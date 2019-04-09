<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class CourseContext extends Context
{
    private
        $course;

    /**
     * create new course-context
     *
     * @param string $seminar_id
     */
    function __construct($course, $observer)
    {
        $this->course = $course;
        $this->observer = $observer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProvider()
    {
        if (!$this->provider) {
            $course = $this->course;

            $module_names = ['forum', 'participants', 'documents', 'wiki', 'schedule', 'literature'];

            // get list of possible providers by checking the activated plugins
            // and modules for the current seminar
            $modules = new \Modules();
            $activated_modules = array_keys(array_filter($modules->getLocalModules($course->id, 'sem', $course->modules, $course->status ?: 1)));

            // check modules
            foreach (array_intersect($module_names, $activated_modules) as $name) {
                $this->addProvider('Studip\Activity\\'. ucfirst($name) .'Provider');
            }

            //news
            $this->addProvider('Studip\Activity\NewsProvider');

            // add blubber-provider
            $this->addProvider('Studip\Activity\BlubberProvider');

            //plugins
            $standard_plugins = \PluginManager::getInstance()->getPlugins("StandardPlugin", $course->id);
            foreach ($standard_plugins as $plugin) {
                if (!$course->getSemClass()->isSlotModule(get_class($plugin))) {
                    if ($plugin instanceof \Studip\Activity\ActivityProvider) {
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
        return $this->course->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextType()
    {
        return \Context::COURSE;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextFullname($format = 'default')
    {
        return $this->course->getFullname($format);
    }
}
