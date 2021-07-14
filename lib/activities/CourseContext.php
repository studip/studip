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

            $module_provider = [
                'CoreForum' => 'ForumProvider',
                'CoreParticipants' => 'ParticipantsProvider',
                'CoreDocuments' => 'DocumentsProvider',
                'CoreWiki' => 'WikiProvider',
                'CoreSchedule' => 'ScheduleProvider'
            ];

            foreach ($course->tools as $tool) {
                $studip_module = $tool->getStudipModule();
                if($studip_module) {
                    if (isset($module_provider[get_class($studip_module)])) {
                        $this->addProvider('Studip\Activity\\'. $module_provider[get_class($studip_module)]);
                    } elseif ($studip_module instanceof ActivityProvider) {
                        $this->provider[$studip_module->getPluginName()] = $studip_module;
                    }
                }
            }
            //news
            $this->addProvider('Studip\Activity\NewsProvider');
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
