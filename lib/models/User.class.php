<?php
/**
 * User.class.php
 * model class for combined auth_user_md5/user_info record
 * this class represents one user, the attributes from tables
 * auth_user_md5 and user_info were merged.
 *
 * @code
 * $a_user = User::find($id);
 * $another_users_email = User::findByUsername($username)->email;
 * $a_user->email = $another_users_email;
 * $a_user->store();
 * @endcode
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2011 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string user_id database column
 * @property string id alias column for user_id
 * @property string username database column
 * @property string password database column
 * @property string perms database column
 * @property string vorname database column
 * @property string nachname database column
 * @property string email database column
 * @property string validation_key database column
 * @property string auth_plugin database column
 * @property string locked database column
 * @property string lock_comment database column
 * @property string locked_by database column
 * @property string visible database column
 * @property string hobby computed column read/write
 * @property string lebenslauf computed column read/write
 * @property string publi computed column read/write
 * @property string schwerp computed column read/write
 * @property string home computed column read/write
 * @property string privatnr computed column read/write
 * @property string privatcell computed column read/write
 * @property string privadr computed column read/write
 * @property string score computed column read/write
 * @property string geschlecht computed column read/write
 * @property string mkdate computed column read/write
 * @property string chdate computed column read/write
 * @property string title_front computed column read/write
 * @property string title_rear computed column read/write
 * @property string preferred_language computed column read/write
 * @property string smsforward_copy computed column read/write
 * @property string smsforward_rec computed column read/write
 * @property string guestbook computed column read/write
 * @property string email_forward computed column read/write
 * @property string smiley_favorite computed column read/write
 * @property string motto computed column read/write
 * @property string lock_rule computed column read/write
 * @property SimpleORMapCollection course_memberships has_many CourseMember
 * @property SimpleORMapCollection institute_memberships has_many InstituteMember
 * @property SimpleORMapCollection admission_applications has_many AdmissionApplication
 * @property SimpleORMapCollection archived_course_memberships has_many ArchivedCourseMember
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property SimpleORMapCollection studycourses has_many UserStudyCourse
 * @property SimpleORMapCollection contacts has_many Contact
 * @property UserInfo   info   has_one UserInfo
 * @property UserOnline online has_one UserOnline
 */
