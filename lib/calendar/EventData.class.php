<?php


namespace Studip\Calendar;


class EventData
{
    public $begin;
    public $end;
    public $title;
    public $event_classes;
    public $text_colour;
    public $background_colour;
    public $editable;
    public $object_class;
    public $object_id;
    public $parent_object_class;
    public $parent_object_id;
    public $range_type;
    public $range_id;
    public $view_urls;
    public $api_urls;
    public $icon;

    public function __construct(
        \DateTime $begin,
        \DateTime $end,
        string $title,
        Array $event_classes,
        string $text_colour,
        string $background_colour,
        bool $editable,
        string $object_class,
        string $object_id,
        string $parent_object_class,
        string $parent_object_id,
        string $range_type,
        string $range_id,
        Array $view_urls = [],
        Array $api_urls = [],
        string $icon = ''
    )
    {
        $this->begin = $begin;
        $this->end = $end;
        $this->title = $title;
        $this->event_classes = $event_classes;
        $this->text_colour = $text_colour;
        $this->background_colour = $background_colour;
        $this->editable = $editable;
        $this->object_class = $object_class;
        $this->object_id = $object_id;
        $this->parent_object_class = $parent_object_class;
        $this->parent_object_id = $parent_object_id;
        $this->range_type = $range_type;
        $this->range_id = $range_id;
        $this->view_urls = $view_urls;
        $this->api_urls = $api_urls;
        $this->icon = $icon;
    }


    public function toFullcalendarEvent()
    {
        //Note: The timezone must not be transmitted or
        //the events may be shifted when there is a timezone
        //or daylight saving time difference between the server
        //and the client!
        return [
            'resourceId' => $this->range_id,
            'start' => $this->begin->format('Y-m-d\TH:i:s'),
            'end' => $this->end->format('Y-m-d\TH:i:s'),
            'title' => $this->title,
            'classNames' => $this->event_classes,
            'textColor' => $this->text_colour,
            'color' => $this->background_colour,
            'editable' => $this->editable,
            'studip_weekday_begin' => $this->begin->format('N'),
            'studip_weekday_end' => $this->end->format('N'),
            'studip_object_class' => $this->object_class,
            'studip_object_id' => $this->object_id,
            'studip_parent_object_class' => $this->parent_object_class,
            'studip_parent_object_id' => $this->parent_object_id,
            'studip_range_type' => $this->range_type,
            'studip_range_id' => $this->range_id,
            'studip_view_urls' => $this->view_urls,
            'studip_api_urls' => $this->api_urls,
            'icon' => $this->icon
        ];
    }
}
