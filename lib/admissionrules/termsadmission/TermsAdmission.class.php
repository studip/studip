<?php
/**
 * TermsAdmission.class.php
 *
 * Represents a rule for course access with conditions of admission to be accepted.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Niklas Dettmer <ndettmer@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'lib/classes/admission/AdmissionRule.class.php';

class TermsAdmission extends AdmissionRule
{
    // Terms of admission
    public $terms;

    /**
     * Standard constructor.
     *
     * @param  String ruleId
     */
    public function __construct($ruleId = '', $courseSetId = '')
    {
        parent::__construct($ruleId, $courseSetId);

        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('termsadmissions');
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete()
    {
        parent::delete();

        $stmt = DBManager::get()->prepare('DELETE FROM termsadmissions WHERE rule_id = ?');
        $stmt->execute([$this->getId()]);
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription()
    {
        return _('Mit dieser Anmelderegel können Sie einen Kurs mit spezifischen Teilnahmebedingungen realisieren. '
               . 'Die Anmeldung ist erst möglich, nachdem diese akzeptiert wurden.');
    }

    /**
     * Shows an input form
     *
     * @return String A template-based input form.
     * @throws Flexi_TemplateNotFoundException
     */
    public function getInput()
    {
        $factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $factory->open('input');
        $template->rule = $this;

        return MessageBox::info($template->render())->hideClose(true);
    }

    /**
     * Return this rule's name.
     */
    public static function getName() {
        return _('Kurs mit Teilnahmebedingungen');
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     *
     * @return String
     * @throws Flexi_TemplateNotFoundException
     */
    public function getTemplate()
    {
        $factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $factory->open('configure');
        $template->rule = $this;

        return $template->render();
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
        $errors = [];

        // check if the user has accepted the terms
        if (!Request::int('terms_accepted')) {
            $errors[] = _('Um sich anzumelden, müssen Sie die Teilnahmebedingungen akzeptieren.');
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
    public function setAllData($data)
    {
        parent::setAllData($data);
        $this->terms = trim($data['terms']);
        return $this;
    }

    /**
     * Internal helper function for loading rule definition from database.
     */
    public function load()
    {
        $rule = DBManager::get()->fetchOne('SELECT * FROM termsadmissions WHERE rule_id = ?', [$this->getId()]);
        $this->terms = $rule['terms'];
        return $this;
    }

    /**
     * Store rule definition to database.
     */
    public function store()
    {
        // Store data.
        $stmt = DBManager::get()->prepare('INSERT INTO termsadmissions (rule_id, terms, mkdate, chdate) VALUES (?, ?, ?, ?)
                                            ON DUPLICATE KEY UPDATE terms = VALUES(terms), chdate = VALUES(chdate)');
        $stmt->execute([$this->id, $this->terms, time(), time()]);
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     * @throws Flexi_TemplateNotFoundException
     */
    public function toString()
    {
        $factory = new Flexi_TemplateFactory(__DIR__ . '/templates/');
        $template = $factory->open('info');
        $template->rule = $this;

        return $template->render();
    }
}