class User extends AuthUserMd5 implements Range, PrivacyObject
{
    /**
     *
     */
    protected static function configure($config = [])
    {
        $config['has_many']['course_memberships'] = [
            'class_name' => 'CourseMember',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['institute_memberships'] = [
            'class_name' => 'InstituteMember',
            'order_by'   => 'ORDER BY priority ASC',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['admission_applications'] = [
            'class_name' => 'AdmissionApplication',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['archived_course_memberships'] = [
            'class_name' => 'ArchivedCourseMember',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['datafields'] = [
            'class_name'  => 'DatafieldEntryModel',
            'foreign_key' => function ($user) {
                return [$user];
            },
            'assoc_foreign_key' => function ($model, $params) {
                $model->setValue('range_id', $params[0]->id);
            },
            'assoc_func' => 'findByModel',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['studycourses'] = [
            'class_name' => 'UserStudyCourse',
            'assoc_func' => 'findByUser',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_and_belongs_to_many']['contacts'] = [
            'class_name'     => 'User',
            'thru_table'     => 'contact',
            'thru_key'       => 'owner_id',
            'thru_assoc_key' => 'user_id',
            'order_by'       => 'ORDER BY Nachname, Vorname',
            'on_delete'      => 'delete',
            'on_store'       => 'store',
        ];
        $config['has_many']['contactgroups'] = [
            'class_name'        => 'Statusgruppen',
            'assoc_foreign_key' => 'range_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];
        $config['has_one']['info'] = [
            'class_name' => 'UserInfo',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_one']['online'] = [
            'class_name' => 'UserOnline',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];
        $config['has_many']['consultation_blocks'] = [
            'class_name'        => ConsultationBlock::class,
            'assoc_foreign_key' => 'teacher_id',
            'on_delete'         => 'delete',
        ];
        $config['has_many']['consultation_bookings'] = [
            'class_name' => ConsultationBooking::class,
            'on_delete'  => 'delete',
        ];

        $info = new UserInfo();
        $info_meta = $info->getTableMetadata();
        foreach ($info_meta ['fields'] as $field => $meta) {
            if ($field !== $info_meta['pk'][0]) {
                $config['additional_fields'][$field] = [
                    'get'            => '_getAdditionalValueFromRelation',
                    'set'            => '_setAdditionalValueFromRelation',
                    'relation'       => 'info',
                    'relation_field' => $field,
                ];
            }
        }

        parent::configure($config);
    }

    /**
     * Returns the currently authenticated user.
     *
     * @return User User
     */
    public static function findCurrent()
    {
        if (is_object($GLOBALS['user'])) {
            return $GLOBALS['user']->getAuthenticatedUser();
        }
    }

    /**
     * build new object with given data
     *
     * @param $data array assoc array of record
     * @return User
     */
    public static function build($data, $is_new = true)
    {
        $user = new User();
        $user->info = new UserInfo();
        $user->setData($data);
        $user->setNew($is_new);
        foreach (array_keys($user->db_fields) as $field) {
            $user->content_db[$field] = $user->content[$field];
        }
        $user->info = UserInfo::build($data, $is_new);
        return $user;
    }

    /**
     * Returns user object including user_info
     *
     * @param string $id
     * @return User User
     */
    public static function findFull($id)
    {
        $sql = "SELECT *
                FROM auth_user_md5
                LEFT JOIN user_info USING (user_id)
                WHERE user_id = ?";
        $data = DbManager::get()->fetchOne($sql, [$id]);
        if ($data) {
            return self::buildExisting($data);
        }
    }

    /**
     * Returns user objects including user_info
     *
     * @param array $ids
     * @param string $order_by
     * @return User[] User
     */
    public static function findFullMany($ids, $order_by = '')
    {
        $sql = "SELECT *
                FROM auth_user_md5
                LEFT JOIN user_info USING (user_id)
                WHERE user_id IN (?) " . $order_by;
        $data = DbManager::get()->fetchAll($sql, [$ids], 'User::buildExisting');
        return $data;
    }

    /**
     * return user object for given username
     *
     * @param string $username a username
     * @return User
     */
    public static function findByUsername($username)
    {
        return parent::findOneByUsername($username);
    }

    /**
     * returns an array of User-objects that have the given value in the
     * given datafield.
     * @param string $datafield_id
     * @param array of User
     */
    public static function findByDatafield($datafield_id, $value)
    {
        $query = "SELECT range_id
                  FROM datafields_entries
                  WHERE datafield_id = :datafield_id
                    AND content = :value";
        $search = DBManager::get()->prepare($query);
        $search->execute(compact('datafield_id', 'value'));
        $users = [];
        foreach ($search->fetchAll(PDO::FETCH_COLUMN) as $user_id) {
            $users[] = new User($user_id);
        }
        return $users;
    }

    public static function findDozentenByTermin_id($termin_id)
    {
        $record = new User();
        $db = DBManager::get();
        $sql = "SELECT `{$record->db_table}`.*
                FROM `{$record->db_table}`
                INNER JOIN `termin_related_persons` USING (user_id)
                WHERE `termin_related_persons`.`range_id` = ?
                ORDER BY Nachname, Vorname ASC";
        $statement = $db->prepare($sql);
        $statement->execute([$termin_id]);

        $ret = [];
        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $item = new User();
            $item->setData($row, true);
            $item->setNew(false);

            $ret[] = $item;
        }
        return $ret;
    }

    /**
     * Wraps a search parameter in %..% if the parameter itself does not
     * contain % or _.
     *
     * @param String $needle Search parameter
     * @return String containing the wrapped needle if neccessary
     */
    private static function searchParam($needle)
    {
        if (preg_match('/[%_]/S', $needle)) {
            return $needle;
        }

        return '%' . $needle . '%';
    }

    /**
     * Temporary migrate to User.class.php
     *
     * @param $attributes
     * @return array
     */
    public static function search($attributes)
    {
        $params = [];
        $joins  = [];
        $where  = [];

        $query = "SELECT au.*, ui.*
                  FROM `auth_user_md5` au
                  LEFT JOIN `user_online` uo ON (au.`user_id` = uo.`user_id`)
                  LEFT JOIN `user_info` ui ON (au.`user_id` = ui.`user_id`)";

        if ($attributes['username']) {
            $where[] =  "au.`username` like :username";
            $params[':username'] = self::searchParam($attributes['username']);
        }

        if ($attributes['vorname']) {
            $where[] = "au.`Vorname` LIKE :vorname";
            $params[':vorname'] = self::searchParam($attributes['vorname']);
        }

        if ($attributes['nachname']) {
            $where[] = "au.`Nachname` LIKE :nachname";
            $params[':nachname'] = self::searchParam($attributes['nachname']);
        }

        if ($attributes['email']) {
            $where[] = "au.`Email` LIKE :email";
            $params[':email'] = self::searchParam($attributes['email']);
        }

        //permissions
        if (!is_null($attributes['perm']) && $attributes['perm'] != 'alle') {
            $where[] = "au.`perms` = :perms";
            $params[':perms'] = $attributes['perm'];
        }

        //locked user
        if ((int)$attributes['locked'] == 1) {
            $where[] = "au.`locked` = 1";
        }

        // show only users who are not lecturers
        if ($attributes['show_only_not_lectures']) {
            $where[] = "au.`user_id` NOT IN (SELECT `user_id` FROM `seminar_user` WHERE `status` = 'dozent') ";
        }

        if ($attributes['auth_plugins']) {
            $where[] = "IFNULL(`auth_plugin`, 'preliminary') = :auth_plugins ";
            $params[':auth_plugins'] = $attributes['auth_plugins'];
        }

        //inactivity
        if (!is_null($attributes['inaktiv']) && $attributes['inaktiv'][0] != 'nie') {
            $comp = in_array(trim($attributes['inaktiv'][0]), ['=', '>', '<=']) ? $attributes['inaktiv'][0] : '=';
            $days = (int)$attributes['inaktiv'][1];
            $where[] = "uo.`last_lifesign` {$comp} UNIX_TIMESTAMP(TIMESTAMPADD(DAY, -{$days}, NOW())) ";
        } elseif (!is_null($attributes['inaktiv'])) {
            $where[] = "uo.`last_lifesign` IS NULL";
        }

        //datafields
        if (!is_null($attributes['datafields']) && count($attributes['datafields']) > 0) {
            $joins[] = "LEFT JOIN `datafields_entries` de ON (de.`range_id` = au.`user_id`)";
            foreach ($attributes['datafields'] as $id => $entry) {
                $where[] = "de.`datafield_id` = :df_id_". $id;
                $where[] = "de.`content` LIKE :df_content_". $id;
                $params[':df_id_' . $id] = $id;
                $params[':df_content_' . $id] = $entry;
            }
        }

        // roles
        if (!empty($attributes['roles'])) {
            $joins[] = "LEFT JOIN `roles_user` ON roles_user.`userid` = au.`user_id`";
            $where[] = "roles_user.`roleid` IN (:roles)";
            $params[':roles'] = $attributes['roles'];
        }

        // userdomains
        if ($attributes['userdomains']) {
            $joins[] = "LEFT JOIN `user_userdomains` uud ON (au.`user_id` = uud.`user_id`)";
            $joins[] = "LEFT JOIN `userdomains` uds USING (`userdomain_id`)";
            if ($attributes['userdomains'] === 'null-domain') {
                $where[] = "`userdomain_id` IS NULL ";
            } else {
                $where[] = "userdomain_id = :userdomains";
                $params[':userdomains'] = $attributes['userdomains'];
            }
        }

        // degree or studycourse
        if (!empty($attributes['degree']) || !empty($attributes['studycourse']) || !empty($attributes['fachsem'])) {
            $joins[] = "LEFT JOIN `user_studiengang` us ON (us.`user_id` = au.`user_id`)";
            if (!empty($attributes['degree'])) {
                $where[] = "us.`abschluss_id` IN (:degree)";
                $params[':degree'] = $attributes['degree'];
            }

            if (!empty($attributes['studycourse'])) {
                $where[] = "us.`fach_id` IN (:studycourse)";
                $params[':studycourse'] = $attributes['studycourse'];
            }

            if(!empty($attributes['fachsem'])) {
                $where[] = 'us.`semester` = :fachsem';
                $params[':fachsem'] = $attributes['fachsem'];
            }
        }

        if ($attributes['institute']) {
            $joins[] = "LEFT JOIN `user_inst` uis ON uis.`user_id` = au.`user_id`";
            $where[] = "uis.`Institut_id` = :institute";
            $params[':institute'] = $attributes['institute'];
        }

        $query .= implode(' ', $joins);
        $query .= " WHERE 1 AND ";
        $query .= implode(' AND ', $where);
        $query .= " GROUP BY au.`user_id` ";

        //sortieren
        switch ($attributes['sort']) {
            case "perms":
                $query .= "ORDER BY au.`perms` {$attributes['order']}, au.`username`";
                break;
            case "Vorname":
                $query .= "ORDER BY au.`Vorname` {$attributes['order']}, au.`Nachname`";
                break;
            case "Nachname":
                $query .= "ORDER BY au.`Nachname` {$attributes['order']}, au.`Vorname`";
                break;
            case "Email":
                $query .= "ORDER BY au.`Email` {$attributes['order']}, au.`username`";
                break;
            case "changed":
                $query .= "ORDER BY uo.`last_lifesign` {$attributes['order']}, au.`username`";
                break;
            case "mkdate":
                $query .= "ORDER BY ui.`mkdate` {$attributes['order']}, au.`username`";
                break;
            case "auth_plugin":
                $query .= "ORDER BY `auth_plugin` {$attributes['order']}, au.`username`";
                break;
            default:
                $query .= " ORDER BY au.`username` {$attributes['order']}";
        }

        return DBManager::get()->fetchAll($query, $params, __CLASS__ . '::buildExisting');
    }


    /**
     * @see SimpleORMap::store()
     */
    public function store()
    {
        if ($this->isDirty() && !$this->info->isFieldDirty('chdate')) {
            $this->info->setValue('chdate', time());
        }
        return parent::store();
    }

    /**
     * @see SimpleORMap::triggerChdate()
     */
    public function triggerChdate()
    {
       return $this->info->triggerChdate();
    }

    /**
     * returns the name in specified format
     * (formats defined in $GLOBALS['_fullname_sql'])
     *
     * @param string one of full,full_rev,no_title,no_title_rev,no_title_short,no_title_motto,full_rev_username
     * @return string guess what - the fullname
     */
    public function getFullName($format = 'full')
    {
        static $concat,$left,$if,$quote;

        $sql = $GLOBALS['_fullname_sql'][$format];
        if (!$sql || $format == 'no_title') {
            return $this->vorname . ' ' . $this->nachname;
        }
        if ($format == 'no_title_rev') {
            return $this->nachname . ', ' . $this->vorname;
        }
        if ($concat === null) {
            $concat = function() {return join('', func_get_args());};
            $left = function($str, $c = 0) {return mb_substr($str,0,$c);};
            $if = function($ok,$yes,$no) {return $ok ? $yes : $no;};
            $quote = function($str) {return "'" . addcslashes($str, "\\'\0") . "'";};
        }

        $data = array_map($quote, $this->toArray('vorname nachname username title_front title_rear motto perms'));
        $replace_func['CONCAT'] = '$concat';
        $replace_func['LEFT'] = '$left';
        $replace_func['UCASE'] = 'mb_strtoupper';
        $replace_func['IF'] = '$if';
        $eval = strtr($sql, $replace_func);
        $eval = strtr(mb_strtolower($eval), $data);
        return eval('return ' . $eval . ';');
    }

    public function toArrayRecursive($only_these_fields = null)
    {
        $ret = parent::toArrayRecursive($only_these_fields);
        unset($ret['info']);
        return  $ret;
    }

    /**
     * Returns whether the user was assigned a certain role.
     *
     * @param string $role         The role to check
     * @param string $institute_id An optional institute_id
     * @return bool True if the user was assigned this role, false otherwise
     */
    public function hasRole($role, $institute_id = '')
    {
        return RolePersistence::isAssignedRole($this->user_id, $role, $institute_id);
    }

    /**
     * Returns the roles that were assigned to the user.
     *
     * @param boolean $with_implicit
     * @return array
     */
    public function getRoles($with_implicit = false)
    {
        return RolePersistence::getAssignedRoles($this->user_id, $with_implicit);
    }

    /**
     * Returns whether the given user is stored in contacts.
     *
     * @param User $another_user
     * @return bool
     */
    public function isFriendOf($another_user)
    {
        return (bool) DBManager::get()->fetchColumn("SELECT 1 FROM contact WHERE owner_id=? AND user_id=?", [$this->user_id, $another_user->user_id]);
    }

    /**
     * checks if at least one field was modified since last restore
     *
     * @return boolean
     */
    public function isDirty()
    {
        return parent::isDirty() || $this->info->isDirty();
    }

    /**
     * checks if given field was modified since last restore
     *
     * @param string $field
     * @return boolean
     */
    public function isFieldDirty($field)
    {
        $field = mb_strtolower($field);
        return (array_key_exists($field, $this->content_db) ? parent::isFieldDirty($field) : $this->info->isFieldDirty($field));
    }

    /**
     * reverts value of given field to last restored value
     *
     * @param string $field
     * @return mixed the restored value
     */
    public function revertValue($field)
    {
        $field = mb_strtolower($field);
        return (array_key_exists($field, $this->content_db) ? parent::revertValue($field) : $this->info->revertValue($field));
    }

    /**
     * returns unmodified value of given field
     *
     * @param string $field
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function getPristineValue($field)
    {
        $field = mb_strtolower($field);
        return (array_key_exists($field, $this->content_db) ? parent::getPristineValue($field) : $this->info->getPristineValue($field));
    }

    /**
     * Returns data of table row as assoc array with raw contents like
     * they are in the database.
     * Pass array of fieldnames or ws separated string to limit
     * fields.
     *
     * @param mixed $only_these_fields
     * @return array
     */
    public function toRawArray($only_these_fields = null)
    {
        return array_merge($this->info->toRawArray($only_these_fields), parent::toRawArray($only_these_fields));
    }

    /**
     * @param string $relation
     */
    public function initRelation($relation)
    {
        parent::initRelation($relation);
        if ($relation == 'info' && is_null($this->relations['info'])) {
            $options = $this->getRelationOptions($relation);
            $result = new $options['class_name'];
            $foreign_key_value = call_user_func($options['assoc_func_params_func'], $this);
            call_user_func($options['assoc_foreign_key_setter'], $result, $foreign_key_value);
            $this->relations[$relation] = $result;
        }
    }

    /**
     * This function returns the perms allowed for an institute for the current user
     *
     * @return array list of perms
     */
    public function getInstitutePerms()
    {
        if($this->perms === 'admin') {
            return ['admin'];
        }
        $allowed_status = [];
        $possible_status = ['autor', 'tutor', 'dozent'];

        $pos = array_search($this->perms, $possible_status);

        if ($pos !== false) {
            $allowed_status = array_slice($possible_status, 0, $pos + 1);
        }
        return $allowed_status;
    }

    /**
     * Get the decorated StudIP-Kings information
     * @return String
     */
    public function getStudipKingIcon()
    {
        $is_king = StudipKing::is_king($this->user_id, TRUE);

        $result = '';
        foreach ($is_king as $type => $text) {
            $type = str_replace('_', '-', $type);
            $result .= Assets::img('crowns/crown-' . $type . '.png', ['alt' => $text, 'title' => $text]);
        }

        return $result ?: null;
    }

    /**
     * Builds an array containing all available elements that are part of a
     * user's homepage together with their visibility. It isn't sufficient to
     * just load the visibility settings from database, because if the user
     * has added some data (e.g. CV) but not yet assigned a special visibility
     * to that field, it wouldn't show up.
     *
     * @return array An array containing all available homepage elements
     * together with their visibility settings in the form
     * $name => $visibility.
     */
    public function getHomepageElements()
    {
        $homepage_visibility = get_local_visibility_by_id($this->id, 'homepage');
        if (is_array(json_decode($homepage_visibility, true))) {
            $homepage_visibility = json_decode($homepage_visibility, true);
        } else {
            $homepage_visibility = [];
        }

        // News
        $news = StudipNews::GetNewsByRange($this->id, true);

        // Non-private dates.
        if (Config::get()->CALENDAR_ENABLE) {
            $dates = CalendarEvent::countBySql('range_id = ?', [$this->id]);
        }

        // Votes
        if (Config::get()->VOTE_ENABLE) {
            $activeVotes  = Questionnaire::countBySQL("user_id = ? AND visible = '1'", [$this->id]);
            $stoppedVotes = Questionnaire::countBySQL("user_id = ? AND visible = '0'", [$this->id]);
        }
        // Evaluations
        $evalDB = new EvaluationDB();
        $activeEvals = $evalDB->getEvaluationIDs($this->id, EVAL_STATE_ACTIVE);
        // Literature
        $lit_list = StudipLitList::GetListsByRange($this->id);
        // Free datafields
        $data_fields = DataFieldEntry::getDataFieldEntries($this->id, 'user');
        $homepageplugins = [];

        // Now join all available elements with visibility settings.
        $homepage_elements = [];

        if (Avatar::getAvatar($this->id)->is_customized() && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['picture']) {
            $homepage_elements['picture'] = [
                'name'        => _('Eigenes Bild'),
                'visibility'  => $homepage_visibility['picture'] ?: get_default_homepage_visibility($this->id),
                'extern'      => true,
                'category'    => 'Allgemeine Daten',
            ];
        }

        if ($this->info->motto && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['motto']) {
            $homepage_elements['motto'] = [
                'name'       => _('Motto'),
                'visibility' => $homepage_visibility['motto'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Private Daten',
            ];
        }
        if (Config::get()->ENABLE_SKYPE_INFO) {
            if ($GLOBALS['user']->cfg->getValue('SKYPE_NAME') && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['skype_name']) {
                $homepage_elements['skype_name'] = [
                    'name'       => _('Skype Name'),
                    'visibility' => $homepage_visibility['skype_name'] ?: get_default_homepage_visibility($this->id),
                    'category'   => 'Private Daten',
                ];
            }
        }
        if ($this->info->privatnr && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['Private Daten_phone']) {
            $homepage_elements['private_phone'] = [
                'name'       => _('Private Telefonnummer'),
                'visibility' => $homepage_visibility['private_phone'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Private Daten',
            ];
        }
        if ($this->info->privatcell && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['private_cell']) {
            $homepage_elements['private_cell'] = [
                'name'       => _('Private Handynummer'),
                'visibility' => $homepage_visibility['private_cell'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Private Daten',
            ];
        }
        if ($this->info->privadr && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['privadr']) {
            $homepage_elements['privadr'] = [
                'name'         => _('Private Adresse'),
                'visibility'   => $homepage_visibility['privadr'] ?: get_default_homepage_visibility($this->id),
                'category'     => 'Private Daten',
            ];
        }
        if ($this->info->home && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['homepage']) {
            $homepage_elements['homepage'] = [
                'name'        => _('Homepage-Adresse'),
                'visibility'  => $homepage_visibility['homepage'] ?: get_default_homepage_visibility($this->id),
                'extern'      => true,
                'category'    => 'Private Daten',
            ];
        }
        if ($news && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['news']) {
            $homepage_elements['news'] = [
                'name'       => _('Ankündigungen'),
                'visibility' => $homepage_visibility['news'] ?: get_default_homepage_visibility($this->id),
                'extern'     => true,
                'category'   => 'Allgemeine Daten',
            ];
        }
        if (Config::get()->CALENDAR_ENABLE && $dates && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['dates']) {
            $homepage_elements['termine'] = [
                'name'       => _('Termine'),
                'visibility' => $homepage_visibility['termine'] ?: get_default_homepage_visibility($this->id),
                'extern'     => true,
                'category'   => 'Allgemeine Daten',
            ];
        }
        if (Config::get()->VOTE_ENABLE && ($activeVotes || $stoppedVotes || $activeEvals) && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['votes']) {
            $homepage_elements['votes'] = [
                'name'       => _('Fragebögen'),
                'visibility' => $homepage_visibility['votes'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Allgemeine Daten',
            ];
        }

        $query = "SELECT 1
                  FROM user_inst
                  LEFT JOIN Institute USING (Institut_id)
                  WHERE user_id = ? AND inst_perms = 'user'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        if ($statement->fetchColumn() && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['studying']) {
            $homepage_elements['studying'] = [
                'name'       => _('Wo ich studiere'),
                'visibility' => $homepage_visibility['studying'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Studien-/Einrichtungsdaten',
            ];
        }
        if ($lit_list && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['literature']) {
            $homepage_elements['literature'] = [
                'name'       => _('Literaturlisten'),
                'visibility' => $homepage_visibility['literature'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Allgemeine Daten',
            ];
        }
        if ($this->info->lebenslauf && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['lebenslauf']) {
            $homepage_elements['lebenslauf'] = [
                'name'       => _('Lebenslauf'),
                'visibility' => $homepage_visibility['lebenslauf'] ?: get_default_homepage_visibility($this->id),
                'extern'     => true,
                'category'   => 'Private Daten',
            ];
        }
        if ($this->info->hobby && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['hobby']) {
            $homepage_elements['hobby'] = [
                'name'       => _('Hobbies'),
                'visibility' => $homepage_visibility['hobby'] ?: get_default_homepage_visibility($this->id),
                'category'   => 'Private Daten',
            ];
        }
        if ($this->info->publi && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['publi']) {
            $homepage_elements['publi'] = [
                'name'       => _('Publikationen'),
                'visibility' => $homepage_visibility['publi'] ?: get_default_homepage_visibility($this->id),
                'extern'     => true,
                'category'   => 'Private Daten',
            ];
        }
        if ($this->info->schwerp && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['schwerp']) {
            $homepage_elements['schwerp'] = [
                'name'       => _('Arbeitsschwerpunkte'),
                'visibility' => $homepage_visibility['schwerp'] ?: get_default_homepage_visibility($this->id),
                'extern'     => true,
                'category'   => 'Private Daten',
            ];
        }

        if ($data_fields) {
            foreach ($data_fields as $key => $field) {
                if ($field->getValue() && $field->isEditable($this->perms) && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms][$key]) {
                    $homepage_elements[$key] = [
                        'name'       => $field->getName(),
                        'visibility' => $homepage_visibility[$key] ?: get_default_homepage_visibility($this->id),
                        'extern'     => true,
                        'category'   => 'Zusätzliche Datenfelder',
                    ];
                }
            }
        }

        foreach (Kategorie::findByUserId($this->id) as $category) {
            $homepage_elements['kat_' . $category->id] = [
                'name'       => $category->name,
                'visibility' => $homepage_visibility['kat_' . $category->id] ?: get_default_homepage_visibility($this->id),
                'extern'     => true,
                'category'   => 'Eigene Kategorien',
            ];
        }

        if ($homepageplugins) {
            foreach ($homepageplugins as $plugin) {
                $homepage_elements['plugin_' . $plugin->getPluginId()] = [
                    'name'       => $plugin->getPluginName(),
                    'visibility' => $homepage_visibility['plugin_'.$plugin->getPluginId()] ?: get_default_homepage_visibility($this->id),
                    'category'   => 'Plugins',
                ];
            }
        }
        return $homepage_elements;
    }

    /**
     * Changes a user's email adress.
     *
     * @param string $email New email
     * @param bool   $force Force update (even if nothing actually changed)
     * @return bool
     */
    public function changeEmail($email, $force = false)
    {
        // Email did not actually change and update is not forced
        if ($this->email === $email && !$force) {
            return true;
        }

        // Is changing of email globally allowed?
        if (!Config::get()->ALLOW_CHANGE_EMAIL) {
            return false;
        }

        // Is changing of email allowed by auth plugin?
        if (StudipAuthAbstract::CheckField('auth_user_md5.Email', $this->auth_plugin) || LockRules::check($this->user_id, 'email')) {
            return false;
        }

        $validator          = new email_validation_class; ## Klasse zum Ueberpruefen der Eingaben
        $validator->timeout = 10;
        $REMOTE_ADDR        = $_SERVER['REMOTE_ADDR'];
        $Zeit               = date('H:i:s, d.m.Y');

        // accept only registered domains if set
        $email_restriction = trim(Config::get()->EMAIL_DOMAIN_RESTRICTION);
        if (!$validator->ValidateEmailAddress($email, $email_restriction)) {
            if ($email_restriction) {
                $email_restriction_msg_part = '';
                $email_restriction_parts    = explode(',', $email_restriction);
                for ($email_restriction_count = 0; $email_restriction_count < count($email_restriction_parts); $email_restriction_count++) {
                    if ($email_restriction_count == count($email_restriction_parts) - 1) {
                        $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . '<br>';
                    } else if (($email_restriction_count + 1) % 3) {
                        $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ', ';
                    } else {
                        $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ',<br>';
                    }
                }
                PageLayout::postError(sprintf(_('Die E-Mail-Adresse fehlt, ist falsch geschrieben oder gehört nicht zu folgenden Domains:%s'),
                    '<br>' . $email_restriction_msg_part));
            } else {
                PageLayout::postError(_('Die E-Mail-Adresse fehlt oder ist falsch geschrieben!'));
            }
            return false;
        }

        if (!$validator->ValidateEmailHost($email)) {     // Mailserver nicht erreichbar, ablehnen
            PageLayout::postError(_('Der Mailserver ist nicht erreichbar. Bitte überprüfen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken können!'));
            return false;
        } else {       // Server ereichbar
            if (!$validator->ValidateEmailBox($email)) {    // aber user unbekannt. Mail an abuse!
                StudipMail::sendAbuseMessage("edit_about", "Emailbox unbekannt\n\nUser: " . $this->username . "\nEmail: ".$email ."\n\nIP: " . $REMOTE_ADDR ." \nZeit: " . $Zeit . "\n");
                PageLayout::postError(_('Die angegebene E-Mail-Adresse ist nicht erreichbar. Bitte überprüfen Sie Ihre Angaben!'));
                return false;
            }
        }

        if (self::countBySql('email = ? AND user_id != ?', [$email, $this->user_id])) {
            PageLayout::postError(sprintf(_('Die angegebene E-Mail-Adresse wird bereits von einem anderen Benutzer (%s) verwendet. Bitte geben Sie eine andere E-Mail-Adresse an.'),
                htmlReady($this->getFullName())));
            return false;
        }

        if (StudipAuthAbstract::CheckField('auth_user_md5.validation_key', $this->auth_plugin)) {
            PageLayout::postSuccess(_('Ihre E-Mail-Adresse wurde geändert!'));
        } else {
            // auth_plugin does not map validation_key (what if...?)

            // generate 10 char activation key
            $key = '';
            mt_srand((double)microtime() * 1000000);
            for ($i = 1; $i <= 10; $i++) {
                $temp = mt_rand() % 36;
                if ($temp < 10)
                    $temp += 48;   // 0 = chr(48), 9 = chr(57)
                else
                    $temp += 87;   // a = chr(97), z = chr(122)
                $key .= chr($temp);
            }
            $this->validation_key = $key;

            $activatation_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'activate_email.php?uid=' . $this->user_id . '&key=' . $this->validation_key;
            // include language-specific subject and mailbody with fallback to german
            $lang = getUserLanguagePath($this->id);
            if($lang == '') {
                $lang = 'de';
            }
            include_once("locale/$lang/LC_MAILS/change_self_mail.inc.php");

            $mail = StudipMail::sendMessage($email, $subject, $mailbody);

            if (!$mail) {
                return true;
            }

            $this->store();

            PageLayout::postInfo(sprintf(_('An Ihre neue E-Mail-Adresse <b>%s</b> wurde ein Aktivierungslink geschickt, dem Sie folgen müssen bevor Sie sich das nächste mal einloggen können.'), $email));
            StudipLog::log('USER_NEWPWD', $this->user_id);
        }
        return true;
    }

    /**
     * Merge an user ($old_id) to another user ($new_id).  This is a part of the
     * old numit-plugin.
     *
     * @param string $old_user
     * @param string $new_user
     * @param boolean $identity merge identity (if true)
     *
     * @return array() messages to display after migration
     * @deprecated
     */
    public static function convert($old_id, $new_id, $identity = false)
    {
        NotificationCenter::postNotification('UserWillMigrate', $old_id, $new_id);

        $messages = [];

        //Identitätsrelevante Daten migrieren
        if ($identity) {
            // Veranstaltungseintragungen
            self::removeDoubles('seminar_user', 'Seminar_id', $new_id, $old_id);
            $query = "UPDATE IGNORE seminar_user SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            self::removeDoubles('admission_seminar_user', 'seminar_id', $new_id, $old_id);
            $query = "UPDATE IGNORE admission_seminar_user SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            // Persönliche Infos
            $query = "DELETE FROM user_info WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id]);

            $query = "UPDATE IGNORE user_info SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            // Migrate registration timestamp by creating a new empty user info
            // entry
            $query = "INSERT INTO `user_info` (`user_id`, `mkdate`, `chdate`)
                      SELECT ?, `mkdate`, `chdate`
                      FROM `user_info`
                      WHERE `user_id` = ?";
            DBManager::get()->execute($query, [$old_id, $new_id]);

            // Studiengänge
            self::removeDoubles('user_studiengang', 'fach_id', $new_id, $old_id);
            $query = "UPDATE IGNORE user_studiengang SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            // Eigene Kategorien
            $query = "UPDATE IGNORE kategorien SET range_id = ? WHERE range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            // Institute
            self::removeDoubles('user_inst', 'Institut_id', $new_id, $old_id);
            $query = "UPDATE IGNORE user_inst SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            // Generische Datenfelder zusammenführen (bestehende Einträge des
            // "neuen" Nutzers werden dabei nicht überschrieben)
            $old_user = User::find($old_id);

            $query = "INSERT INTO datafields_entries
                        (datafield_id, range_id, sec_range_id, content, mkdate, chdate)
                      VALUES (:datafield_id, :range_id, :sec_range_id, :content,
                              :mkdate, :chdate)
                      ON DUPLICATE KEY
                        UPDATE content = IF(content IN ('', 'default_value'), VALUES(content), content),
                               chdate = UNIX_TIMESTAMP()";

            $old_user->datafields->each(function ($field) use ($new_id, $query) {
                if (!$field->isNew() && $field->content !== null) {
                    $data = $field->toArray('datafield_id sec_range_id content mkdate chdate');
                    $data['range_id'] = $new_id;
                    DBManager::get()->execute($query, $data);
                }
            });

            # Datenfelder des alten Nutzers leeren
            $old_user->datafields = [];
            $old_user->store();

            //

            //Buddys
            $query = "UPDATE IGNORE contact SET owner_id = ? WHERE owner_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$new_id, $old_id]);

            // Avatar
            $old_avatar = Avatar::getAvatar($old_id);
            $new_avatar = Avatar::getAvatar($new_id);
            if ($old_avatar->is_customized()) {
                if (!$new_avatar->is_customized()) {
                    $avatar_file = $old_avatar->getFilename(AVATAR::ORIGINAL);
                    if (!file_exists($avatar_file)) {
                        $avatar_file = $old_avatar->getFilename(AVATAR::NORMAL);
                    }
                    $new_avatar->createFrom($avatar_file);
                }
                $old_avatar->reset();
            }

            $messages[] = _('Identitätsrelevante Daten wurden migriert.');
        }

        // Restliche Daten übertragen

        // ForumsModule migrieren
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $plugin->migrateUser($old_id, $new_id);
        }

        // Dateieintragungen und Ordner
        // TODO (mlunzena) should post a notification
        $query = "UPDATE IGNORE file_refs SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE files SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE folders SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        //Kalender
        $query = "UPDATE IGNORE calendar_event SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE calendar_user SET owner_id = ? WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE calendar_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE event_data SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE event_data SET editor_id = ? WHERE editor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        //Archiv
        self::removeDoubles('archiv_user', 'seminar_id', $new_id, $old_id);
        $query = "UPDATE IGNORE archiv_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Evaluationen
        $query = "UPDATE IGNORE eval SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        self::removeDoubles('eval_user', 'eval_id', $new_id, $old_id);
        $query = "UPDATE IGNORE eval_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE evalanswer_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Kategorien
        $query = "UPDATE IGNORE kategorien SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Literatur
        $query = "UPDATE IGNORE lit_catalog SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE lit_list SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Nachrichten (Interne)
        $query = "UPDATE IGNORE message SET autor_id = ? WHERE autor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        self::removeDoubles('message_user', 'message_id', $new_id, $old_id);
        $query = "UPDATE IGNORE message_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // News
        $query = "UPDATE IGNORE news SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE news_range SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Informationsseiten
        $query = "UPDATE IGNORE scm SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Statusgruppeneinträge
        self::removeDoubles('statusgruppe_user', 'statusgruppe_id', $new_id, $old_id);
        $query = "UPDATE IGNORE statusgruppe_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        // Termine
        $query = "UPDATE IGNORE termine SET autor_id = ? WHERE autor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        //Votings
        $query = "UPDATE IGNORE questionnaires SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE questionnaire_assignments SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE questionnaire_assignments SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        self::removeDoubles('questionnaire_anonymous_answers', 'questionnaire_id', $new_id, $old_id);
        $query = "UPDATE IGNORE questionnaire_anonymous_answers SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        self::removeDoubles('questionnaire_answers', 'question_id', $new_id, $old_id);
        $query = "UPDATE IGNORE questionnaire_answers SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        //Wiki
        $query = "UPDATE IGNORE wiki SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        $query = "UPDATE IGNORE wiki_locks SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        //Adressbucheinträge
        $query = "UPDATE IGNORE contact SET owner_id = ? WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        //Blubber
        $query = "UPDATE IGNORE blubber SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);
        $query = "UPDATE IGNORE blubber_follower SET studip_user_id = ? WHERE studip_user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);
        $query = "UPDATE IGNORE blubber_mentions SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);
        $query = "UPDATE IGNORE blubber_reshares SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);
        $query = "UPDATE IGNORE blubber_streams SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);

        NotificationCenter::postNotification('UserDidMigrate', $old_id, $new_id);

        $messages[] = _('Dateien, Termine, Adressbuch, Nachrichten und weitere Daten wurden migriert.');
        return $messages;
    }

    /**
     * Delete double entries of the old and new user. This is a part of the old
     * numit-plugin.
     *
     * @param string $table
     * @param string $field
     * @param md5 $new_id
     * @param md5 $old_id
     * @deprecated
     */
    private static function removeDoubles($table, $field, $new_id, $old_id)
    {
        $items = [];

        $query = "SELECT a.{$field} AS field_item
                  FROM {$table} AS a, {$table} AS b
                  WHERE a.user_id = ? AND b.user_id = ? AND a.{$field} = b.{$field}";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$new_id, $old_id]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $value) {
            array_push($items, $value['field_item']);
        }

        if (!empty($items)) {
            $query = "DELETE FROM `{$table}`
                      WHERE user_id = :user_id AND `{$field}` IN (:items)";

            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $new_id);
            $statement->bindValue(':items', $items, StudipPDO::PARAM_ARRAY);
            $statement->execute();
        }
    }

    /**
     * Returns a descriptive text for the range type.
     *
     * @return string
     */
    public function describeRange()
    {
        return _('NutzerIn');
    }

    /**
     * Returns a unique identificator for the range type.
     *
     * @return string
     */
    public function getRangeType()
    {
        return 'user';
    }

    /**
     * Returns the id of the current range
     *
     * @return mixed (string|int)
     */
    public function getRangeId()
    {
        return $this->id;
    }

    /**
     * Decides whether the user may access the range.
     *
     * @param string $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function userMayAccessRange($user_id = null)
    {
        // TODO: Visibility checks
        if ($user_id === null) {
            $user_id = $GLOBALS['user']->id;
        }
        return $user_id === $this->user_id
            || self::find($user_id)->perms === 'root'
            || !in_array(self::find($user_id)->visible, ['no', 'never']);
    }

    /**
     * Decides whether the user may edit/alter the range.
     *
     * @param string $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function userMayEditRange($user_id = null)
    {
        if ($user_id === null) {
            $user_id = $GLOBALS['user']->id;
        }
        return $user_id === $this->user_id
            || self::find($user_id)->perms === 'root';
    }

    /**
     * Decides whether the user may administer the range.
     *
     * @param string $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function userMayAdministerRange($user_id = null)
    {
        return $this->userMayEditRange($user_id);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = User::findBySQL("user_id = ?", [$storage->user_id]);

        if ($sorm) {
            $limit ='user_id username password perms vorname nachname email validation_key auth_plugin locked lock_comment locked_by visible';
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray($limit);
            }
            if ($field_data) {
                $storage->addTabularData(_('Kerndaten'), 'auth_user_md5', $field_data);
            }

            $limit = 'user_id hobby lebenslauf publi schwerp home privatnr privatcell privadr score geschlecht mkdate chdate title_front title_rear preferred_language smsforward_copy smsforward_rec email_forward smiley_favorite motto lock_rule';
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray($limit);
            }
            if ($field_data) {
                $storage->addTabularData(_('Benutzer Informationen'), 'user_info', $field_data);
            }
        }

        $data = DBManager::get()->fetchAll('SELECT * FROM object_user_visits WHERE user_id = ?', [$storage->user_id]);
        $storage->addTabularData(_('Objekt Aufrufe'), 'object_user_visits', $data);
    }
}
