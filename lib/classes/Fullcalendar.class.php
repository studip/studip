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


    /**
     * The name of the fullcalendar for the data attribute. This is set
     * to "fullcalendar" by default, but custom fullcalendars may require
     * special data attributes to prevent the default Fullcalendar JS
     * initialiser to be executed.
     */
    protected $data_name;


    public static function create(
        $title = '',
        $config = [],
        $attributes = [],
        $data_name = 'fullcalendar'
    )
    {
        $instance = new \Studip\Fullcalendar(
            $title,
            $config,
            $attributes,
            $data_name
        );

        return $instance->render();
    }


    public function __construct(
        $title = '',
        $config = [],
        $attributes = [],
        $data_name = 'fullcalendar'
    )
    {
        $this->title = $title;
        $this->config = $config;
        $this->attributes = $attributes;
        $this->data_name = $data_name;
    }


    public function render()
    {
        $factory = new \Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/templates');
        $template = $factory->open('studip-fullcalendar.php');
        $real_data_name = sprintf('data-%s', $this->data_name);
        return $template->render(
            [
                'title' => $this->title,
                'config' => $this->config,
                'attributes' => array_merge(
                    $this->attributes,
                    [$real_data_name => '1']
                )
            ]
        );
    }


    /**
     * Creates an array with data for a Fullcalendar instance
     * from Stud.IP objects that implement the EventSource interface.
     */
    public static function createData($objects = [], $begin = null, $end = null)
    {
        if (!count($objects)) {
            //No data means there is nothing to do.
            return [];
        }

        $data = [];

        foreach ($objects as $object) {
            if ($object instanceof \Studip\Calendar\EventSource) {
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
