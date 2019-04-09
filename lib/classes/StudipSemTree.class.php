<?php
# Lifter007: TODO
# Lifter003: TODO

/**
 * class to handle the seminar tree
 *
 * This class provides an interface to the structure of the seminar tree
 *
 * @access    public
 * @author    André Noack <noack@data-quest.de>
 * @license   GPL2 or any later version
 * @copyright 2003 André Noack <noack@data-quest.de>,
 *                 Suchi & Berg GmbH <info@data-quest.de>
 */
class StudipSemTree extends TreeAbstract
{
    public $sem_dates = [];
    public $sem_number = null;
    public $visible_only = false;
    public $sem_status = [];

    /**
    * constructor
    *
    * do not use directly, call TreeAbstract::GetInstance("StudipRangeTree")
    * @access private
    */
    public function __construct($args)
    {
        DbView::addView('sem_tree');

        $this->root_name = Config::get()->UNI_NAME_CLEAN;
        if (isset($args['visible_only'])) {
            $this->visible_only = (int) $args['visible_only'];
        }
        if (isset($args['sem_number']) ){
            $this->sem_number = array_map('intval', $args['sem_number']);
        }
        if ($args['sem_status']){
            $this->sem_status = array_map('intval', $args['sem_status']);
        } else {
            foreach ($GLOBALS['SEM_CLASS'] as $key => $value){
                if ($value['bereiche']){
                    foreach ($GLOBALS['SEM_TYPE'] as $type_key => $type_value) {
                        if($type_value['class'] == $key)
                            $this->sem_status[] = $type_key;
                    }
                }
            }
        }

        if (!count($this->sem_status)){
            $this->sem_status[] = -1;
        }

        parent::__construct(); //calling the baseclass constructor
        if (isset($args['build_index']) ){
            $this->buildIndex();
        }

        $this->sem_dates = SemesterData::GetSemesterArray();
    }

    /**
    * initializes the tree
    *
    * stores all rows from table sem_tree in array $tree_data
    * @access public
    */
    public function init()
    {
        parent::init();

        $db = $this->view->get_query("view:SEM_TREE_GET_DATA_NO_ENTRIES");

        while ($db->next_record()){
            $this->tree_data[$db->f("sem_tree_id")] = ['type' => $db->f('type'), "info" => $db->f("info"),"studip_object_id" => $db->f("studip_object_id"), "entries" => 0];
            if ($db->f("studip_object_id")){
                $name = $db->f("studip_object_name");
            } else {
                $name = $db->f("name");
            }
            $this->storeItem($db->f("sem_tree_id"), $db->f("parent_id"), $name, $db->f("priority"));
        }
    }

    public function initEntries()
    {
        $this->view->params[0] = $this->sem_status;
        $this->view->params[1] = $this->visible_only ? "visible=1" : "1";
        $this->view->params[1] .= (isset($this->sem_number)) ? " AND ((" . $this->view->sem_number_sql
                                . ") IN (" . join(",",$this->sem_number) .") OR ((" . $this->view->sem_number_sql
                                .") <= " . $this->sem_number[count($this->sem_number)-1]
                                . "  AND ((" . $this->view->sem_number_end_sql . ") >= " . $this->sem_number[count($this->sem_number)-1]
                                . " OR (" . $this->view->sem_number_end_sql . ") = -1))) " : "";

        $db = $this->view->get_query("view:SEM_TREE_GET_ENTRIES");
        while ($db->next_record()){
            $this->tree_data[$db->f("sem_tree_id")]['entries'] = $db->f('entries');
        }
        $this->entries_init_done = true;
    }

    public function isModuleItem($item_id)
    {
        return isset($GLOBALS['SEM_TREE_TYPES'][$this->getValue($item_id, 'type')]['is_module']);
    }

    public function isHiddenItem($item_id)
    {
        return (bool) $GLOBALS['SEM_TREE_TYPES'][$this->getValue($item_id, 'type')]['hidden'];
    }

