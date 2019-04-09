<?php

/**
 * TimedAdmission.class.php
 *
 * Specifies a time frame for course admission.
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

class TimedAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * End of course admission.
     */
    public $endTime = 0;

    /**
     * Start of course admission.
     */
    public $startTime = 0;

    // --- OPERATIONS ---

    /**
     * Standard constructor
     *
     * @param  String ruleId
     */
    public function __construct($ruleId='', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);
        $this->default_message = _('Sie befinden sich nicht innerhalb des Anmeldezeitraums.');
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('timedadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `timedadmissions`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription() {
        return _("Anmelderegeln dieses Typs legen ein Zeitfenster fest, in ".
            "dem die Anmeldung zu Veranstaltungen möglich ist. Es kann auch ".
            "nur ein Start- oder Endzeitpunkt angegeben werden.");
    }

    /**
     * Gets the end of course admission.
     *
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Zeitgesteuerte Anmeldung");
    }

    /**
     * Gets the start of course admission.
     *
     * @return int
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
    public function getTemplate() {
        $factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates/');
        // Open specific template for this rule and insert base template.
        $tpl = $factory->open('configure');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Helper function for loading rule definition from database.
     */
    public function load() {
        // Load data.
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `timedadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->startTime = $current['start_time'];
            $this->endTime = $current['end_time'];
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
        if (!$this->checkTimeFrame()) {
            $errors[] = $this->getMessage();
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
        if ($data['startdate']) {
            $sdate = $data['startdate'];
            $stime = $data['starttime'];
            $parsed = date_parse($sdate.' '.$stime);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $this->setStartTime($timestamp);
        }
        if ($data['enddate']) {
            $edate = $data['enddate'];
            $etime = $data['endtime'];
            if (!$etime) {
                $etime = '23:59';
            }
            $parsed = date_parse($edate.' '.$etime);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $this->setEndTime($timestamp);
        }
        return $this;
    }

    /**
     * Sets a new end timestamp for course admission.
     *
     * @param  int newEndTime
     * @return TimedAdmission
     */
    public function setEndTime($newEndTime)
    {
        $this->endTime = $newEndTime;
        return $this;
    }

    /**
     * Sets a new start timestamp for course admission.
     *
     * @param  int newStartTime
     * @return TimedAdmission
     */
    public function setStartTime($newStartTime)
    {
        $this->startTime = $newStartTime;
        return $this;
    }

    /**
     * Store rule definition to database.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `timedadmissions`
            (`rule_id`, `message`, `start_time`,
            `end_time`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `start_time`=VALUES(`start_time`),
            `end_time`=VALUES(`end_time`),message=VALUES(message), `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->message, (int)$this->startTime,
            (int)$this->endTime, time(), time()]);
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
        if (!$data['startdate'] && !$data['enddate']) {
            $errors[] = _('Bitte geben Sie entweder ein Start- oder Enddatum an.');
        }
        if ($data['startdate'] && $data['enddate'] && strtotime($data['enddate'] . ' ' . $data['endtime']) < strtotime($data['startdate']. ' ' . $data['starttime'])) {
            $errors[] = _('Das Enddatum darf nicht vor dem Startdatum liegen.');
        }
        return $errors;
    }

} /* end of class TimedAdmission */

?>
