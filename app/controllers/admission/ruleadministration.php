<?php

/**
 * Admission_RuleAdministrationController - Global administration
 * of available admission rules
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

class Admission_RuleAdministrationController extends AuthenticatedController
{
    /**
     * @see AuthenticatedController::before_filter
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');

        Navigation::activateItem('/admin/config/admissionrules');
        PageLayout::addScript('studip-admission.js');

        $sidebar = Sidebar::Get();
        $sidebar->setTitle(PageLayout::getTitle() ?: _('Anmelderegeln'));
        //$sidebar->setImage('sidebar/roles-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(_('Installierte Anmelderegeln'),
            $this->url_for('admission/ruleadministration'))
            ->setActive($action === 'index');
        $views->addLink(_('Regelkompatibilität'),
            $this->url_for('admission/ruleadministration/compatibility'))
            ->setActive($action === 'compatibility');
        $sidebar->addWidget($views);
    }

    /**
     * Show overview of available admission rules.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung von Anmelderegeln'));

        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules(false);
        // Available rule classes.
        $ruleClasses = array_map(function($s) { return mb_strtolower($s); }, array_keys($this->ruleTypes));
        // Found directories with rule definitions.
        $ruleDirs = array_map(function($s) { return basename($s); }, glob($GLOBALS['STUDIP_BASE_PATH'].'/lib/admissionrules/*', GLOB_ONLYDIR));
        // Compare the two.
        $this->newRules = array_diff($ruleDirs, $ruleClasses);
    }

    public function compatibility_action()
    {
        PageLayout::setTitle(_('Anmelderegelkompatibilität'));

        $this->ruletypes = AdmissionRule::getAvailableAdmissionRules(false);
        $this->matrix = AdmissionRuleCompatibility::getCompatibilityMatrix();
    }

    /**
     * Shows where the given admission rule is activated (system wide or
     * only at specific institutes).
     *
     * @param String $ruleType Class name of the rule type to check.
     */
    public function check_activation_action($ruleType)
    {
        PageLayout::setTitle(_('Verfügbarkeit der Anmelderegel'));
        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules(false);
        $this->type = $ruleType;
        $stmt = DBManager::get()->prepare("SELECT ai.`institute_id`
            FROM `admissionrule_inst` ai
            JOIN `admissionrules` r ON (ai.`rule_id`=r.`id`)
            WHERE r.`ruletype`=?");
        $stmt->execute([$ruleType]);
        $this->activated = [];
        $globally = true;
        $atInst = false;
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($globally) $globally = false;
            if (!$atInst) $atInst = true;
            $institute = new Institute($current['institute_id']);
            $this->activated[$current['institute_id']] = $institute->name;
        }
        $this->globally = $globally;
        $this->atInst = $atInst;
    }

    /**
     * (De-)Activates the given rule type for system wide usage.
     *
     * @param  String $ruleType the class name of the rule type to activate.
     */
    public function activate_action($ruleType)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (Request::submitted('submit')) {
            $success = false;
            $stmt = DBManager::get()->prepare("UPDATE `admissionrules` SET `active`=? WHERE `ruletype`=?");
            $success = $stmt->execute([(bool) Request::get('enabled'), $ruleType]);
            // Get corresponding rule id.
            $stmt = DBManager::get()->prepare("SELECT `id` FROM `admissionrules` WHERE `ruletype`=? LIMIT 1");
            $success = $stmt->execute([$ruleType]);
            if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (Request::get('enabled')) {
                    $stmt = DBManager::get()->prepare("DELETE FROM `admissionrule_inst`
                        WHERE `rule_id` IN (SELECT `id` FROM `admissionrules` WHERE `ruletype`=?);");
                    $success = $stmt->execute([$ruleType]);
                    if (Request::get(activated) == 'inst') {
                        $institutes = Request::getArray('institutes');
                        $query = "INSERT INTO `admissionrule_inst`
                            (`rule_id`, `institute_id`, `mkdate`)
                            VALUES ";
                        $params = [];
                        $first = true;
                        foreach ($institutes as $institute) {
                            if ($first) {
                                $first = false;
                            } else {
                                $query .= ", ";
                            }
                            $query .= "(?, ?, UNIX_TIMESTAMP())";
                            $params[] = $data['id'];
                            $params[] = $institute;
                        }
                        $stmt = DBManager::get()->prepare($query);
                        $success = $stmt->execute($params);
                    }
                }
            }
            if ($success) {
                PageLayout::postSuccess(_('Ihre Einstellungen wurden gespeichert.'));
            } else {
                PageLayout::postError(_('Ihre Einstellungen konnten nicht gespeichert werden.'));
            }
        }
        $this->redirect('admission/ruleadministration');
    }

    public function save_compat_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        // Iterate over existing entries and check which ones must be deleted.
        $matrix = AdmissionRuleCompatibility::getCompatibilityMatrix();

        $values = Request::getArray('compat');

        $to_delete = [];
        $new = [];
        foreach ($matrix as $type => $compat) {
            /*
             * Get entries that are in database, but not in request.
             * These must be removed from database as they are not
             * set anymore.
             */
            $to_delete[$type] = array_diff($compat, $values[$type]);

            /*
             * Get entries that are in request data, but not in database.
             * These must be inserted into DB.
             */
            $new[$type] = array_diff($values[$type], $compat);
        }

        // Get types that are set in request but not present at all in DB.
        foreach (array_diff(array_keys($values), array_keys($matrix)) as $newtype) {
            $new[$newtype] = $values[$newtype];
        }

        // Get types that are set in matrix but not present at all in request.
        foreach (array_diff(array_keys($matrix), array_keys($values)) as $oldtype) {
            $to_delete[$oldtype] = $matrix[$oldtype];
        }

        $success = 0;
        $fail = [];

        // Process the entries that will be deleted.
        foreach ($to_delete as $type => $compat) {
            foreach ($compat as $ctype) {
                $entry = AdmissionRuleCompatibility::find([$type, $ctype]);

                if ($entry->delete()) {
                    $success++;
                } else {
                    $fail[] = $type . ' => ' . $entry;
                }
            }
        }

        // Process the new entries.
        foreach ($new as $type => $entries) {
            foreach ($entries as $entry) {
                $a = new AdmissionRuleCompatibility();
                $a->rule_type = $type;
                $a->compat_rule_type = $entry;

                if ($a->store()) {
                    $success++;
                } else {
                    $fail[] = $type . ' => ' . $entry;
                }
            }

        }

        if ($success > 0 && count($fail) == 0) {
            PageLayout::postSuccess(_('Die Einstellungen zur Regelkompatibilität wurden gespeichert.'));
        } else if ($success > 0 && count($fail) > 0) {
            PageLayout::postWarning(_('Die Einstellungen zur '.
                'Regelkompatibilität konnten nicht vollständig gespeichert '.
                'werden. Es sind Probleme bei folgenden Einträgen aufgetreten:'),
                $fail);
        } else if (count($fail) > 0) {
            PageLayout::postError(_('Die Einstellungen zur Regelkompatibilität konnten nicht gespeichert werden.'));
        }

        $this->relocate('admission/ruleadministration/compatibility');
    }

    /**
     * Validate ticket (passed via request environment).
     * This method always checks Request::quoted('ticket').
     *
     * @throws InvalidArgumentException  if ticket is not valid
     */
    private function check_ticket()
    {
        if (!check_ticket(Request::option('ticket'))) {
            throw new InvalidArgumentException(_('Das Ticket für diese Aktion ist ungültig.'));
        }

    }

}