    public function getSemIds($item_id,$ids_from_kids = false)
    {
        if (!$this->tree_data[$item_id])
            return false;
        $this->view->params[0] = $this->sem_status;
        $this->view->params[1] = $this->visible_only ? "visible=1" : "1";
        if ($ids_from_kids && $item_id != 'root'){
            $this->view->params[2] = $this->getKidsKids($item_id);
        }
        $this->view->params[2][] = $item_id;
        $this->view->params[3] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end >= " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
        $ret = false;
        if ($item_id == 'root' && $ids_from_kids) {
            unset($this->view->params[2]);
            $this->view->params = array_values($this->view->params);
            $rs = $this->view->get_query("view:SEM_TREE_GET_SEMIDS_ROOT");
        } else {
            $rs = $this->view->get_query("view:SEM_TREE_GET_SEMIDS");
        }
        while($rs->next_record()){
            $ret[] = $rs->f(0);
        }
        return $ret;
    }

    public function getSemData($item_id,$sem_data_from_kids = false)
    {
        if (!$this->tree_data[$item_id])
            return false;
        $this->view->params[0] = $this->sem_status;
        $this->view->params[1] = $this->visible_only ? "visible=1" : "1";
        if ($sem_data_from_kids && $item_id != 'root'){
            $this->view->params[2] = $this->getKidsKids($item_id);
        }
        $this->view->params[2][] = $item_id;
        $this->view->params[3] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end >= " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
        if ($item_id == 'root' && $sem_data_from_kids) {
            unset($this->view->params[2]);
            $this->view->params = array_values($this->view->params);
            $rs = $this->view->get_query("view:SEM_TREE_GET_SEMDATA_ROOT");
        } else {
            $rs = $this->view->get_query("view:SEM_TREE_GET_SEMDATA");
        }
        return new DbSnapshot($rs);
    }

    public function getLonelySemData($item_id)
    {
        if (!$institut_id = $this->tree_data[$item_id]['studip_object_id'])
            return false;
        $this->view->params[0] = $this->sem_status;
        $this->view->params[1] = $this->visible_only ? "visible=1" : "1";
        $this->view->params[2] = $institut_id;
        $this->view->params[3] = (isset($this->sem_number)) ? " HAVING sem_number IN (" . join(",",$this->sem_number) .") OR (sem_number <= " . $this->sem_number[count($this->sem_number)-1] . "  AND (sem_number_end >= " . $this->sem_number[count($this->sem_number)-1] . " OR sem_number_end = -1)) " : "";
        return new DbSnapshot($this->view->get_query("view:SEM_TREE_GET_LONELY_SEM_DATA"));
    }

    public function getNumEntries($item_id, $num_entries_from_kids = false)
    {
        if (!$this->tree_data[$item_id]) {
            return false;
        }

        if (!$this->entries_init_done) {
            $this->initEntries();
        }

        return parent::getNumEntries($item_id, $num_entries_from_kids);
        /*
        if (!$num_entries_from_kids){
            return $this->tree_data[$item_id]["entries"];
        } else {
            $item_list = $this->getKidsKids($item_id);
            $item_list[] = $item_id;
            $ret = 0;
            $num_items = count($item_list);
            for ($i = 0; $i < $num_items; ++$i){
                $ret += $this->tree_data[$item_list[$i]]["entries"];
            }
            return $ret;
        }
        */
    }

    public function getAdminRange($item_id)
    {
        if (!$this->tree_data[$item_id])
            return false;
        if ($item_id == "root")
            return "root";
        $ret_id = $item_id;
        while (!$this->tree_data[$ret_id]['studip_object_id']){
            $ret_id = $this->tree_data[$ret_id]['parent_id'];
            if ($ret_id == "root")
                break;
        }
        return $ret_id;
    }

    public function InsertItem($item_id, $parent_id, $item_name, $item_info, $priority, $studip_object_id, $type)
    {
        $view = new DbView();
        $view->params = [$item_id,$parent_id,$item_name,$priority,$item_info,$studip_object_id, $type];
        $rs = $view->get_query("view:SEM_TREE_INS_ITEM");
        // Logging
        StudipLog::log("STUDYAREA_ADD",$item_id);
        NotificationCenter::postNotification("StudyAreaDidCreate", $item_id, $GLOBALS['user']->id);

        return $rs->affected_rows();
    }

