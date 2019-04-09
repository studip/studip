<?php

require_once 'lib/classes/searchtypes/SQLSearch.class.php';

class MvvQuickSearch extends SQLSearch
{
    
    private $qs_name;
    
    public function __construct($query, $title = '', $avatarLike = '')
    {
        parent::__construct($query, $title, $avatarLike);
    }
    
    public function getResults($input, $contextual_data = [],
            $limit = PHP_INT_MAX, $offset = 0)
    {
        $qs_id = md5(serialize($this));
        $results = parent::getResults($input, $contextual_data, 100, $offset);
        $count = sizeof($results);
        if ($count > $limit) {
            $results = array_slice($results, 0, $limit);
            if ($count < 100) {
                $result_txt = sprintf(_('Alle %s Treffer anzeigen'), $count);
            } else {
                $result_txt = sprintf(_('Die ersten %s Treffer anzeigen'), $count);
            }
            if (!$this->zusatz) {
                $this->zusatz =
                        '<span class="mvv-qs-selected" data-qs_name="'
                        . $this->qs_name
                        . '" data-qs_id="'
                        . $qs_id
                        . '" style="font-weight:bold">'
                        . $result_txt
                        . '</span>';
            }
            $results[sizeof($results)] = ['', ''];
        }
        return $results;
    }
    
    public function getAvatarImageTag($id, $size = Avatar::SMALL, $options = []) 
    {
        if (!$id) {
            return $this->zusatz;
        }
        parent::getAvatarImageTag($id, $size = Avatar::SMALL, $options);
    }
    
    public function setQsName($qs_name)
    {
        $this->qs_name = $qs_name;
    }
    
}
