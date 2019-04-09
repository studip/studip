<?php
/**
 * log_event.php - Shared_LogEventController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */


require_once 'app/models/event_log.php';

class Shared_LogEventController extends MVVController
{
    
    public function show_action($object_type, $object_id)
    {
        $this->object_type = $object_type;
        $this->object_id = $object_id;
        
        // check permissions
        $this->have_log_perm($this->object_type, $this->object_id);
        
        $event_log = new EventLog();
                
        $this->start = (int) Request::int('start');
        $this->format = Request::quoted('format');
        $this->num_entries = 0;
        $this->log_events = [];
        
        $this->num_entries += $event_log->count_log_events('all', $this->object_id);  
        if ($this->num_entries) {
            $this->log_events = $event_log->get_log_events('all', $this->object_id, 0);
        }
        
        $this->object2_type = Request::option('object2_type');
        $this->object2_id = Request::option('object2_id');
        if ($this->object2_type) {
            // check permission
            $this->have_log_perm($this->object2_type, $this->object2_id);
            $this->num_entries += $event_log->count_log_events('all', $this->object2_id);
            $log_events = $event_log->get_log_events('all', $this->object2_id, 0);                
            if ($log_events) {    
                if (empty($this->log_events)){
                    $this->log_events = $log_events;                    
                } else {
                    $this->log_events = array_merge($this->log_events, $log_events);
                } 
            }
        } 
        
        //Merging the events in correct order
        $this->log_events = $this->order_events_by_date($this->log_events);
    }
    
    private function have_log_perm($object_type, $object_id)
    {
        if (is_subclass_of($object_type, 'ModuleManagementModel')) {
            $object = $object_type::find($object_id);
            if ($object && MvvPerm::get($object)->havePerm(MvvPerm::PERM_READ)) {
                return true;
            }
        }
        throw new AccessDeniedException();
    }
    
    private function order_events_by_date($event_array)
    {
        $ordered_events = [];
        $log_events = [];        
        foreach ($event_array as $levent) {
            $ordered_events[$levent['time']][] = $levent;
        }        
        foreach ($ordered_events as $ts => $events) {
            $log_events = array_merge($log_events, $events);
        }          
        return $log_events;
        
    }
    
    public function get_log_autor_action()
    {
        if (Request::isAjax()) {
            $mvv_id = Request::get('mvv_id',null);
            $mvv_coid = Request::get('mvv_coid',null);
            $mvv_field = Request::get('mvv_field',null);
            $log_action = Request::get('log_action',null);
            $mvv_debug = Request::get('mvv_debug',null);
            
            $parts = explode('.', $mvv_field);
            if($parts[0] == 'mvv_modul_deskriptor'){
                if ($modul = Modul::find($mvv_id)) {
                    $mvv_id = $modul->getDeskriptor()->getId();
                }
            }
            if($parts[0] == 'mvv_modulteil_deskriptor'){
                if ($modulteil = Modulteil::find($mvv_id)){
                    $mvv_id = $modulteil->getDeskriptor()->getId();
                }
            }
                        
            if ($mvv_id && $mvv_field || $mvv_debug && $mvv_field) {
                $search_action = "";
                
                if ($mvv_id){
                    $search_action .= " AND `affected_range_id` LIKE " . DBManager::get()->quote($mvv_id) ;
                }
                
                if ($mvv_coid){
                    $search_action .= " AND `coaffected_range_id` LIKE " . DBManager::get()->quote($mvv_coid) ;
                }
                
                if ($mvv_debug){
                    $search_action .= " AND `dbg_info` LIKE " . DBManager::get()->quote($mvv_debug) ;
                }
                
                if ($log_action == 'new') {
                    $search_action .= " AND ( `log_actions`.`name` LIKE '%_new' OR `log_actions`.`name` LIKE '%_update' )";
                } else {
                    $search_action .= " AND `log_actions`.`name` LIKE CONCAT('%_'," . DBManager::get()->quote($log_action) . ")";
                }
                
                $statement = DBManager::get()->prepare("SELECT *, `log_actions`.`name` 
                        FROM `log_events` 
                        LEFT JOIN `log_actions` ON `log_events`.`action_id` =  `log_actions`.`action_id` 
                        WHERE `info` = ? 
                        " . $search_action . " 
                        ORDER BY `mkdate` DESC");
                $statement->execute([$mvv_field]);
                $res = $statement->fetchOne();
                if ($res) {
                    //$user = get_fullname($res['user_id'], "full", true);
                    $user = get_username($res['user_id']);
                    $fields = explode('.',$mvv_field);
                    $field = count($fields) > 1 ? $fields[1] : $mvv_field;
                    echo json_encode(["user"=> $user ,"time"=> date('d.m.Y - H:i:s', $res['mkdate']), "field" => $field]);
                }
            }
            die();
        } else {
            $this->render_nothing();
        }
    }    

}
