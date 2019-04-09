<?php

# Lifter010: TODO
/**
 * vote.php - Votecontroller controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class BbController extends AuthenticatedController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
    }
    
    public function index_action($page = 1)
    {
        $this->entries_per_page = Request::int('entries_per_page', 20);

        $images = [];
        
        foreach (scandir($GLOBALS['DYNAMIC_CONTENT_PATH'] . '/user') as $file) {
            if (mb_strpos($file, '_normal.png') !== FALSE && $file !== 'nobody_normal.png') {
                $images[] = [
                'time'     => @filemtime($GLOBALS['DYNAMIC_CONTENT_PATH'] . '/user/'.$file),
                'file'     => $file,
                'user_id'  => mb_substr($file, 0, mb_strrpos($file, '_'))];
            }
        }
        
        usort($images, function($b, $a) {
            return $a['time'] - $b['time'];
        });
        
        $this->entries = sizeof($images);
        $this->page = $page;
        $this->images = array_slice($images, $this->entries_per_page * ($page - 1), $this->entries_per_page);
    }

}
