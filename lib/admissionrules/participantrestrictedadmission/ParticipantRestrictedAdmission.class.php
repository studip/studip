<?php

/**
 * ParticipantRestrictedAdmission.class.php
 *
 * Specifies restricted number of participants for course admission.
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

class ParticipantRestrictedAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * Timestamp for execution of seat distribution algorithm
     */
    public $distributionTime = null;

    public $first_come_first_served_allowed = false;

    public $minimum_timespan_to_distribution_time = 120;


    // --- OPERATIONS ---

    /**
     * Standard constructor
     *
     * @param  String ruleId
     */
    public function __construct($ruleId='', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);
        $this->first_come_first_served_allowed = (bool)Config::get()->ENABLE_COURSESET_FCFS;
        $this->default_message = _('Es stehen keine weiteren Plätze zur Verfügung.');
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('participantrestrictedadmissions');
        }
    }

    public function isFCFSallowed()
    {
        return $this->first_come_first_served_allowed;
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete() {
        if ($this->prio_exists) {
            $set_id = DBManager::get()->fetchColumn("SELECT set_id FROM courseset_rule WHERE rule_id = ? LIMIT 1", [$this->id]);
            //Delete priorities
            AdmissionPriority::unsetAllPriorities($set_id);
        }
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `participantrestrictedadmissions`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription() {
        return _("Anmelderegeln dieses Typs legen fest, ob die zugeordneten Veranstaltungen eine maximale Teilnehmendenanzahl haben. Die Platzverteilung erfolgt automatisiert.");
    }

    /**
     * Gets the time for seat distribution algorithm.
     *
     * @return int
     */
    public function getDistributionTime()
    {
        return $this->distributionTime;
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _("Beschränkte Teilnehmendenanzahl");
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
            FROM `participantrestrictedadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->message = $current['message'];
            $this->distributionTime = $current['distribution_time'];
            if ($current['distribution_time'] > 0) {
                $this->prio_exists = DBManager::get()->fetchColumn("SELECT 1 FROM courseset_rule INNER JOIN priorities USING(set_id) WHERE rule_id = ? LIMIT 1", [$this->id]);
            }
        }
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
        if ($data['distributiondate']) {
            if (!$data['distributiontime']) {
                $data['distributiontime'] = '23:59';
            }
            $ddate = strtotime($data['distributiondate'] . ' ' . $data['distributiontime']);
            $this->setDistributionTime($ddate);
        }
        if ($data['enable_FCFS']) {
            $this->setDistributionTime(0);
        }
        if ($data['startdate']) {
             $starttime = strtotime($data['startdate'] . ' ' . $data['starttime']);
             if ($starttime > time()) {
                 $this->minimum_timespan_to_distribution_time = $this->minimum_timespan_to_distribution_time + (($starttime - time()) / 60);
             }
        }

        return $this;
    }

    /**
     * Sets a new timestamp for seat distribution algorithm execution.
     *
     * @param  int newDistributionTime
     * @return TimedAdmission
     */
    public function setDistributionTime($newDistributionTime)
    {
        $this->distributionTime = $newDistributionTime;
        return $this;
    }


    /**
     * Store rule definition to database.
     */
    public function store() {
        // Store data.
        $stmt = DBManager::get()->prepare("INSERT INTO `participantrestrictedadmissions`
            (`rule_id`, `message`, `distribution_time`,
             `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            `distribution_time`=VALUES(`distribution_time`),
             message=VALUES(message), `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, (string)$this->message,
            (int)$this->distributionTime, time(), time()]);
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
        if (!$data['distributiontime']) {
            $data['distributiontime'] = '23:59';
        }
        $ddate = strtotime($data['distributiondate'] . ' ' . $data['distributiontime']);
        if (!$data['enable_FCFS'] && (!$data['distributiondate'] || $ddate < (time() + $this->minimum_timespan_to_distribution_time*60))) {
            $errors[] = sprintf(_('Bitte geben Sie für die Platzverteilung ein Datum an, das weiter in der Zukunft liegt. Das frühestmögliche Datum ist %s.'), strftime('%x %R', time() + $this->minimum_timespan_to_distribution_time*60));
        }
        if ($data['enable_FCFS'] && $data['distributiondate']) {
            $errors[] = _('Sie können kein Datum für die automatische Platzverteilung einstellen und gleichzeitig die automatische Platzverteilung ausschalten.');
        }
        return $errors;
    }

}

?>
