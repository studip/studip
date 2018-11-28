<?php
class ActionsWidget extends LinksWidget
{
    const INDEX = 'actions';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->title = _('Aktionen');
    }
}