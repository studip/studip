<?php

/**
 * CourseMemberAdmission.class.php
 *
 * Specifies a mandatory course membership for course admission.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class CourseMemberAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * End of course admission.
     */
    public $mandatory_course_id = '';
    public $modus = '';
    public $default_message1 = '';

    // --- OPERATIONS ---

    /**
     * Standard constructor
     *
     * @param  String ruleId
     */
    public function __construct($ruleId='', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);
        $this->default_message = _('Sie sind nicht in der Veranstaltung "%s" eingetragen.');
        $this->default_message1 = _('Sie dürfen nicht in der Veranstaltung "%s" eingetragen sein.');
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('coursememberadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `coursememberadmissions`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription() {
        return _("Anmelderegeln dieses Typs legen eine Veranstaltung fest, in der die Nutzer bereits eingetragen sein müssen, oder in der sie nicht eingetragen sein dürfen, um sich zu Veranstaltungen des Anmeldesets anmelden zu können.");
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Veranstaltungsbezogene Anmeldung");
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     *
     * @return String
     */
    public function getTemplate() {
        // Open generic admission rule template.
        $tpl = $GLOBALS['template_factory']->open('admission/rules/configure');
        $tpl->set_attribute('rule', $this);
        $factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates/');
        // Now open specific template for this rule and insert base template.

        $tpl2 = $factory->open('configure');
        $tpl2->set_attribute('rule', $this);
        $tpl2->set_attribute('mandatory_course', Course::find($this->mandatory_course_id));
        $tpl2->set_attribute('tpl', $tpl->render());
        return $tpl2->render();
    }

    /**
     * Helper function for loading rule definition from database.
     */
    public function load() {
        // Load data.
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `coursememberadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetchOne()) {
            $this->message = $current['message'];
            $this->startTime = $current['start_time'];
            $this->endTime = $current['end_time'];
            $this->mandatory_course_id = $current['course_id'];
            $this->modus = $current['modus'];
        }
    }

    /**
     * Is admission allowed according to the defined time frame?
     *
     * @param  String userId
     * @param  String courseId
     * @return Array
     */
    public function ruleApplies($userId, $courseId) {
        $errors = [];
        if ($this->checkTimeFrame()) {
            $user = User::find($userId);
            $is_member = $user->course_memberships->findOneBy('seminar_id', $this->mandatory_course_id);
            if ((!$this->modus && !$is_member) || ($this->modus && $is_member)) {
                $errors[] = $this->getMessage(Course::find($this->mandatory_course_id));
            }
        }
        return $errors;
    }

    /**
     * Uses the given data to fill the object values. This can be used
     * as a generic function for storing data if the concrete rule type
     * isn't known in advance.
     *
     * @param Array $data
     * @return AdmissionRule This object.
     */
    public function setAllData($data) {
        parent::setAllData($data);
        $this->mandatory_course_id = $data['mandatory_course_id'] ?: $data['mandatory_course_id_old'];
        $this->modus = $data['modus'];
        return $this;
     }

    /**
     * Store rule definition to database.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `coursememberadmissions`
            (`rule_id`, `message`, `course_id`, `modus`, `start_time`,
            `end_time`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `start_time`=VALUES(`start_time`),
            `end_time`=VALUES(`end_time`),message=VALUES(message),course_id=VALUES(course_id),modus=VALUES(modus), `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->message,$this->mandatory_course_id, (int)$this->modus, (int)$this->startTime,
            (int)$this->endTime,  time(), time()]);
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
        if (!($data['mandatory_course_id'] || $data['mandatory_course_id_old'])) {
            $errors[] = _('Bitte wählen Sie eine Veranstaltung aus.');
        }
        return $errors;
    }

    public function getMessage($course = null)
    {
        $message = parent::getMessage();
        if ($course) {
            return sprintf($message, $course->getFullname('number-name'));
        } else {
            return $message;
        }
    }

}
