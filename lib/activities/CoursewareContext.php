<?php

namespace Studip\Activity;

class CoursewareContext extends Context
{

    public function __construct($courseware, $observer)
    {
        $this->courseware = $courseware;
        $this->observer = $observer;

        $id = explode('_' , $this->courseware->id);
        $this->context = $id[0];
        $this->range_id = $id[1];
    }

    protected function getProvider()
    {
        $this->addProvider('Studip\Activity\CoursewareProvider');

        return $this->provider;
    }

    public function getRangeId()
    {
        return $this->range_id;
    }

    public function getContextType()
    {
        if($this->context == 'user') {
            return \Context::USER;
        }

        if ($this->content == 'course') {
            return \Context::COURSE;
        }
    }

    public function getContextFullname($format = 'default')
    {
        if($this->context == 'user') {
            $user = \User::find($this->range_id);

            return $user->getFullname($format);
        }

        if($this->context == 'course') {
            $course = \Course::find($this->range_id);

            return $course->getFullname($format);
        }
    }

}