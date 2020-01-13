<?php
namespace Studip;

class Fullcalendar
{
    protected $title;

    /**
     * Fullcalendar configuration options.
     * They are passed to the JavaScript fullcalendar class.
     */
    protected $config;

    /**
     * Additional HTML attributes that shall be attached to the
     * section element in which the fullcalendar instance is created.
     */
    protected $attributes;


    public static function create($title = '', $config = [], $attributes = [])
    {
        $instance = new self($title, $config, $attributes);

        return $instance->render();
    }

    public function __construct($title = '', $config = [], $attributes = [])
    {
        $this->title = $title;
        $this->config = $config;
        $this->attributes = $attributes;
    }

    public function render()
    {
        $attributes = $this->attributes;
        $attributes['data-title']  = $this->title;
        $attributes['data-config'] = json_encode($this->config);

        return $GLOBALS['template_factory']->render(
            'studip-fullcalendar.php',
            compact('attributes')
        );
    }

    /**
     * Creates an array with data for a Fullcalendar instance
     * from Stud.IP objects that implement the EventSource interface.
     */
    public static function createData($objects = [], $begin = null, $end = null)
    {
        $data = [];
        foreach ($objects as $object) {
            if ($object instanceof Calendar\EventSource) {
                $events = $object->getFilteredEventData(
                    $GLOBALS['user']->id, null, null, $begin, $end
                );

                foreach ($events as $event) {
                    $data[] = $event->toFullcalendarEvent();
                }
            }
        }
        return $data;
    }
}
