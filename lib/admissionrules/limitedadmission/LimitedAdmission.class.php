<?php

/**
 * LimitedAdmission.class.php
 *
 * Represents rules for admission to a limited number of courses.
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

class LimitedAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---


    /**
     * Maximal number of courses that a user can register for.
     */
    public $maxNumber = 1;

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId
     * @return LimitedAdmission
     */
    public function __construct($ruleId='', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);
        $this->default_message = _('Sie haben sich bereits zur maximalen Anzahl von %s Veranstaltungen angemeldet.');

        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('limitedadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `limitedadmissions`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
        // Delete all custom max numbers.
        $stmt = DBManager::get()->prepare("DELETE FROM `userlimits`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Users can specify their own maximal number of courses they want
     * to be registered for. This method gets the specified value for the
     * given user or the max number that has been specified  by the rule if no
     * custom number was set.
     *
     * @param  userId
     * @return Integer
     */
    public function getCustomMaxNumber($userId)
    {
        // Initially we use the number given per admission rule.
        $maxNumber = $this->maxNumber;
        $stmt = DBManager::get()->prepare("SELECT `maxnumber`
            FROM `userlimits` WHERE rule_id=? AND user_id=?");
        $stmt->execute([$this->id, $userId]);
        // The user has given some custom number.
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Custom number must be smaller than rule max number.
            $maxNumber = min($maxNumber, $current['maxnumber']);
        }
        return $maxNumber;
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription() {
        return _("Diese Art von Anmelderegel legt eine Maximalzahl von ".
            "Veranstaltungen fest, an denen Nutzer im aktuellen ".
            "Anmeldeset teilnehmen können.");
    }

    /**
     * Gets the maximal number of courses that users can be registered for.
     *
     * @return Integer
     */
    public function getMaxNumber()
    {
        return (int)$this->maxNumber;
    }

    public function getMaxNumberForUser($userId)
    {
        return min($this->maxNumber, $this->getCustomMaxNumber($userId));
    }


    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Anmeldung zu maximal n Veranstaltungen");
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
        $tpl2->set_attribute('tpl', $tpl->render());
        return $tpl2->render();
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load() {
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `limitedadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->startTime = $current['start_time'];
            $this->endTime = $current['end_time'];
            $this->maxNumber = $current['maxnumber'];
        }
    }

    /**
     * Does the current rule allow the given user to register as participant
     * in the given course? That only happens when the user has no more than
     * the given number of registrations at the other courses in the course set.
     *
     * @param  String userId
     * @param  String courseId
     * @return Array Any errors that occurred on admission.
     */
    public function ruleApplies($userId, $courseId)
    {
        $errors = [];
        // Check for rule validity time frame.
        if ($this->checkTimeFrame()) {
            // How many courses from this set has the user already registered for?
            $db = DBManager::get();
            $number = $db->fetchColumn("SELECT COUNT(*)
                FROM `seminar_user` WHERE `user_id`=? AND `status` IN ('user', 'autor') AND `Seminar_id` IN (
                    SELECT `Seminar_id` FROM `seminar_courseset` WHERE `set_id`=?)", [$userId, $this->courseSetId]);
            $number += $db->fetchColumn("SELECT COUNT(*)
                FROM `admission_seminar_user` WHERE `user_id`=? AND `Seminar_id` IN (
                    SELECT `Seminar_id` FROM `seminar_courseset` WHERE `set_id`=?)", [$userId, $this->courseSetId]);
            // Check if the number is smaller than admission rule limit
            if (!($number <
                    $this->getMaxNumber())) {
                $errors[] = $this->getMessage($this->getMaxNumber());
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
        $this->maxNumber = intval($data['maxnumber']);
        return $this;
    }

    /**
     * Sets a new maximal number of courses that the given user can
     * register for.
     *
     * @param  String userId
     * @param  Integer maxNumber
     * @return LimitedAdmission
     */
    public function setCustomMaxNumber($userId, $maxNumber)
    {
        $stmt = DBManager::get()->prepare("INSERT INTO `userlimits`
            (`rule_id`, `user_id`, `maxnumber`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `maxnumber`=VALUES(`maxnumber`), `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $userId,
            min($this->maxNumber, $maxNumber), time(), time()]);
        return $this;
    }

    /**
     * Sets a new maximal number of courses for registration of the same user.
     *
     * @param  Integer newMaxNumber
     * @return LimitedAdmission
     */
    public function setMaxNumber($newMaxNumber)
    {
        $this->maxNumber = $newMaxNumber;
        return $this;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `limitedadmissions`
            (`rule_id`, `message`, `start_time`, `end_time`, `maxnumber`,
                `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `message`=VALUES(`message`), `start_time`=VALUES(`start_time`),
            `end_time`=VALUES(`end_time`), `maxnumber`=VALUES(`maxnumber`),
            `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->message, (int)$this->startTime,
            (int)$this->endTime, $this->maxNumber, time(), time()]);
        return $this;
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString() {
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
        if (!$data['maxnumber']) {
            $errors[] = _('Bitte geben Sie die maximale Anzahl erlaubter Anmeldungen an.');
        }
        return $errors;
    }

    public function getMessage($max_number = null)
    {
        $message = parent::getMessage();
        if (isset($max_number)) {
            return sprintf($message, $max_number);
        } else {
            return $message;
        }
    }
} /* end of class LimitedAdmission */

?>
