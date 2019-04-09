<?php
/**
 * LogEvent
 * model class for table log_events
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 * @property string event_id database column
 * @property string id alias column for event_id
 * @property string user_id database column
 * @property string action_id database column
 * @property string affected_range_id database column
 * @property string coaffected_range_id database column
 * @property string info database column
 * @property string dbg_info database column
 * @property string mkdate database column
 * @property LogAction action belongs_to LogAction
 * @property User user belongs_to User
 */


class LogEvent extends SimpleORMap implements PrivacyObject
{

    protected $formatted_text = '';

    protected static function configure($config = [])
    {
        $config['db_table'] = 'log_events';
        $config['belongs_to']['action'] = [
            'class_name' => 'LogAction',
            'foreign_key' => 'action_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        ];
        parent::configure($config);
    }

    /**
     * Returns the number of log events counted by actions as an array where
     * the ey is the action id and the value is the number of events for
     * this action.
     *
     * @return array Number of loge events for all actions
     */
    public static function countByActions()
    {
        $query = "SELECT action_id, COUNT(*) FROM log_events GROUP BY action_id";
        $statement = DBManager::get()->query($query);
        return $statement->fetchGrouped(PDO::FETCH_COLUMN);
    }

    /**
     * Deletes all expired events.
     *
     * @return int Number of deleted events.
     */
    public static function deleteExpired()
    {
        $db = DBManager::get();
        $sql = 'DELETE log_events FROM log_events JOIN log_actions USING(action_id)
            WHERE expires > 0 AND mkdate + expires < UNIX_TIMESTAMP()';
        return $db->exec($sql);
    }

    /**
     * Returns the formatted log event. Fills the action template with data
     * of this event.
     *
     * @return string The formatted log event.
     */
    public function formatEvent()
    {
        $text = $this->formatObject();
        $patterns = [
            '/%affected/',
            '/%coaffected/',
            '/%info/',
            '/%dbg_info/'
        ];
        $replacements = [
            $this->affected_range_id,
            $this->coaffected_range_id,
            htmlReady($this->info),
            htmlReady($this->dbg_info)
        ];
        $replace_callbacks = [
            '/%sem\(%affected\)/',
            '/%sem\(%coaffected\)/',
            '/%studyarea\(%affected\)/',
            '/%studyarea\(%coaffected\)/',
            '/%res\(%affected\)/',
            '/%res\(%coaffected\)/',
            '/%inst\(%affected\)/',
            '/%inst\(%coaffected\)/',
            '/%user\(%affected\)/',
            '/%user\(%coaffected\)/',
            '/%user/',
            '/%singledate\(%affected\)/',
            '/%semester\(%coaffected\)/',
            '/%plugin\(%coaffected\)/',
            '/%group\(%coaffected\)/',
        ];

        $text = preg_replace_callback($replace_callbacks, [$this, 'formatCallback'], $text);
        return preg_replace($patterns, $replacements, $text);
    }

    /**
     * @param $m
     * @return string
     */
    protected function formatCallback($m)
    {
        $map = [
            'sem'  => 'Seminar',
            'res'  => 'Resource',
            'inst' => 'Institute',
            'user' => 'Username',
            'group' => 'Statusgruppe'
        ];
        $ret = '';
        if (preg_match_all('/%([a-z]+)/', $m[0], $found)) {
            if (isset($found[1][0])) {
                $funcname = 'format' . (isset($map[$found[1][0]]) ? $map[$found[1][0]] : $found[1][0]);
                if (isset($found[1][1])) {
                    $param = $found[1][1] . '_range_id';
                } else {
                    $param = 'user_id';
                }
                if (is_callable([$this, $funcname])) {
                    $ret = call_user_func([$this, $funcname], $param);
                }
            }
        }
        return $ret;
    }

    /**
     * Returns the name of the resource for the resource id found in the
     * given field or the resource id if the resource is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of the resource or resource id.
     */
    protected function formatResource($field) {
        $resObj = ResourceObject::Factory($this->$field);
        if ($resObj->getName()) {
            return $resObj->getFormattedLink();
        }
        return $this->$field;
    }

    /**
     * Returns the name of the user with the id found in the given field.
     *
     * @param string $field The name of the table field.
     * @return string The name of the user.
     */
    protected function formatUsername($field)
    {
        return '<a href="' . URLHelper::getLink('dispatch.php/admin/user/edit/'
                . $this->$field) . '">' . htmlReady(get_fullname($this->$field))
                . '</a>';
    }

    /**
     * Returns the name of the seminar for the id found in the given
     * field or the id if the seminar is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of seminar or the id.
     */
    protected function formatSeminar($field)
    {
        $course = Course::find($this->$field);

        if (!$course) {
            return $this->$field;
        }
        return sprintf('<a href="%s">%s %s (%s)</a>',
                       URLHelper::getLink('dispatch.php/course/details',
                               ['sem_id' => $course->getId()]),
                       htmlReady($course->VeranstaltungsNummer),
                       htmlReady(my_substr($course->name, 0, 100)),
                       htmlReady($course->start_semester->name));
    }

    /**
     * Returns the name of the institute for the id found in the given
     * field or the id if the institute is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of institute or the id.
     */
    protected function formatInstitute($field)
    {
        $institute = Institute::find($this->$field);

        if (!$institute) {
            return $this->$field;
        }

        return sprintf('<a href="%s">%s</a>',
                       URLHelper::getLink('dispatch.php/institute/overview',
                               ['auswahl' => $institute->getId()]),
                       htmlReady(my_substr($institute->name, 0, 100)));
    }

    /**
     * Returns the name of the study area for the id found in the given
     * field or the id if the study area is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of seminar or the id.
     */
    protected function formatStudyarea($field)
    {
        $study_area = StudipStudyArea::find($this->$field);

        if (!$study_area) {
            return $this->$field;
        }

        return '<em>' . $study_area->getPath(' &gt ') . '</em>';
    }

    /**
     * Returns the singledate for the id found in the given field.
     *
     * @param string $field The name of the table field.
     * @return string The singledate.
     */
    protected function formatSingledate($field) {
        $termin = new SingleDate($this->$field);
        return '<em>' . $termin->toString() . '</em>';
    }

    /**
     * Returns the name of the plugin for the id found in the given
     * field or the id if the plugin is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of plugin or the id.
     */
    protected function formatPlugin($field) {
        $plugin_manager = PluginManager::getInstance();
        $plugin_info = $plugin_manager->getPluginInfoById($this->$field);

        return $plugin_info ? '<em>'
                . $plugin_info['name'] . '</em>' : $this->$field;
    }

    /**
     * Returns the name of the semester for the id found in the given
     * field or the id if the seminar is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of semester or the id.
     */
    protected function formatSemester($field) {
        $all_semester = SemesterData::getAllSemesterData();
        foreach ($all_semester as $val) {
            if ($val['beginn'] == $this->$field) {
                return '<em>' . $val['name'] . '</em>';
            }
        }
        return $this->$field;
    }

    /**
     * Returns the name of the statusgroup for the id found in the given
     * field or the id if the group is unknown.
     *
     * @param string $field The name of the table field.
     * @return string The name of statusgruppe or the id.
     */
    protected function formatStatusgruppe($field)
    {
        $group = Statusgruppen::find($this->$field);

        if (!$group) {
            return $this->$field;
        }
        $course = Course::find($group->range_id);
        return sprintf(
            '<a href="%s">%s</a>',
            URLHelper::getLink('dispatch.php/course/statusgroups', [
                'contentbox_open' => $group->getId()
            ]),
            htmlReady($group->name. ($course ? " (VA: ".$course->name.")" : ""))
        );
    }

    protected function formatObject()
    {
        if ($this->action) {
            switch ($this->action->type) {
                case 'plugin':
                    $plugin_manager = PluginManager::getInstance();
                    $plugin_info = $plugin_manager->getPluginInfo($this->action->class);
                    $class_name = $plugin_info['class'];
                    $plugin = $plugin_manager->getPlugin($class_name);
                    if ($plugin instanceof Loggable) {
                        return $class_name::logFormat($this);
                    }
                    break;
                case 'file':
                    if (!file_exists($this->action->filename)) {
                        require_once($this->action->filename);
                        $class_name = $this->action->class;
                        if ($class_name instanceof Loggable) {
                            return $class_name::logFormat($this);
                        }
                    }
                    break;
                case 'core':
                    $class_name = $this->action->class;
                    $interfaces = class_implements($class_name);
                    if (isset($interfaces['Loggable'])) {
                        return $class_name::logFormat($this);
                    }
            }
        }
        return $this->action->info_template;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $user_id = $storage->user_id;
        $templates = [];

        $query = "SELECT *
                  FROM log_events
                  WHERE :user_id IN (user_id, affected_range_id, coaffected_range_id)";
        $log = DBManager::get()->fetchAll($query, [':user_id' => $user_id]);

        foreach ($log as $pos => $event) {
            if (!array_key_exists($event['action_id'], $templates)) {
                $log_action = LogAction::find($event["action_id"]);
                $templates[$event['action_id']] = $log_action->info_template;
            }
            $template = $templates[$event['action_id']];
            $log_event = LogEvent::find($event['event_id']);

            // anonymize
            $was_censored = false;
            if ($event['user_id'] && substr_count($template, '%user ') && $event['user_id'] !== $user_id) {
                $event['user_id'] = preg_replace('/[^w]/', '#', $event['user_id']);
                $log_event->user_id = $event['user_id'];
                $was_censored = true;
            }
            if ($event['affected_range_id'] && substr_count($template, '%user(%affected)') && $event['affected_range_id'] !== $user_id) {
                $event['affected_range_id'] = preg_replace('/[^w]/', '#', $event['affected_range_id']);
                $log_event->affected_range_id = $event['affected_range_id'];
                $was_censored = true;
            }
            if ($event['coaffected_range_id'] && substr_count($template, '%user(%coaffected)') && $event['coaffected_range_id'] !== $user_id) {
                $event['coaffected_range_id'] = preg_replace('/[^w]/', '#', $event['coaffected_range_id']);
                $log_event->coaffected_range_id = $event['coaffected_range_id'];
                $was_censored = true;
            }

            //censore possible info
            if ($was_censored) {
                if (!empty($event['info'])) {
                    $event['info'] = preg_replace('/[^w]/', '#', $event['info']);
                }
                if (!empty($event["dbg_info"])) {
                    $event['dbg_info'] = preg_replace('/[^w]/', '#', $event['dbg_info']);
                }
            }

            $a['readable_entry'] = html_entity_decode(strip_tags(str_replace('<br>', PHP_EOL, ($log_event->formatEvent()))));
            $log[$pos]= array_merge($a, $event);
        }

        if ($log) {
            $storage->addTabularData(_('Logs'), 'log_events', $log);
        }
    }
}
