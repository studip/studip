<?php
/**
 * RoomRequest.class.php - model class for table resources_requests
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Noack <noack@data-quest.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string request_id database column
 * @property string id alias column for request_id
 * @property string seminar_id database column
 * @property string termin_id database column
 * @property string metadate_id database column
 * @property string user_id database column
 * @property string last_modified_by database column
 * @property string resource_id database column
 * @property string category_id database column
 * @property string comment database column
 * @property string reply_comment database column
 * @property string reply_recipients database column
 * @property string closed database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class RoomRequest extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resources_requests';
        $config['belongs_to']['user'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['cycle'] = [
            'class_name'  => 'SeminarCycleDate',
            'foreign_key' => 'metadate_id'
        ];
        $config['belongs_to']['date'] = [
            'class_name'  => 'CourseDate',
            'foreign_key' => 'termin_id'
        ];
        $config['belongs_to']['resource'] = [
            'class_name' => 'ResourceObject',
            'foreign_key' => 'resource_id',
            'assoc_func' => 'Factory'
        ];
        $config['registered_callbacks']['after_initialize'][] = 'cbInitProperties';
        parent::configure($config);
    }

    private $properties = [];          //the assigned property-requests
    public $last_search_result_count;          //the number of found rooms from last executed search
    private $properties_changed = false;
    private $default_seats;

    public static function findByCourse($seminar_id)
    {
        return self::findOneBySql("termin_id = '' AND metadate_id = '' AND seminar_id = ?", [$seminar_id]);
    }

    public static function findByDate($termin_id)
    {
        return self::findOneBySql("termin_id = ?", [$termin_id]);
    }

    public static function findByCycle($metadate_id)
    {
        return self::findOneBySql("metadate_id = ?", [$metadate_id]);
    }

    public static function existsByCourse($seminar_id, $is_open = false)
    {
        $db = DbManager::get();
        $id = self::existsForSQL(($is_open ? "closed = 0 AND " : "") . "termin_id = '' AND metadate_id = '' AND seminar_id = " . $db->quote($seminar_id));
        return $id;
    }

    public static function existsByDate($termin_id, $is_open = false)
    {
        $db = DbManager::get();
        $id = self::existsForSQL(($is_open ? "closed = 0 AND " : "") . "termin_id = " . $db->quote($termin_id));
        return $id;
    }

    public static function existsByCycle($metadate_id, $is_open = false)
    {
        $db = DbManager::get();
        $id = self::existsForSQL(($is_open ? "closed = 0 AND " : "") . "metadate_id = " . $db->quote($metadate_id));
        return $id;
    }

    public static function existsForSQL($where)
    {
        $db = DBManager::get();
        $sql = "SELECT request_id FROM resources_requests WHERE " . $where;
        return $db->query($sql)->fetchColumn();
    }

    public function getResourceId()
    {
        return $this->content['resource_id'];
    }

    public function getSeminarId()
    {
        return $this->content['seminar_id'];
    }

    public function getTerminId()
    {
        return $this->content['termin_id'];
    }

    public function getMetadateId()
    {
        return $this->content['metadate_id'];
    }

    public function getUserId()
    {
        return $this->content['user_id'];
    }

    public function getCategoryId()
    {
        return $this->content['category_id'];
    }

    public function getComment()
    {
        return $this->content['comment'];
    }

    public function getReplyComment()
    {
        return $this->content['reply_comment'];
    }

    public function getClosed()
    {
        return $this->content['closed'];
    }

    public function getPropertyState($property_id)
    {
        return $this->properties[$property_id]["state"];
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getAvailableProperties()
    {
        $available_properties = [];
        if ($this->category_id) {
            $db = DBManager::get();

            $st = $db->prepare("SELECT b.property_id as id, b.*
                                FROM resources_categories_properties a
                                LEFT JOIN resources_properties b USING (property_id)
                                WHERE requestable = 1 AND category_id = ?");
            if ($st->execute([$this->category_id])) {
                $available_properties = array_map('array_shift', $st->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP));
            }
        }
        return $available_properties;
    }

    public function getSettedPropertiesCount()
    {
        $count = 0;
        foreach ($this->properties as $val) {
            if ($val) $count++;
        }
        return $count;
    }

    public function getSeats()
    {
        //The following statement makes the assumption that a
        //resource property with a system-value of 2 is a
        //seats property.
        //Furthermore it is based on the assumption that only
        //one such property exists for a room request.

        //Explaination:
        //Get the state from a resource request's property
        //where the property is requestable (defined in the
        //resource request's category) and where the property
        //has a system value of '2' (defined in the
        //property's definition). Furthermore the property
        //must belong to this room request.
        $available_properties = $this->getAvailableProperties();
        foreach ($this->properties as $key => $val) {
            if ($available_properties[$key]["system"] == 2) {
                return $val["state"];
            }
        }
        return false;
    }

    public function setResourceId($value)
    {
        $this->content['resource_id'] = $value;
    }

    public function setUserId($value)
    {
        $this->content['user_id'] = $value;
    }

    public function setSeminarId($value)
    {
        $this->content['seminar_id'] = $value;
    }

    public function setCategoryId($value)
    {
        $this->content['category_id'] = $value;
        if ($this->isFieldDirty('category_id')) {
            $this->inititalizeProperties();
        }
    }

    private function inititalizeProperties()
    {
        $this->properties = [];
        $this->properties_changed = true;
        if ($this->default_seats) {
            foreach ($this->getAvailableProperties() as $key=>$val) {
                if ($val["system"] == 2) {
                    $this->setPropertyState($key, $this->default_seats);
                }
            }
        }
    }

    public function setComment($value)
    {
        $this->content['comment'] = $value;
    }

    public function setReplyComment($value)
    {
        $this->content['reply_comment'] = $value;
    }

    /**
     * this function changes the state of the room-request
     *
     * possible states are:
     *  0 - room-request is open
     *  1 - room-request has been edited, but no confirmation has been sent
     *  2 - room-request has been edited and a confirmation has been sent
     *  3 - room-request has been declined
     *
     * @param integer $value one of the states
     */
    public function setClosed($value)
    {
        $this->content['closed'] = $value;
    }

    public function setTerminId($value)
    {
        $this->content['termin_id'] = $value;
    }

    public function setMetadateId($value)
    {
        $this->content['metadate_id'] = $value;
    }

    public function setPropertyState($property_id, $value)
    {
        if ($this->properties[$property_id]['state'] != $value) {
            $this->properties_changed = true;
        }
        if ($value) {
            $this->properties[$property_id] = ["state" => $value];
        } else {
            $this->properties[$property_id] = false;
        }
    }

    public function setDefaultSeats($value)
    {
        $this->default_seats = (int)$value;
    }

    public function searchRoomsToRequest($search_exp, $properties = false)
    {
        $permitted_rooms = null;
        if(getGlobalPerms($GLOBALS['user']->id) != 'admin' && !Config::GetInstance()->getValue('RESOURCES_ALLOW_ROOM_REQUESTS_ALL_ROOMS')) {
            $my_rooms = new ResourcesUserRoomsList($GLOBALS['user']->id, false, false, true);
            $global_resources = DBManager::get()
                                ->query("SELECT resource_id FROM resources_objects WHERE owner_id='global'")
                                ->fetchAll(PDO::FETCH_COLUMN);
            $permitted_rooms = array_unique(array_merge(array_keys($my_rooms->getRooms()), $global_resources));
        }
        return $this->searchRooms($search_exp, $properties, 0, 0, true, $permitted_rooms);
    }

    public function searchRooms($search_exp, $properties = FALSE, $limit_lower = 0, $limit_upper = 0, $only_rooms = TRUE, $permitted_resources = FALSE)
    {
        $search_exp = addslashes($search_exp);
        //create permitted resource clause
        if (is_array($permitted_resources)) {
            $permitted_resources_clause="AND a.resource_id IN ('".join("','",$permitted_resources)."')";
        }

        //create the query
        if ($search_exp && !$properties)
            $query = sprintf ("SELECT a.resource_id, a.name FROM resources_objects a %s WHERE a.name LIKE '%%%s%%' %s ORDER BY a.name", ($only_rooms) ? "INNER JOIN resources_categories b ON (a.category_id=b.category_id AND is_room = 1)" : "", $search_exp, $permitted_resources_clause);

        //create the very complex query for room search AND room propterties search...
        if ($properties) {
            $setted_properties = $this->getSettedPropertiesCount();
            $query = sprintf ("SELECT DISTINCT a.resource_id, b.name %s FROM resources_objects_properties a LEFT JOIN resources_objects b USING (resource_id) WHERE %s ", ($setted_properties) ? ", COUNT(a.resource_id) AS resource_id_count" : "", ($permitted_resources_clause) ? "1 ".$permitted_resources_clause." AND " : "");

            $i=0;
            if ($setted_properties) {
                $available_properties = $this->getAvailableProperties();
                foreach ($this->properties as $key => $val) {
                    if ($val) {
                        //let's create some possible wildcards
                        if (preg_match("/<=/", $val["state"])) {
                            $val["state"] = trim(mb_substr($val["state"], mb_strpos($val["state"], "<")+2, mb_strlen($val["state"])));
                            $linking = "<=";
                        } elseif (preg_match("/>=/", $val["state"])) {
                            $val["state"] = trim(mb_substr($val["state"], mb_strpos($val["state"], "<")+2, mb_strlen($val["state"])));
                            $linking = ">=";
                        } elseif (preg_match("/</", $val["state"])) {
                            $val["state"] = trim(mb_substr($val["state"], mb_strpos($val["state"], "<")+1, mb_strlen($val["state"])));
                            $linking = "<";
                        } elseif (preg_match("/>/", $val["state"])) {
                            $val["state"] = trim(mb_substr($val["state"], mb_strpos($val["state"], "<")+1, mb_strlen($val["state"])));
                            $linking = ">";
                        } elseif ($available_properties[$key]["system"] == "2") {
                            $linking = ">=";
                        } else $linking = "=";

                        $query.= sprintf(" %s (property_id = '%s' AND state %s %s%s%s) ", ($i) ? "OR" : "", $key, $linking,  (!is_numeric($val["state"])) ? "'" : "", $val["state"], (!is_numeric($val["state"])) ? "'" : "");
                        $i++;
                    }
                }
            }

            if ($search_exp)
                $query.= sprintf(" %s (b.name LIKE '%%%s%%' OR b.description LIKE '%%%s%%') ", ($setted_properties) ? "AND" : "", $search_exp, $search_exp);

            $query.= sprintf ("%s b.category_id ='%s' ", ($setted_properties) ? "AND" : "", $this->category_id);

            if ($setted_properties)
                $query.= sprintf (" GROUP BY a.resource_id  HAVING resource_id_count = '%s' ", $i);

            $query.= sprintf ("ORDER BY b.name %s", ($limit_upper) ? "LIMIT ".(($limit_lower) ? $limit_lower : 0).",".($limit_upper - $limit_lower) : "");
        }

        $db = DBManager::get();
        $result = $db->query( $query );

        $found = [];

        foreach( $result as $res ){
            if ($res["name"]) {
                $found [$res["resource_id"]] = $res["name"];
            }
        }

        $this->last_search_result_count = $result->rowCount();
        return $found;
    }

    public function cbInitProperties()
    {
        if ($this->getId()) {
            $db = DBManager::get();
            $st = $db->prepare("SELECT a.property_id, state, mkdate, chdate, type, name, options, system
                                FROM resources_requests_properties a
                                LEFT JOIN resources_properties b USING (property_id)
                                WHERE a.request_id=? ");
            if ($st->execute([$this->getId()])) {
                $this->properties = array_map('array_shift', $st->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP));
                $this->properties_changed = false;
            }
        } else {
            $this->inititalizeProperties();
        }
    }

    //private
    private function cleanProperties()
    {
        $db = DBManager::get();
        foreach ($this->properties as $key => $val) {
            if ($val)
                $properties[] = $key;
        }
        if (is_array($properties)) {
            $in="('".join("','",$properties)."')";
        }
        $query = sprintf("DELETE FROM resources_requests_properties WHERE %s request_id = '%s' ", (is_array($properties)) ? "property_id  NOT IN ".$in." AND " : "", $this->getId());
        $result = $db->exec( $query );
        return $result > 0 ;
    }

    //private
    private function storeProperties()
    {
        $db = DBManager::get();
        foreach ($this->properties as $key=>$val) {
            $query = sprintf ("REPLACE INTO resources_requests_properties SET request_id = '%s', property_id = '%s', state = '%s', mkdate = '%s', chdate = '%s'", $this->getId(), $key, $val["state"], (!$val["mkdate"]) ? time() : $val["mkdate"], time());

            if ($db->exec( $query ))
                $changed = TRUE;
        }
        if ($this->cleanProperties())
            $changed = TRUE;

        return $changed;
    }

    public function checkOpen($also_change = FALSE)
    {
        $db = DBManager::get();
        $existing_assign = false;
        //a request for a date is easy...
        if ($this->termin_id) {
            $query = sprintf ("SELECT assign_id FROM resources_assign WHERE assign_user_id = %s ", $db->quote($this->termin_id));
            $existing_assign = $db->query( $query )->fetchColumn();
        //metadate request
        } elseif ($this->metadate_id){
            $query = sprintf("SELECT count(termin_id)=count(assign_id) FROM termine LEFT JOIN resources_assign ON(termin_id=assign_user_id)
                    WHERE metadate_id=%s" , $db->quote($this->metadate_id));
        //seminar request
        } else {
            $query = sprintf("SELECT count(termin_id)=count(assign_id) FROM termine LEFT JOIN resources_assign ON(termin_id=assign_user_id)
                    WHERE range_id='%s' AND date_typ IN".getPresenceTypeClause(), $this->seminar_id);
            }
        if ($query) {
            $existing_assign = $db->query( $query )->fetchColumn();
        }

        if($existing_assign && $also_change){
            $this->setClosed(1);
            $this->store();
        }
        return (bool)$existing_assign;
    }


    public function copy()
    {
        $this->setId($this->getNewId());
        $this->setNew(true);
        $this->properties_changed = true;
    }

    public function store()
    {
        if (!$this->user_id) {
            $this->user_id = $GLOBALS['user']->id;
        }
        $this->closed = (int)$this->closed;
        if ($this->resource_id || $this->getSettedPropertiesCount()) {
            if (!$this->category_id && $this->resource) {
                $this->category_id = $this->resource->category_id;
            }
            if ($this->isNew() && !$this->getId()) {
                $this->setId($this->getNewId());
            }
            if ($this->properties_changed) {
                $properties_changed = $this->properties_changed;
                $properties_stored = $this->storeProperties();
            }
            if ($properties_stored || $this->isDirty()) {
                $this->last_modified_by = $GLOBALS['user']->id;
            }
            $is_new = $this->isNew();
            $stored = parent::store();
            // LOGGING
            $props="";
            foreach ($this->properties as $key => $val) {
                $props.=$val['name']."=".$val['state']." ";
            }
            if (!$props) {
                $props="--";
            }
            if ($is_new) {
                StudipLog::log("RES_REQUEST_NEW",$this->seminar_id,$this->resource_id,"Termin: $this->termin_id, Metadate: $this->metadate_id, Properties: $props, Kommentar: $this->comment",$query);
            } else {
                if($properties_changed && !$stored) {
                    $this->triggerChdate();
                }
                if ($stored) {
                    if ($this->closed==1 || $this->closed==2) {
                        StudipLog::log("RES_REQUEST_RESOLVE",$this->seminar_id,$this->resource_id,"Termin: {$this->termin_id}, Metadate: $this->metadate_id, Properties: $props, Status: ".$this->closed,$query);
                    } else if ($this->closed==3) {
                        StudipLog::log("RES_REQUEST_DENY",$this->seminar_id,$this->resource_id,"Termin: {$this->termin_id}, Metadate: $this->metadate_id, Properties: $props, Status: ".$this->closed,$query);
                    } else {
                        StudipLog::log("RES_REQUEST_UPDATE",$this->seminar_id,$this->resource_id,"Termin: {$this->termin_id}, Metadate: $this->metadate_id, Properties: $props, Status: ".$this->closed,$query);
                    }
                }
            }
        }
        return $stored || $properties_changed;
    }

    public function delete()
    {
        $db = DBManager::get();
        $query = "DELETE FROM resources_requests_properties WHERE request_id=". $db->quote($this->getId());
        $properties_deleted = $db->exec($query);
        // LOGGING
        StudipLog::log("RES_REQUEST_DEL",$this->seminar_id,$this->resource_id,"Termin: $this->termin_id, Metadate: $this->metadate_id","");
        return parent::delete() || $properties_deleted;
    }

    public function toArray($only_these_fields = NULL)
    {
        $ret = parent::toArray($only_these_fields);
        if ($only_these_fields === null || isset($only_these_fields['properties'])) {
        $ret['properties'] = $this->getProperties();
        }
        return $ret;
    }

    public function getType()
    {
        if ($this->termin_id) {
            return 'date';
        }
        if ($this->metadate_id) {
            return 'cycle';
        }
        if ($this->seminar_id) {
            return 'course';
        }
        return null;
    }

    public function getStatus()
    {
        switch ($this->getClosed()) {
            case '0';
                return 'open';
                break;
            case '1';
                return 'pending';
                break;
            case '2';
                return 'closed';
                break;
            case '3';
                return 'declined';
                break;
        }
    }

    public function getInfo()
    {
        if ($this->isNew()) {
            if (!($this->getSettedPropertiesCount() || $this->getResourceId())) {
                $requestData[] = _('Die Raumanfrage ist unvollständig, und kann so nicht dauerhaft gespeichert werden!');
            } else {
                $requestData[] = _('Die Raumanfrage ist neu.');
            }
            $requestData[] = '';
        } else {
            $requestData[] = _('Erstellt von') . ': ' . get_fullname($this->user_id);
            $requestData[] = _('Erstellt am') . ': ' . strftime('%x %H:%M', $this->mkdate);
            $requestData[] = _('Letzte Änderung') . ': ' . strftime('%x %H:%M', $this->chdate);
            $requestData[] = _('Letzte Änderung von') . ': ' . get_fullname($this->last_modified_by ?: $this->user_id);
        }
        if ($this->resource_id) {
            $resObject = ResourceObject::Factory($this->resource_id);
            $requestData[] = _('Raum') . ': ' . $resObject->getName();
            $requestData[] = _('verantwortlich') . ': ' . $resObject->getOwnerName();
        } else {
            $requestData[] = _('Es wurde kein spezifischer Raum gewünscht');
        }
        $requestData[] = '';

        foreach ($this->getAvailableProperties() as $val) {
            if ($this->getPropertyState($val['property_id']) !== null) {
                $state = $this->getPropertyState($val['property_id']);
                $prop = $val['name'].': ';
                if ($val['type'] == 'bool') {
                    if ($state == 'on') {
                        $prop .= _('vorhanden');
                    } else {
                        $prop .= _('nicht vorhanden');
                    }
                } else {
                    $prop .= $state;
                }
                $requestData[] = $prop;
            }
        }
        $requestData[] = '';

        $requestData[] = sprintf(_('Bearbeitung durch: %s'), $this->getStatusExplained());
        $requestData[] = '';

        // if the room-request has been declined, show the decline-notice placed by the room-administrator
        if ($this->getClosed() == 3) {
            $requestData[] = _('Nachricht Raumadminstration') . ':';
            $requestData[] = $this->getReplyComment();
        } else {
            $requestData[] = _('Nachricht an Raumadministration') . ':';
            $requestData[] = $this->getComment();
        }
        return join("\n", $requestData);
    }

    public function getTypeExplained()
    {
        $ret = '';
        if ($this->termin_id) {
            $ret = _("Einzeltermin der Veranstaltung");
            if (get_object_type($this->termin_id, ['date'])) {
                $termin = new SingleDate($this->termin_id);
                $ret .= chr(10) . '(' . $termin->toString() . ')';
            }
        } elseif ($this->metadate_id) {
            $ret = _("alle Termine einer regelmäßigen Zeit");
            if ($cycle = SeminarCycleDate::find($this->metadate_id)) {
                $ret .= chr(10) . ' (' . $cycle->toString('full') . ')';
            }
        } elseif ($this->seminar_id) {
            $ret =  _("alle regelmäßigen und unregelmäßigen Termine der Veranstaltung");
            if (get_object_type($this->seminar_id, ['sem'])) {
                $course = new Seminar($this->seminar_id);
                $ret .= chr(10) . ' (' . $course->getDatesExport(['short' => true, 'shrink' => true]) . ')';
            }
        } else {
            $ret = _("Kein Typ zugewiesen");
        }
        return $ret;
    }

    public function getStatusExplained()
    {
        if ($this->getClosed() == 0) {
            $txt = _("Die Anfrage wurde noch nicht bearbeitet.");
        } else if ($this->getClosed() == 3) {
            $txt = _("Die Anfrage wurde bearbeitet und abgelehnt.");
        } else {
            $txt = _("Die Anfrage wurde bearbeitet.");
        }
        return $txt;
    }

    public function getUserStatus($user_id)
    {
        $db = DBManager::get();
        $sql = "SELECT mkdate FROM resources_requests_user_status WHERE request_id=? AND user_id=?";
        $st = $db->prepare($sql);
        $st->execute([$this->request_id, $user_id]);
        return $st->fetchColumn();
    }

    public function setUserStatus($user_id, $status= true)
    {
        $db = DBManager::get();
        if ($status) {
            $sql = "REPLACE INTO resources_requests_user_status (request_id,user_id,mkdate) VALUES (?,?,UNIX_TIMESTAMP())";
        } else {
            $sql = "DELETE FROM resources_requests_user_status WHERE request_id=? AND user_id=?";
        }
        $st = $db->prepare($sql);
        $st->execute([$this->request_id, $user_id]);
        return $st->rowCount();
    }

    public function getAffectedDates()
    {
        $dates = [];
        switch ($this->getType()) {
            case 'date':
                $dates[] = $this->date;
                break;
            case 'cycle':
                $dates = $this->cycle->dates->getArrayCopy();
                break;
            case 'course':
                $dates = $this->course->dates->getArrayCopy();
                break;
        }
        return $dates;
    }
}

