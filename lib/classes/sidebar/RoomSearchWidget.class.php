<?php


/**
 * A special search widget that provides a room search.
 */
class RoomSearchWidget extends SidebarWidget
{
    protected $action_link;
    protected $criteria;
    protected $selected_criteria;
    protected $defined_properties;


    protected function setupSearchParameters()
    {
        $this->defined_properties = RoomManager::getAllRoomPropertyDefinitions(
            true,
            [
                'seats', 'room_type','room_category_id'
            ]
        );

        $resource_categories = ResourceCategory::findBySQL('`class_name` ="Room" ORDER by `name`');
        $categories = [
            '' => _('Alle Kategorien')
        ];
        if($resource_categories) {
            foreach($resource_categories as $resource_category) {
                $categories[$resource_category->id] = $resource_category->name;
            }
        }

        $room_types = Room::getAllRoomTypes();
        if ($room_types) {
            $filtered_room_types = [];
            foreach ($room_types as $type) {
                $filtered_room_types[$type] = $type;
            }
            $room_types = array_merge(
                ['' => _('Alle Raumtypen')],
                $filtered_room_types
            );
        } else {
            $room_types = [
                '' => _('Alle Raumtypen')
            ];
        }

        $locations = Location::findAll();
        $location_options = [
            [
                'id' => '',
                'name' => _('Alle Standorte und Gebäude'),
                'sub_options' => []
            ]
        ];
        if ($locations) {
            foreach ($locations as $location) {
                $buildings = Building::findByLocation($location->id);
                $sub_options = [];
                foreach ($buildings as $building) {
                    $sub_options[] = [
                        'id' => 'building_' . $building->id,
                        'name' => $building->name
                    ];
                }
                $location_options[] = [
                    'id' => 'location_' . $location->id,
                    'name' => $location->name,
                    'sub_options' => $sub_options
                ];
            }
        }

        $this->criteria = [];

        if ($this->defined_properties) {
            foreach ($this->defined_properties as $property) {
                $this->criteria[$property->name] = [
                    'name' => $property->name,
                    'title' => (
                        $property->display_name != ''
                        ? $property->display_name
                        : $property->name
                    ),
                    'type' => $property->type,
                    'range_search' => $property->range_search,
                    'optional' => true
                ];
            }
        }

        //Add special criteria:
        $this->criteria['special__room_name'] = [
            'name' => 'special__room_name',
            'title' => _('Raumname'),
            'type' => 'text',
            'range_search' => false,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];
        $this->criteria['room_category_id'] = [
            'name' => 'room_category_id',
            'title' => _('Kategorie'),
            'type' => 'select',
            'range_search' => false,
            'options' => $categories,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];
        $this->criteria['room_type'] = [
            'name' => 'room_type',
            'title' => _('Raumtyp'),
            'type' => 'select',
            'range_search' => false,
            'options' => $room_types,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];
        $this->criteria['special__building_location'] = [
            'name' => 'special__building_location',
            'title' => _('Standort / Gebäude'),
            'type' => 'hidden',
            'range_search' => false,
            //'options' => $location_options,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];

        if (Request::get('special__building_location') && !Request::submitted('room_search_reset')) {
            $res_id = explode('_', Request::get('special__building_location'));
            $selected_res =Resource::find($res_id[1]);
            if ($selected_res) {
                $this->criteria['special__building_location_label'] = [
                    'name' => 'special__building_location_label',
                    'title' => _('Standort / Gebäude'),
                    'type' => 'disabled_text',
                    'range_search' => false,
                    //'options' => $location_options,
                    'switch' => false,
                    'value' => $selected_res->name,
                    'optional' => false
                ];
            }
        }


        $begin = new DateTime();
        $begin = $begin->add(new DateInterval('P1D'));
        $begin->setTime(intval(date('H')), 0, 0);
        $end = clone $begin;
        $end = $end->add(new DateInterval('PT30M'));

        $current_semester = Semester::findCurrent();
        $all_semesters = Semester::getAll();

        $this->criteria['special__time_range'] = [
            'name' => 'special__time_range',
            'title' => _('Frei in einem Zeitbereich'),
            'optional' => false,
            'enabled' => false,
            'semester' => [
                'objects' => $all_semesters,
                'value' => $current_semester->id
            ],
            'range' => [
                'begin' => $begin,
                'end' => $end
            ],
            'day_of_week' => [
                'options' => [
                    '0' => _('Bitte wählen'),
                    '1' => _('Montag'),
                    '2' => _('Dienstag'),
                    '3' => _('Mittwoch'),
                    '4' => _('Donnerstag'),
                    '5' => _('Freitag'),
                    '6' => _('Samstag'),
                    '7' => _('Sonntag')
                ],
                'value' => ''
            ]
        ];

        $this->criteria['special__seats'] = [
            'name' => 'special__seats',
            'title' => _('Sitzplätze'),
            'type' => 'num',
            'range_search' => true,
            'switch' => true,
            'value' => '0',
            'optional' => false
        ];
    }


    protected function handleSearchRequest()
    {
        $this->selected_criteria = [];

        //If the reset button has been pressed, reset the search
        //and do nothing else.
        if ($this->searchResetRequested() || !$this->searchRequested()) {
            //If no room search is requested we can stop here.
            return;
        }

        $default_begin = new DateTime();
        $default_begin = $default_begin->add(new DateInterval('P1D'));
        $default_begin->setTime(intval(date('H')), 0, 0);
        $default_end = clone $default_begin;
        $default_end = $default_end->add(new DateInterval('PT30M'));

        foreach ($this->criteria as $name => $data) {
            if ($name == 'special__time_range') {
                if (Request::get($name . '_enabled')) {
                    $data['enabled'] = true;
                    $this->selected_criteria[$name] = $data;
                    if (Request::submitted($name . '_begin_date')
                        || Request::submitted($name . '_begin_time')
                        || Request::submitted($name . '_end_date')
                        || Request::submitted($name . '_end_time')) {
                            $submitted_begin = Request::getDateTime(
                                $name . '_begin_date',
                                'd.m.Y',
                                $name . '_begin_time',
                                'H:i'
                            );
                            $submitted_end = Request::getDateTime(
                                $name . '_end_date',
                                'd.m.Y',
                                $name . '_end_time',
                                'H:i'
                            );
                            if(!$submitted_begin || !$submitted_end) {
                                $submitted_begin = $default_begin;
                                $submitted_end = $default_end;
                            }
                        $this->selected_criteria[$name]['range'] = [
                            'begin' => $submitted_begin,
                            'end' => $submitted_end
                        ];
                    }
                    $this->selected_criteria[$name]['day_of_week']['value'] =
                        Request::get($name . '_day_of_week');
                    $this->selected_criteria[$name]['semester']['value'] =
                        Request::get($name . '_semester_id');
                }
            } else {
                if ($data['switch']) {
                    if (Request::get($name . '_enabled')) {
                        $data['enabled'] = true;
                    } else {
                        //The criteria isn't enabled. We can move on to the
                        //next criteria.
                        continue;
                    }
                }
                if ($data['type'] == 'date') {
                    if ($data['range_search']) {
                        if (Request::submitted($name . '_begin_date')
                            || Request::submitted($name . '_begin_time')
                            || Request::submitted($name . '_end_date')
                            || Request::submitted($name . '_end_time')) {
                            $this->selected_criteria[$name] = $data;
                            $submitted_begin = Request::getDateTime(
                                $name . '_begin_date',
                                'd.m.Y',
                                $name . '_begin_time',
                                'H:i'
                            );
                            $submitted_end = Request::getDateTime(
                                $name . '_end_date',
                                'd.m.Y',
                                $name . '_end_time',
                                'H:i'
                            );
                            if(!$submitted_begin || !$submitted_end) {
                                $submitted_begin = $default_begin;
                                $submitted_end = $default_end;
                            }
                            $this->selected_criteria[$name]['value'] = [
                                'begin' => $submitted_begin,
                                'end' => $submitted_end
                            ];
                        }
                    } else {
                        if (Request::submitted($name . '_date')
                            || Request::submitted($name . '_time')) {
                            $this->selected_criteria[$name] = $data;
                            $this->selected_criteria[$name]['value'] =
                                Request::getDateTime(
                                    $name . '_date',
                                    'd.m.Y',
                                    $name . '_time',
                                    'H:i'
                                );
                        }
                    }
                } elseif (($data['type'] == 'num') and $data['range_search']) {
                    if (Request::submitted($name . '_min')
                        || Request::submitted($name . '_max')) {
                        $this->selected_criteria[$name] = $data;
                        $this->selected_criteria[$name]['value'] = [
                            Request::get($name . '_min'),
                            Request::get($name . '_max')
                        ];
                    }
                } else {
                    if (Request::submitted($name)) {
                        $this->selected_criteria[$name] = $data;
                        $this->selected_criteria[$name]['value'] = Request::get($name);
                    }
                }
            }
        }

        $_SESSION['room_search_criteria']['room_search'] =
            $this->selected_criteria;
    }


    protected function restoreSearchFromSession()
    {
        if (is_array($_SESSION['room_search_criteria']['room_search'])) {
            $this->selected_criteria =
                $_SESSION['room_search_criteria']['room_search'];
        } else {
            $this->selected_criteria = [];
        }
    }


    protected function search()
    {
        //The properties array is a "simplified" version of the
        //$selected_criteria array, stripped from all special search criteria,
        //except the "seats" search criteria.

        $properties = [];
        if ($this->selected_criteria) {
            foreach ($this->selected_criteria as $name => $criteria) {

                //Do not add the special properties
                //into the $properties array:
                if (preg_match('/special__/', $name) && ($name != 'special__seats')) {
                    continue;
                }
                if ($name == 'room_type' && empty($criteria['value'])) {
                    continue;
                }
                if ($name == 'room_category_id' && empty($criteria['value'])) {
                    continue;
                }
                if ($name == 'special__seats') {
                    if ($criteria['value'][0] || $criteria['value'][1]) {
                        $properties['seats'] = $criteria['value'];
                    }
                    $name = 'seats';
                } else {
                    $properties[$name] = $criteria['value'];
                }

                if ($properties[$name][0] && $properties[$name][1] &&
                    ($properties[$name][0] > $properties[$name][1]) && $name != 'room_category_id') {
                    //A range is selected, but the range start is bigger
                    //then the range end. That's an error!

                    //Resolve the property name for a "beautiful" property name:
                    $property = ResourcePropertyDefinition::findOneBySql(
                        'name = :name',
                        ['name' => $name]
                    );
                    $property_name = $name;
                    if ($property) {
                        $property_name = $property->display_name;
                    }

                    PageLayout::postError(
                        sprintf(
                            _('Für die Eigenschaft %1$s wurde ein ungültiger Bereich angegeben (von %2$s bis %3$s)!'),
                            htmlReady($property_name),
                            htmlReady($properties[$name][0]),
                            htmlReady($properties[$name][1])
                        )
                    );
                    return;
                }
            }
        }

        $building_or_location_id = explode(
            '_',
            $this->selected_criteria['special__building_location']['value']
        );

        $this->location_id = null;
        $this->building_id = null;

        if ($building_or_location_id[0] == 'building') {
            $this->building_id = $building_or_location_id[1];
        } elseif ($building_or_location_id[0] == 'location') {
            $this->location_id = $building_or_location_id[1];
        } elseif($building_or_location_id[0] == 'resourcelabel') {
            $resourcelabel = ResourceLabel::find($building_or_location_id[1]);
            if ($resourcelabel) {
                $sub_buildings = [];
                foreach($resourcelabel->findChildrenByClassName('Building') as $sub_building) {
                    $sub_buildings[] = $sub_building->id;
                }
                $this->building_id = $sub_buildings;
            }
        } elseif($building_or_location_id[0] == 'room') {
            $this->rooms = [Room::find($building_or_location_id[1])];
            return;
        }

        //The time intervals have to be calculated by the selected time range
        //and the selected day of week.
        //The selected semester is represented by the selected time range
        //since its begin and end date are set on the client side in
        //the special__available_range property when a semester is selected.
        $time_intervals = [];
        if ($this->selected_criteria['special__time_range']) {
            $time_range_criteria = $this->selected_criteria['special__time_range'];

            //Get and check day of week:
            if ($time_range_criteria['day_of_week']['value']) {
                $selected_dow = $time_range_criteria['day_of_week']['value'];
                if (($selected_dow >= 1) && ($selected_dow <= 7)) {

                    //Get and check the time range:
                    if (($time_range_criteria['range']['begin'] instanceof DateTime)
                        && ($time_range_criteria['range']['end'] instanceof DateTime)) {
                        //Start from the begin date and make time intervals
                        //for the specified time on the specified day of week.
                        $begin = clone $time_range_criteria['range']['begin'];
                        $begin_dow = $begin->format('N');
                        if ($begin_dow < $selected_dow) {
                            $diff = $selected_dow - $begin_dow;
                            $begin = $begin->add(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        } elseif ($begin_dow > $selected_dow) {
                            $diff = $begin_dow - $selected_dow;
                            $begin = $begin->sub(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        }
                        $end = clone $time_range_criteria['range']['end'];
                        $current_begin = clone $begin;
                        do {
                            $current_end = clone $current_begin;
                            $current_end->setTime(
                                intval($end->format('H')),
                                intval($end->format('i')),
                                intval($end->format('s'))
                            );
                            $time_intervals[] = [
                                'begin' => clone $current_begin,
                                'end' => clone $current_end
                            ];
                            $current_begin = $current_begin->add(
                                new DateInterval('P1W')
                            );
                        } while ($current_begin < $end);
                    } else {
                        //Get the next occurrence of the specified day of week.
                        $begin = new DateTime();
                        $begin_dow = $begin->format('N');
                        if ($begin_dow < $selected_dow) {
                            $diff = $selected_dow - $begin_dow;
                            $begin = $begin->add(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        } elseif ($begin_dow > $selected_dow) {
                            $diff = $begin_dow - $selected_dow;
                            $begin = $begin->sub(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        }
                        $begin->setTime(0,0,0);
                        $end = clone $begin;
                        $end = $end->add(
                            new DateInterval('P1D')
                        )->sub(
                            new DateInterval('PT1S')
                        );

                        $time_intervals[] = [
                            'begin' => $begin,
                            'end' => $end
                        ];
                    }
                }
            } elseif ($time_range_criteria['range']) {
                //A time range without a day of week is specified.
                $time_intervals[] = $time_range_criteria['range'];
            }
        }

        $this->rooms = RoomManager::findRooms(
            $this->selected_criteria['special__room_name']['value'],
            $this->location_id,
            $this->building_id,
            $properties,
            $time_intervals,
            'name ASC, mkdate ASC',
            false
        );
    }


    public function resetSearch()
    {
        $this->selected_criteria = [];
        $_SESSION['room_search_criteria']['room_search'] = [];
    }


    public function __construct($action_link = '')
    {
        parent::__construct();

        $this->template = 'sidebar/room-search-widget';

        if ($action_link) {
            $this->action_link = $action_link;
        }

        $this->setupSearchParameters();
        if ($this->searchRequested()) {
            $this->handleSearchRequest();
        } elseif ($this->searchResetRequested()) {
            $this->resetSearch();
        } else {
            $this->restoreSearchFromSession();
        }

        if ($this->selected_criteria) {
            $this->search();
        }
    }


    public function searchRequested()
    {
        return Request::submitted('room_search');
    }


    public function searchResetRequested()
    {
        return Request::submitted('room_search_reset');
    }


    public function getResults()
    {
        return $this->rooms;
    }


    public function setActionLink($action_link = '')
    {
        if (!$action_link) {
            return;
        }

        $this->action_link = $action_link;
    }

    public function getActionLink()
    {
        return $this->action_link;
    }

    public function getSelectedCriteria()
    {
        return $this->selected_criteria;
    }

    public function render($variables = [])
    {
        $variables['title'] = _('Suchkriterien für Räume');

        $template = $GLOBALS['template_factory']->open(
            $this->template
        );

        $template->set_layout('widgets/widget-layout');

        $template->set_attributes($variables);

        $template->set_attribute(
            'criteria',
            $this->criteria
        );
        $template->set_attribute(
            'action_link',
            $this->action_link
        );

        $template->set_attribute('selected_criteria', $this->selected_criteria);

        return $template->render();
    }
}
