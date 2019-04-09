<?php

/**
 * Admission_RuleController - Admission rules
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class Admission_RuleController extends AuthenticatedController
{
    /**
     * @see AuthenticatedController::before_filter
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['perm']->have_perm('admin') || ($GLOBALS['perm']->have_perm('dozent') && get_config('ALLOW_DOZENT_COURSESET_ADMIN'))) {
            Navigation::activateItem('/tools/coursesets');
        }
        PageLayout::setTitle(_('Anmeldesets'));

    }

    /**
     * Gets the template for the rule configuration form.
     *
     * @param String $ruleType Class name of the rule to configure.
     * @param String $ruleId   Optional ID of an existing rule.
     */
    public function configure_action($ruleType = '', $ruleId = '')
    {
        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules();
        UserFilterField::getAvailableFilterFields();
        $this->ruleType = $ruleType;
        // Check if rule data has been given via request.
        if (Request::getArray('rules')) {
            $rule_siblings = [];
            foreach (Request::getManyObjects('rules', 'AdmissionRule') as $rule) {
                if ($ruleType == get_class($rule) && $rule->getId() == Request::get('ruleId')) {
                    $this->rule = $rule;
                } else {
                    $rule_siblings[$rule->getId()] = $rule;
                }
            }
            if (!$this->rule && in_array($ruleType, array_keys($this->ruleTypes))) {
                $this->rule = new $ruleType($ruleId);
            }
            $this->rule->setSiblings($rule_siblings);
        } elseif (Request::get('rule')) {
            $rule = Request::getObject('rule', 'AdmissionRule');
            if ($ruleType == get_class($rule)) {
                $this->rule = $rule;
            }
        } elseif (in_array($ruleType, array_keys($this->ruleTypes))) {
            $this->rule = new $ruleType($ruleId);
        }
        if ($this->rule) {
            $this->ruleTemplate = $this->rule->getTemplate();
        }
    }

    /**
     * Shows a form for selecting which rule type to use.
     *
     * @param String $cs_id ID of a courseset the rule shall belong to.
     */
    public function select_type_action($cs_id = '')
    {
        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules();
        $this->courseset = new CourseSet($cs_id);
        $this->courseset->clearAdmissionRules();
        foreach (Request::getManyObjects('rules', 'AdmissionRule') as $rule) {
            $this->courseset->addAdmissionRule($rule);
        }
    }

    /**
     * Saves the given rule.
     *
     * @param String $ruleType The class name of the configured rule.
     * @param String $ruleId   ID of the rule to save, or empty if this is a new rule.
     */
    public function save_action($ruleType, $ruleId = '')
    {
        CSRFProtection::verifyUnsafeRequest();
        $rules = AdmissionRule::getAvailableAdmissionRules();
        $this->rule = new $ruleType($ruleId);
        $requestData = Request::getInstance();
        // Check for start and end date and parse the String values to timestamps.
        if ($requestData['start_date'] ) {
            $parsed = date_parse($requestData['start_date']);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0,
                $parsed['month'], $parsed['day'], $parsed['year']);
            $requestData['start_time'] = $timestamp;
        }
        if ($requestData['end_date'] ) {
            $parsed = date_parse($requestData['end_date']);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0,
                $parsed['month'], $parsed['day'], $parsed['year']);
            $requestData['end_time'] = $timestamp;
        }
        $this->rule->setAllData($requestData);
    }

    /**
     * Validates if the values given in the current request are sufficient to
     * configure a rule of the given type.
     *
     * @param String $ruleType Class name of the rule to check.
     */
    public function validate_action($ruleType)
    {
        $rules = AdmissionRule::getAvailableAdmissionRules();
        $rule = new $ruleType();
        $this->errors = $rule->validate(Request::getInstance());
    }

}
