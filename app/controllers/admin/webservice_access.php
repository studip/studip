<?php
# Lifter010: TODO
/**
 * webservice_access.php - access rules für webservices admin controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

class Admin_WebserviceAccessController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory;

        parent::before_filter($action, $args);

        $perm->check('root');

        if (!Config::get()->WEBSERVICES_ENABLE) {
            throw new AccessDeniedException(_("Die Webservices sind in diesem System nicht aktiviert."));
        }

        PageLayout::setTitle(_('Verwaltung der Zugriffsregeln für Webservices'));
        Navigation::activateItem('/admin/config/webservice_access');

        $this->get_all_rules();
    }

    /**
     * Display the list of ws access rules
     */
    function index_action()
    {
    }

     /**
     * Mark one rule as editable and display the list of ws access rules
     */
    function edit_action($id)
    {
        $this->edit = $id;
        $this->render_action('index');
    }

     /**
     * Add a new rule on top, mark as editable and display the list of ws access rules
     */
    function new_action()
    {
        array_unshift($this->ws_rules, new WebserviceAccessRule());
        $this->edit = 0;
        $this->render_action('index');
    }

    function delete_action($id)
    {
        $rule = $this->ws_rules[$id];
        if ($rule && !$rule->isNew() && $rule->delete()) {
            PageLayout::postMessage(MessageBox::success(_("Die Regel wurde gelöscht.")));
        }
        $this->redirect($this->url_for('admin/webservice_access'));
    }

    function update_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        if (Request::submitted('ok')) {
            if (!($rule = $this->ws_rules[Request::int('ws_rule_id')])) {
                $rule = new WebserviceAccessRule();
                $rule->id = 0;
                array_unshift($this->ws_rules, $rule);
            }
            $rule->api_key = trim(Request::get('ws_rule_api_key'));
            $rule->method = trim(Request::get('ws_rule_method'));
            $rule->ip_range = trim(Request::get('ws_rule_ip_range'));
            $rule->type = trim(Request::get('ws_rule_type'));

            $msg = [];

            if (mb_strlen($rule->api_key) < 5) {
                $msg['error'][] = _("Bitte geben Sie einen API-Key mit min. 5 Zeichen an.");
            }
            foreach ($rule->ip_range as $key => $ip) {
                if (!$ip) {
                    unset($rule->ip_range[$key]);
                    continue;
                }
                list($ip_address, $mask) = explode('/', $ip);
                if (!ip2long($ip_address) || ($mask && ($mask < 8 || $mask > 30))) {
                    $msg['error'][] = sprintf(_("Der IP Bereich %s ist ungültig."), htmlready($ip));
                    unset($rule->ip_range[$key]);
                }
            }
            if (!$rule->method) {
                $msg['info'][] = _("Eine Regel ohne angegebene Methode gilt für alle Methoden!");
            }
            if (!count($rule->ip_range)) {
                $msg['info'][] = _("Eine Regel ohne IP Bereich gilt für alle IP Adressen!");
            }
            if ($msg['error']) {
                PageLayout::postMessage(MessageBox::error(_("Die Regel wurde nicht gespeichert."), $msg['error']));
                $this->edit = $rule->id;
                $this->render_action('index');
                return;
            } else {
                if ($rule->store()) {
                    PageLayout::postMessage(MessageBox::success(_("Die Regel wurde gespeichert."), $msg['info']));
                }
            }
        }
        $this->redirect($this->url_for('admin/webservice_access'));
    }

    function test_action()
    {
        if (Request::submitted('ok')) {
            CSRFProtection::verifyUnsafeRequest();

            $test_api_key = trim(Request::get("test_api_key"));
            $test_method = trim(Request::get("test_method"));
            $test_ip = trim(Request::get("test_ip"));

            if ($test_api_key && $test_method && $test_ip) {
                if (WebserviceAccessRule::checkAccess($test_api_key, $test_method, $test_ip)) {
                    PageLayout::postMessage(MessageBox::success(_("Zugriff erlaubt.")));
                } else {
                    PageLayout::postMessage(MessageBox::error(_("Zugriff verboten.")));
                }
            }
        }
    }

     /**
     * reload all rules from database
     */
    function get_all_rules()
    {
        $this->ws_rules = [];
        foreach (WebserviceAccessRule::findAll() as $rule) {
            $this->ws_rules[$rule->id] = $rule;
        }
    }

}
