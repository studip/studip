<?php

/**
 * ConditionalAdmission.class.php
 *
 * An admission rule that specifies conditions for course admission, like
 * degree, study course or semester.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class ConditionalAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * All conditions that must be fulfilled for successful admission.
     */
    public $conditions = [];

    /**
     * Grouped conditions that must be fulfilled for successful admission.
     */
    public $conditiongroups = [];

    /**
     * Conditions that must be fulfilled for successful admission.
     */
    public $ungrouped_conditions = [];

    /**
     * Quota for grouped conditions
     */
    public $quota = [];

    /**
     * Are condition groups allowed?
     */
    public $conditiongroups_allowed = null;

    /**
     * courseset siblings of this rule
     */
    public $siblings = [];

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId If this rule has been saved previously, it
     *      will be loaded from database.
     * @return AdmissionRule the current object (this).
     */
    public function __construct($ruleId='', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);
        $this->default_message = _("Sie erfüllen nicht die Bedingung: %s");
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('conditionaladmissions');
        }
        return $this;
    }

    /**
     * Adds a new UserFilter to this rule.
     *
     * @param  UserFilter condition
     * @param  String group
     * @param  Int quota
     * @return ConditionalAdmission
     */
    public function addCondition($condition, $group = '', $quota = 0)
    {
        if ($group) {
            $this->conditiongroups[$group][$condition->getId()] = $condition;
            $this->quota[$group] = $quota;
        } else {
            $this->ungrouped_conditions[$condition->getId()] = $condition;
        }
        return $this;
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete()
    {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `conditionaladmissions`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
        // Delete all associated conditions...
        foreach ($this->ungrouped_conditions as $condition) {
            $condition->delete();
        }
        foreach ($this->conditiongroups as $conditions) {
            foreach ($conditions as $condition) {
                $condition->delete();
            }
        }
        // ... and their connection to this rule.
        $stmt = DBManager::get()->prepare("DELETE `admission_condition`, `admission_conditiongroup` FROM `admission_condition`
            LEFT JOIN `admission_conditiongroup` USING( `conditiongroup_id`)
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Gets all users that are matched by thís rule.
     *
     * @return Array An array containing IDs of users who are matched by
     *      this rule.
     */
    public function getAffectedUsers()
    {
        $users = [];
        foreach ($this->ungrouped_conditions as $condition) {
            $users = array_intersect($users, $condition->getAffectedUsers());
        }
        foreach ($this->conditiongroups as $conditions) {
            foreach ($conditions as $condition) {
                $users = array_intersect($users, $condition->getAffectedUsers());
            }
        }
        return $users;
    }

    /**
     * Gets all defined conditions.
     *
     * @return Array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Gets all grouped conditiongroups.
     *
     * @return Array
     */
    public function getConditionGroups()
    {
        return $this->conditiongroups;
    }

    /**
     * Gets all grouped conditiongroups.
     *
     * @return Array
     */
    public function getUngroupedConditions()
    {
        return $this->ungrouped_conditions;
    }

    /**
     * Gets quota for given conditiongroup.
     *
     * @return Array
     */
    public function getQuota($group_id)
    {
        return $this->quota[$group_id];
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription()
    {
        return _('Über eine Menge von Bedingungen kann festgelegt werden, '.
            'wer zur Anmeldung zu den Veranstaltungen des Anmeldesets '.
            'zugelassen ist. Es muss nur eine der Bedingungen erfüllt sein, '.
            'innerhalb einer Bedingung müssen aber alle Datenfelder '.
            'zutreffen.');
    }

    /**
     * Return this rule's name.
     */
    public static function getName()
    {
        return _('Bedingte Anmeldung');
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     *
     * @return String
     */
    public function getTemplate()
    {
        // Open generic admission rule template.
        $tpl = $GLOBALS['template_factory']->open('admission/rules/configure');
        $tpl->set_attribute('rule', $this);
        $factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates/');
        // Now open specific template for this rule and insert base template.
        $tpl2 = $factory->open('configure');
        $tpl2->set_attribute('rule', $this);
        $tpl2->set_attribute('tpl', $tpl->render());
        return $tpl2->render();
    }

    /**
     * Helper function for loading data from DB. Generic AdmissionRule data is
     * loaded with the parent load() method.
     */
    public function load()
    {
        // Load basic data.
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `conditionaladmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->startTime = $current['start_time'];
            $this->endTime = $current['end_time'];
            // Retrieve conditions.
            $stmt = DBManager::get()->prepare("SELECT *
                FROM `admission_condition` LEFT JOIN `admission_conditiongroup` USING (`conditiongroup_id`) WHERE `rule_id`=?");
            $stmt->execute([$this->id]);
            $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($conditions as $condition) {
                $currentCondition = new UserFilter($condition['filter_id']);
                if ($condition['conditiongroup_id']) {
                    $this->conditiongroups[$condition['conditiongroup_id']][$condition['filter_id']] = $currentCondition;
                    $this->quota[$condition['conditiongroup_id']] = $condition['quota'];
                } else {
                    $this->ungrouped_conditions[$condition['filter_id']] = $currentCondition;
                }
            }
        }
    }

    /**
     * Checks if condition groups are allowed.
     *
     * @return Boolean
     */
    public function conditiongroupsAllowed()
    {
        if ($this->conditiongroups_allowed === null) {
            foreach ($this->getSiblings() as $rule) {
                if (get_class($rule) == 'ParticipantRestrictedAdmission') {
                    if ($rule->getDistributionTime() > time()) {
                        $this->conditiongroups_allowed = true;
                    }
                }
            }
        }
        return $this->conditiongroups_allowed;
    }

    /**
     * Removes condition groups and sets all conditions as ungrouped.
     *
     */
    public function removeConditionGroups()
    {
       foreach ($this->conditiongroups as $conditiongroup_id => $conditions) {
           foreach ($conditions as $condition_id => $condition) {
               $this->ungrouped_conditions[$condition_id] = $condition;
           }
       }
       $this->conditiongroups = [];
    }

    /**
     * Removes the condition with the given ID from the rule.
     *
     * @param  String conditionId
     * @return ConditionalAdmission
     */
    public function removeCondition($conditionId)
    {
        if (isset($this->ungrouped_conditions[$conditionId])) {
            $this->ungrouped_conditions[$conditionId]->delete();
            unset($this->ungrouped_conditions[$conditionId]);
        } else {
            foreach ($this->conditiongroups as $conditiongroup_id => $conditions) {
                if (isset($conditions[$conditionId])) {
                    $this->conditiongroups[$conditiongroup_id]->conditions[$conditionId]->delete();
                    unset($this->conditiongroups[$conditiongroup_id]->conditions[$conditionId]);
                }
            }
        }
        return $this;
    }

    /**
     * Checks whether the given user fulfills the configured
     * admission conditions. Only one of the conditions needs to be
     * fulfilled (logical operator OR). The fields in a condition are
     * in conjunction (logical operator AND).
     *
     * @param String $userId
     * @param String $courseId
     * @return Array Array with conditions that have failed. If array
     *               is empty, everything's all right.
     */
    public function ruleApplies($userId, $courseId)
    {
        $failed = [];
        // Check for rule validity time frame.
        if ($this->checkTimeFrame()) {
            // Check all configured conditions.
            foreach ($this->ungrouped_conditions as $condition) {
                if (!$condition->isFulfilled($userId)) {
                    $failed[] = $this->getMessage($condition->toString());
                } else {
                    $failed = [];
                    break;
                }
            }
            foreach ($this->conditiongroups as $conditions) {
                foreach ($conditions as $condition) {
                    if (!$condition->isFulfilled($userId)) {
                        $failed[] = $this->getMessage($condition->toString());
                    } else {
                        $failed = [];
                        break 2;
                    }
                }
            }
        } else {
            $failed = [];
        }
        return $failed;
    }

    /**
     * Uses the given data to fill the object values. This can be used
     * as a generic function for storing data if the concrete rule type
     * isn't known in advance.
     *
     * @param Array $data
     * @return AdmissionRule This object.
     */
    public function setAllData($data)
    {
        UserFilterField::getAvailableFilterFields();
        parent::setAllData($data);
        $this->conditions = [];
        $this->ungrouped_conditions = [];
        $this->conditiongroups = [];
        $this->quota = [];
        foreach ($data['conditions'] as $ser_con) {
            $condition = ObjectBuilder::build($ser_con, 'UserFilter');
            $this->addCondition($condition, $data['conditiongroup_'.$condition->getId()], $data['quota_'.$data['conditiongroup_'.$condition->getId()]]);
        }
        foreach ($this->getConditiongroups() as $conditiongroup_id => $conditions) {
            if (mb_strlen($conditiongroup_id) < 32) {
                $group = md5(uniqid('conditiongroups' . microtime(), true));

                $this->conditiongroups[$group] = $this->conditiongroups[$conditiongroup_id];
                unset($this->conditiongroups[$conditiongroup_id]);

                $this->quota[$group] = $this->quota[$conditiongroup_id];
                unset($this->quota[$conditiongroup_id]);
            }
        }
        if (count($this->getConditiongroups()) && $data['conditiongroups_allowed']) {
            $this->conditiongroups_allowed = true;
        }

        return $this;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store()
    {
        // Store rule data.
        $stmt = DBManager::get()->prepare("INSERT INTO `conditionaladmissions`
            (`rule_id`, `message`, `start_time`, `end_time`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `message`=VALUES(`message`),
            `start_time`=VALUES(`start_time`),
            `end_time`=VALUES(`end_time`),
            `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->message, (int)$this->startTime,
            (int)$this->endTime, time(), time()]);
        // prepare condition data.
        $keys = array_keys($this->ungrouped_conditions);
        foreach ($this->conditiongroups as $conditiongroup_id => $conditions) {
            $keys = array_merge($keys, array_keys($conditions));
        }

        // Delete removed conditions from DB.
        $stmt = DBManager::get()->prepare("SELECT `filter_id`, `conditiongroup_id` FROM
            `admission_condition` WHERE `rule_id`=? AND `filter_id` NOT IN ('".
                implode("', '", $keys)."')");
        $stmt->execute([$this->id]);
        $groups = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $entry) {
            $current = new UserFilter($entry['filter_id']);
            $current->delete();
            $groups[] = $entry['conditiongroup_id'];
        }
        DBManager::get()->exec("DELETE FROM `admission_condition`
            WHERE `rule_id`='".$this->id."' AND `filter_id` NOT IN ('".
                    implode("', '", $keys)."')");
        // Store all conditions.
        $queries = [];
        $parameters = [];
        $groupqueries = [];
        $groupparameters = [];
        foreach ($this->ungrouped_conditions as $condition) {
            // Store each ungrouped condition...
            $condition->store();
            $queries[] = "(?, ?, ?, ?)";
            $parameters[] = $this->id;
            $parameters[] = $condition->getId();
            $parameters[] = '';
            $parameters[] = time();
        }

        foreach ($this->conditiongroups as $conditiongroup_id => $conditions) {
            $groupqueries[] = "(?, ?)";
            $groupparameters[] = $conditiongroup_id;
            $groupparameters[] = $this->quota[$conditiongroup_id];
            foreach ($conditions as $condition) {
                // Store each group of conditions...
                $condition->store();
                $queries[] = "(?, ?, ?, ?)";
                $parameters[] = $this->id;
                $parameters[] = $condition->getId();
                $parameters[] = $conditiongroup_id;
                $parameters[] = time();
            }
        }

        // Store all assignments between rule and condition.
        if (count($queries) > 0) {
            $stmt = DBManager::get()->prepare("INSERT INTO `admission_condition`
                (`rule_id`, `filter_id`, `conditiongroup_id`, `mkdate`)
                VALUES " . implode(',', $queries) . " ON DUPLICATE KEY UPDATE
                `filter_id`=VALUES(`filter_id`), `conditiongroup_id`=VALUES(`conditiongroup_id`)");
            $stmt->execute($parameters);
        }

        // Store all assignments between condition and conditiongroup.
        if (count($groupqueries) > 0) {
            $stmt = DBManager::get()->prepare("INSERT INTO `admission_conditiongroup`
                (`conditiongroup_id`, `quota`)
                VALUES " . implode(',', $groupqueries) . " ON DUPLICATE KEY UPDATE
                `quota`=VALUES(`quota`)");
            $stmt->execute($groupparameters);
        }

        return $this;
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString()
    {
        $factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates/');
        $tpl = $factory->open('info');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Validates if the given request data is sufficient to configure this rule
     * (e.g. if required values are present).
     *
     * @param  Array Request data
     * @return Array Error messages.
     */
    public function validate($data)
    {
        $errors = parent::validate($data);
        if (!$data['conditions'] || !is_array($data['conditions'])) {
            $errors[] = _('Es muss mindestens eine Auswahlbedingung angegeben werden.');
            return $errors;
        }
        $quota = [];
        $quota_total = 0;
        $grouped = 0;
        $ungrouped = 0;
        $no_quota = 0;
        foreach ($data['conditions'] as $ser_con) {
            $condition = ObjectBuilder::build($ser_con, 'UserFilter');
            if ($data['conditiongroup_'.$condition->getId()]) {
                $grouped++;
            } else {
                $ungrouped++;
            }
            $quota[$data['conditiongroup_' . $condition->getId()]] = $data['quota_' . $data['conditiongroup_' . $condition->getId()]];
            if (!$quota[$data['conditiongroup_' . $condition->getId()]]) {
                $no_quota++;
            }
        }
        foreach ($quota as $id => $part) {
            $quota_total += $part;
        }
        if ($grouped && $ungrouped) {
            $errors[] = sprintf(_('Es müssen entweder alle Bedingungen Teil einer Gruppe sein, oder keine. %s Bedingungen sind keiner Gruppe zugeordnet.'), $ungrouped);
        } elseif ($grouped && $quota_total !== 100) {
            $errors[] = _('Die Gesamtsumme der Kontingente muss 100 Prozent betragen.');
        } elseif ($grouped && $no_quota) {
            $errors[] = sprintf(_('Für %s Gruppen muss noch ein Kontingent festgelegt werden.'), $no_quota);
        }
        return $errors;
    }

    public function getMessage($condition = null)
    {
        $message = parent::getMessage();
        if ($condition) {
            return sprintf($message, $condition);
        } else {
            return $message;
        }
    }

    public function __clone()
    {
        $this->id = md5(uniqid(get_class($this)));
        $this->courseSetId = null;
        $cloned_conditions = [];
        foreach ($this->ungrouped_conditions as $condition) {
            $dolly = clone $condition;
            $cloned_conditions[$dolly->id] = $dolly;
        }
        $this->ungrouped_conditions = $cloned_conditions;
        $cloned_conditiongroups = [];
        $cloned_quota = [];
        foreach ($this->conditiongroups as $conditiongroup_id => $conditions) {
            $cloned_conditiongroup_id = md5(uniqid($conditiongroup_id));
            $cloned_quota[$cloned_conditiongroup_id] = $this->quota[$conditiongroup_id];
            foreach ($conditions as $condition) {
                $dolly = clone $condition;
                $cloned_conditiongroups[$cloned_conditiongroup_id][$dolly->id] = $dolly;
            }
        }
        $this->conditiongroups = $cloned_conditiongroups;
        $this->quota = $cloned_quota;
    }

    public function setSiblings($siblings = [])
    {
        parent::setSiblings($siblings);
        $this->conditiongroups_allowed = null;
    }

} /* end of class ConditionalAdmission */

