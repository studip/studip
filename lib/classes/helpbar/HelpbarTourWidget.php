<?php
class HelpbarTourWidget extends HelpbarWidget
{
    protected $template = 'helpbar/tour-widget.php';
    public $tour_data = array();

    public function __construct()
    {
        $this->tour_data = HelpTour::getHelpbarTourData();
        
        foreach ($this->tour_data['tours'] as $index => $tour) {
            $element = new LinkElement($tour->name, URLHelper::getURL('?tour_id=' . $tour->tour_id));

            $visit_state = HelpTourUser::find(array($tour->tour_id, $GLOBALS['user']->id));            
            if ($visit_state === null) {
                $element->addClass('tour-new');
            } elseif (!$visit_state->completed) {
                $element->addClass('tour-paused');
            } else {
                $element->addClass('tour-completed');
            }
            $element->addClass('tour_link');
            $element['id'] = $tour->tour_id;

            $this->addElement($element);
        }
    }
}