<?php
# Lifter010: TODO
/**
 * specification.php - controller class for the specification
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       Stud.IP version 2.1
 */
class Admin_SpecificationController extends AuthenticatedController
{
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        # user must have special permission
        if (!$perm->have_perm(Config::get()->AUX_RULE_ADMIN_PERM ?: 'admin')) {
            throw new AccessDeniedException();
        }

        //setting title and navigation
        Navigation::activateItem('/admin/config/specification');
        PageLayout::setTitle(_('Verwaltung von Zusatzangaben'));
    }

    /**
     * Maintenance view for the specification parameters
     */
    public function index_action()
    {
        $this->allrules = AuxLockRules::getAllLockRules();
    }

    /**
     * Edit or create a rule
     *
     * @param md5 $edit_id
     */
    public function edit_action($id = null)
    {
        //get data
        $user_field            = 'user';
        $semdata_field         = 'usersemdata';
        $this->semFields       = AuxLockRules::getSemFields();
        $this->entries_user    = DataField::getDataFields($user_field);
        $this->entries_semdata = DataField::getDataFields($semdata_field);
        $this->rule            = is_null($id) ? false : AuxLockRules::getLockRuleByID($id);

        if ($GLOBALS['perm']->have_perm('root') && count($this->entries_semdata) == 0) {
            PageLayout::postWarning(sprintf(
                _('Sie müssen zuerst im Bereich %sDatenfelder%s in der Kategorie '
                . '<em>Datenfelder für Personenzusatzangaben in Veranstaltungen</em> '
                . 'einen neuen Eintrag erstellen.'),
                '<a href="' . URLHelper::getLink('dispatch.php/admin/datafields') . '">',
                '</a>'
            ));
        }
    }

    /**
     * Store or edit Rule
     * @param string $id
     */
    public function store_action($id = '')
    {
        CSRFProtection::verifyRequest();

        $errors = [];
        if (!Request::get('rulename')) {
            $errors[] = _('Bitte geben Sie der Regel mindestens einen Namen!');
        }
        if (!AuxLockRules::checkLockRule(Request::getArray('fields'))) {
            $errors[] = _('Bitte wählen Sie mindestens ein Feld aus der Kategorie "Zusatzinformationen" aus!');
        }

        if (empty($errors)) {
            if (!$id) {
                //new
                AuxLockRules::createLockRule(Request::get('rulename'), Request::get('description'), Request::getArray('fields'), Request::getArray('order'));
            } else {
                //edit
                AuxLockRules::updateLockRule($id, Request::get('rulename'), Request::get('description'), Request::getArray('fields'), Request::getArray('order'));
            }
            PageLayout::postSuccess(sprintf(
                _('Die Regel "%s" wurde erfolgreich gespeichert!'),
                htmlReady(Request::get('rulename'))
            ));
        } else {
            PageLayout::postError(_('Ihre Eingaben sind ungültig.'), $errors);
        }

        $this->redirect('admin/specification');
    }

    /**
     * Delete a rule, using a modal dialog
     *
     * @param md5 $rule_id
     */
    public function delete_action($rule_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (AuxLockRules::deleteLockRule($rule_id)) {
            PageLayout::postSuccess(_('Die Regel wurde erfolgreich gelöscht!'));
        } else {
            PageLayout::postError(_('Es können nur nicht verwendete Regeln gelöscht werden!'));
        }

        $this->redirect('admin/specification');
    }
}
