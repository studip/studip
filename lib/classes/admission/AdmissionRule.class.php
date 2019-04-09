<?php

/**
 * AdmissionRule.class.php
 *
 * An abstract representation of rules for course admission.
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

abstract class AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * When does the validity end?
     */
    public $endTime = 0;

    /**
     * A unique identifier for this rule.
     */
    public $id = '';

    /**
     * A customizable message that is shown to users that are rejected for admission
     * because of the current rule.
     */
    public $message = '';

    /**
     * default message that is shown to users that are rejected for admission
     * because of the current rule.
     */
    public $default_message = '';

    /**
     * When does the validity start?
     */
    public $startTime = 0;

    /**
     * ID of the CourseSet this admission rule belongs to (is stored here for
     * performance reasons).
     */
    public $courseSetId = '';

    /**
     * courseset siblings of this rule
     */
    public $siblings = [];

    /**
     * Are siblings set manually?
     */
    public $siblings_override = false;

    // --- OPERATIONS ---

    public function __construct($ruleId = '', $courseSetId = '')
    {
        $this->id = $ruleId;
        $this->courseSetId = $courseSetId;
    }

    /**
     * Hook that can be called after the seat distribution on the courseset
     * has completed.
     *
     * @param CourseSet $courseset Current courseset.
     */
    public function afterSeatDistribution($courseset)
    {
        return true;
    }

    /**
     * Checks if we are in the rule validity time frame.
     *
     * @return True if the rule is valid because the time frame applies,
     *         otherwise false.
     */
    public function checkTimeFrame()
    {
        $valid = true;
        // Start time given, but still in the future.
        if ($this->startTime && $this->startTime > time()) {
            $valid = false;
        }
        // End time given, but already past.
        if ($this->endTime && $this->endTime < time()) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete()
    {
        // Delete rule assignment to coursesets.
        $stmt = DBManager::get()->prepare("DELETE FROM `courseset_rule`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Generate a new unique ID.
     *
     * @param  String tableName
     */
    public function generateId($tableName)
    {
        do {
            $newid = md5(uniqid(get_class($this).microtime(), true));
            $db = DBManager::get()->query("SELECT `rule_id`
                FROM `".$tableName."` WHERE `rule_id`=" . DBManager::get()->quote($newid));
        } while ($db->fetch());
        return $newid;
    }

    /**
     * Gets all users that are matched by thís rule.
     *
     * @return Array An array containing IDs of users who are matched by
     *      this rule.
     */
    public function getAffectedUsers()
    {
        return [];
    }

    /**
     * Reads all available AdmissionRule subclasses and loads their definitions.
     *
     * @param  bool $activeOnly Show only active rules.
     * @return Array
     */
    public static function getAvailableAdmissionRules($activeOnly = true)
    {
        $rules = [];
        $where = ($activeOnly ? " WHERE `active`=1" : "");
        $data = DBManager::get()->query("SELECT * FROM `admissionrules`".$where.
            " ORDER BY `id` ASC");
        while ($current = $data->fetch(PDO::FETCH_ASSOC)) {
            $className = $current['ruletype'];
            if (is_dir($GLOBALS['STUDIP_BASE_PATH'] . DIRECTORY_SEPARATOR . $current['path'])) {
                StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . DIRECTORY_SEPARATOR . $current['path']);
                try {
                    $rule = new $className();
                    $rules[$className] = [
                            'id' => $current['id'],
                            'name' => $className::getName(),
                            'description' => $className::getDescription(),
                            'active' => $current['active']
                        ];
                } catch (Exception $e) {
                }
            }
        }
        return $rules;
    }

    /**
     * Get end of validity.
     *
     * @return Integer
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Subclasses of AdmissionRule can require additional data to be entered on
     * admission (like PasswordAdmission which needs a password for course
     * access). Their corresponding method getInput only returns a HTML form
     * fragment as the output can be concatenated with output from other
     * rules.
     * This static method provides the frame for rendering a full HTML form
     * around the fragments from subclasses.
     *
     * @return Array Start and end templates which wrap input form fragments
     *               from subclasses.
     */
    public static final function getInputFrame()
    {
        return [
            $GLOBALS['template_factory']->open('admission/rules/input_start')->render(),
            $GLOBALS['template_factory']->open('admission/rules/input_end')->render()
        ];
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription()
    {
        return _("Legt eine Regel fest, die erfüllt sein muss, um sich ".
            "erfolgreich zu einer Menge von Veranstaltungen anmelden zu ".
            "können.");
    }

    public function getInput()
    {
        return '';
    }
    /**
     * Gets the rule ID.
     *
     * @return String This rule's ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the message that is shown to users rejected by this rule.
     *
     * @return String The message.
     */
    public function getMessage()
    {
        return $this->message ?: $this->default_message;
    }

    /**
     * Return this rule's name.
     */
    public static function getName()
    {
        return _("Anmelderegel");
    }

    /**
     * Gets start of validity.
     *
     * @return Integer
     */
    public function getStartTime()
    {
       return $this->startTime;
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     *
     * @return String
     */
    public function getTemplate()
    {
        return '';
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load()
    {
    }

    /**
     * Hook that can be called when the seat distribution on the courseset
     * starts.
     *
     * @param CourseSet The courseset this rule belongs to.
     */
    public function beforeSeatDistribution($courseset)
    {
        return true;
    }

    /**
     * Does the current rule allow the given user to register as participant
     * in the given course?
     *
     * @param  String userId
     * @param  String courseId
     * @return Array
     */
    public function ruleApplies($userId, $courseId)
    {
        return [];
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
        if ($data['start_date'] && !$data['start_time']) {
            $data['start_time'] = strtotime($data['start_date']);
        }
        if ($data['end_date'] && !$data['end_time']) {
            $data['end_time'] = strtotime($data['end_date'] . ' 23:59:59');
        }
        $this->message = $data['message'];
        $this->startTime = $data['start_time'];
        $this->endTime = $data['end_time'];
        return $this;
    }

    /**
     * Sets a new end time for condition validity.
     *
     * @param  Integer newEndTime
     * @return UserFilter
     */
    public function setEndTime($newEndTime)
    {
        $this->endTime = $newEndTime;
        return $this;
    }

    /**
     * Sets a new message to show to users.
     *
     * @param  String newMessage A new message text.
     * @return AdmissionRule This object
     */
    public function setMessage($newMessage)
    {
        $this->message = $newMessage;
        return $this;
    }

    /**
     * Sets a new start time for condition validity.
     *
     * @param  Integer newStartTime
     * @return UserFilter
     */
    public function setStartTime($newStartTime)
    {
        $this->startTime = $newStartTime;
        return $this;
    }

    /**
     * Helper function for storing rule definition to database.
     */
    public function store()
    {
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString()
    {
        return '';
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
        $errors = [];
        if ($data['start_date'] && $data['end_date'] && strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $errors[] = _('Das Enddatum darf nicht vor dem Startdatum liegen.');
        }
        return $errors;
    }

    /**
     * Standard string representation of this object.
     *
     * @return String
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * load sibling rules
     *
     */
    public function loadSiblings()
    {
        if ($this->siblings_override) {
            return false;
        }
        $this->siblings = [];
        if ($this->courseSetId != '') {
            $cs = new CourseSet($this->courseSetId);
            foreach ($cs->getAdmissionRules() as $rule_id => $rule) {
                if ($rule->getId() != $this->id) {
                    $this->siblings[$rule_id] = $rule;
                }
            }
        }
    }

    /**
     * get sibling rules
     *
     */
    public function getSiblings()
    {
        $this->loadSiblings();
        return $this->siblings;
    }

    /**
     * set sibling rules
     *
     */
    public function setSiblings($siblings = [])
    {
        $this->siblings_override = true;
        $this->siblings = $siblings;
    }

    /**
     * checks if given admission rule is allowed to be combined with this rule
     *
     * @param AdmissionRule|string $admission_rule
     * @return boolean
     */
    public function isCombinationAllowed($admission_rule)
    {
        if (is_object($admission_rule)) {
            $admission_rule = get_class($admission_rule);
        }
        return AdmissionRuleCompatibility::exists([get_class($this), $admission_rule]);
    }

    public function __clone()
    {
        $this->id = md5(uniqid(get_class($this)));
        $this->courseSetId = null;
    }
} /* end of abstract class AdmissionRule */