    public function UpdateItem($item_id, $item_name, $item_info, $type)
    {
        $view = new DbView();
        $view->params = [$item_name,$item_info,$type,$item_id];
        $rs = $view->get_query("view:SEM_TREE_UPD_ITEM");
        NotificationCenter::postNotification("StudyAreaDidUpdate", $item_id, $GLOBALS['user']->id);

        return $rs->affected_rows();
    }

    public function DeleteItems($items_to_delete)
    {
        $view = new DbView();
        $view->params[0] = (is_array($items_to_delete)) ? $items_to_delete : [$items_to_delete];
        $view->auto_free_params = false;
        $rs = $view->get_query("view:SEM_TREE_DEL_ITEM");
        $deleted['items'] = $rs->affected_rows();
        $rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_RANGE");
        $deleted['entries'] = $rs->affected_rows();
        // Logging
        foreach ($items_to_delete as $item_id) {
            StudipLog::log("STUDYAREA_DELETE",$item_id);
            NotificationCenter::postNotification("StudyAreaDidDelete", $item_id, $GLOBALS['user']->id);
         }
        return $deleted;
    }

    public function DeleteSemEntries($item_ids = null, $sem_entries = null)
    {
        $view = new DbView();
        if ($item_ids && $sem_entries) {
            $sem_tree_ids = $view->params[0] = (is_array($item_ids)) ? $item_ids : [$item_ids];
            $seminar_ids = $view->params[1] = (is_array($sem_entries)) ? $sem_entries : [$sem_entries];
            $rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_SEM_RANGE");
            $ret = $rs->affected_rows();
            // Logging
            foreach ($sem_tree_ids as $range) {
                foreach ($seminar_ids as $sem) {
                    StudipLog::log("SEM_DELETE_STUDYAREA",$sem,$range);
                }
            }
            if($ret){
                foreach ($sem_tree_ids as $sem_tree_id){
                    $studyarea = StudipStudyArea::find($sem_tree_id);
                    if($studyarea->isModule()) {
                        foreach ($seminar_ids as $seminar_id) {
                            NotificationCenter::postNotification('CourseRemovedFromModule', $studyarea, ['module_id' => $sem_tree_id, 'course_id' => $seminar_id]);
                        }
                    }
                }
            }
        } elseif ($item_ids){
            $view->params[0] = (is_array($item_ids)) ? $item_ids : [$item_ids];
            // Logging
            foreach ($view->params[0] as $range) {
                StudipLog::log("SEM_DELETE_STUDYAREA","all",$range);
            }
            $rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_RANGE");
            $ret = $rs->affected_rows();
        } elseif ($sem_entries){
            $view->params[0] = (is_array($sem_entries)) ? $sem_entries : [$sem_entries];
            // Logging
            foreach ($view->params[0] as $sem) {
                StudipLog::log("SEM_DELETE_STUDYAREA",$sem,"all");
            }
            $rs = $view->get_query("view:SEMINAR_SEM_TREE_DEL_SEMID_RANGE");
            $ret = $rs->affected_rows();
        } else {
            $ret = false;
        }

        return $ret;
    }

    public function InsertSemEntry($sem_tree_id, $seminar_id)
    {
        $view = new DbView();
        $view->params[0] = $seminar_id;
        $view->params[1] = $sem_tree_id;
        $rs = $view->get_query("view:SEMINAR_SEM_TREE_INS_ITEM");
        if($ret = $rs->affected_rows()){
            // Logging
            StudipLog::log("SEM_ADD_STUDYAREA",$seminar_id,$sem_tree_id);
            $studyarea = StudipStudyArea::find($sem_tree_id);
            if($studyarea->isModule()){
                NotificationCenter::postNotification('CourseAddedToModule', $studyarea, ['module_id' => $sem_tree_id, 'course_id' => $seminar_id]);
            }
        }
        return $ret;
    }
}
