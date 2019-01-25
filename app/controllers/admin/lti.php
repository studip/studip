<?php
/**
 * admin/lti.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class Admin_LtiController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');

        Navigation::activateItem('/admin/config/lti');
        PageLayout::setTitle(_('Konfiguration der LTI-Tools'));

        $widget = Sidebar::get()->addWidget(new ActionsWidget());
        $widget->addLink(
            _('Neues LTI-Tool registrieren'),
            $this->url_for('admin/lti/edit'),
            Icon::create('add')
        )->asDialog();

        Helpbar::get()->addPlainText('', _('Hier können Sie Verknüpfungen mit externen Tools konfigurieren, sofern diese den LTI-Standard (Version 1.x) unterstützen.'));
    }

    /**
     * Display the list of registered LTI tools.
     */
    public function index_action()
    {
        $this->tools = LtiTool::findAll();
    }

    /**
     * Display dialog for editing an LTI tool.
     *
     * @param   int $id tool id
     */
    public function edit_action($id = null)
    {
        $this->tool = new LtiTool($id);
    }

    /**
     * Save changes for an LTI tool.
     *
     * @param   int $id tool id
     */
    public function save_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();

        $tool = new LtiTool($id ?: null);
        $tool->name = trim(Request::get('name'));
        $tool->launch_url = trim(Request::get('launch_url'));
        $tool->consumer_key = trim(Request::get('consumer_key'));
        $tool->consumer_secret = trim(Request::get('consumer_secret'));
        $tool->custom_parameters = trim(Request::get('custom_parameters'));
        $tool->allow_custom_url = Request::int('allow_custom_url', 0);
        $tool->deep_linking = Request::int('deep_linking', 0);
        $tool->send_lis_person = Request::int('send_lis_person', 0);

        if ($tool->store()) {
            PageLayout::postSuccess(sprintf(_('Einstellungen für "%s" wurden gespeichert.'), $tool->name));
        }

        $this->redirect('admin/lti');
    }

    /**
     * Delete an LTI tool.
     *
     * @param   int $id tool id
     */
    public function delete_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();

        $tool = LtiTool::find($id);
        $tool_name = $tool->name;

        if ($tool && $tool->delete()) {
            PageLayout::postSuccess(sprintf(_('Das LTI-Tool "%s" wurde gelöscht.'), $tool_name));
        }

        $this->redirect('admin/lti');
    }
}
