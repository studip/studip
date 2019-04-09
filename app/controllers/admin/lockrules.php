<?php
# Lifter010: TODO
/**
 * lockrules.php - lock rules admin controller
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

class Admin_LockrulesController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory;

        parent::before_filter($action, $args);

        $perm->check(Config::get()->LOCK_RULE_ADMIN_PERM ? Config::get()->LOCK_RULE_ADMIN_PERM : 'admin');

        PageLayout::setTitle(_('Verwaltung der Sperrebenen'));
        Navigation::activateItem('/admin/locations/lock_rules');

        URLHelper::bindLinkParam('lock_rule_type', $this->lock_rule_type);

        if (!$this->lock_rule_type || !$GLOBALS['perm']->have_perm('root')) {
            $this->lock_rule_type = 'sem';
        }
        if ($this->lock_rule_type == 'sem') {
            $this->lock_rule_permissions = $GLOBALS['perm']->have_perm('root') ? ['tutor','dozent','admin','root'] : ['tutor','dozent'];
        } elseif ($this->lock_rule_type == 'inst') {
            $this->lock_rule_permissions = ['admin','root'];
        } elseif ($this->lock_rule_type == 'user') {
            $this->lock_rule_permissions = ['tutor','dozent','admin','root'];
        }

        $this->rule_type_names = ['sem' => _('Veranstaltung'), 'inst' => _('Einrichtung'), 'user' => _('Person')];

        $this->sidebar = Sidebar::Get();
        $this->sidebar->setTitle(_('Sperrebenen'));
        $this->sidebar->setImage('sidebar/lock-sidebar.png');
    }

    /**
     * Display the list of lock rules
     */
    function index_action()
    {

        $actions = new ActionsWidget();
        $actions->addLink(_('Neue Sperrebene anlegen'), $this->url_for('admin/lockrules/new'), Icon::create('add', 'clickable'));
        $this->sidebar->addWidget($actions);
        if ($GLOBALS['perm']->have_perm('root')) {
            $list = new SelectWidget(_('Bereichsauswahl'), $this->url_for('admin/lockrules'), 'lock_rule_type');
            foreach (['sem' => _('Veranstaltung'), 'inst' => _('Einrichtung'), 'user' => _('Nutzer')] as $type => $desc) {
                $list->addElement(new SelectElement($type, $desc, Request::get('lock_rule_type') == $type), 'lock_rule_type-' . $type);
            }
            $this->sidebar->addWidget($list);
        }

        if ($this->lock_rule_type == 'sem') {
           $this->lock_rules = LockRules::getAdministrableSeminarRules($GLOBALS['user']->id);
        } else {
            $this->lock_rules = LockRule::findAllByType($this->lock_rule_type);
        }
    }

    /**
     * edit one lock rule
     */
    function edit_action($lock_rule_id)
    {
        $this->lock_rule = LockRule::find($lock_rule_id);
        $this->lock_config = LockRules::getLockRuleConfig($this->lock_rule_type);

        if (Request::submitted('ok')) {
            $ok = $this->handle_form_data();
            if ($ok === false) {
               PageLayout::postMessage(MessageBox::error(_('Die Änderungen der Sperrebene konnten nicht gespeichert werden.'), $this->msg['error']));
           } else if ($ok) {
                PageLayout::postMessage(MessageBox::success(_('Die Änderungen wurden gespeichert.')));
           }
        }

        $info = new ListWidget();
        $info->setTitle(_('Informationen'));
        $info->addElement(new WidgetElement( sprintf(_('Diese Sperrebene wird von %s Objekten benutzt.'), $this->lock_rule->getUsage())));
        $this->sidebar->addWidget($info);
        $actions = new ActionsWidget();
        $actions->addLink(_('Diese Ebene löschen'), $this->url_for('admin/lockrules/delete/' . $this->lock_rule->getid()), Icon::create('trash', 'clickable'));
        $actions->addLink(_('Bearbeiten abbrechen'), $this->url_for('admin/lockrules'), Icon::create('decline', 'clickable'));
        $this->sidebar->addWidget($actions);

    }

    function new_action()
    {
        $this->lock_rule = new LockRule();
        $this->lock_config = LockRules::getLockRuleConfig($this->lock_rule_type);

        if (Request::submitted('ok')) {
           $this->lock_rule->user_id = $GLOBALS['user']->id;
           $this->lock_rule->object_type = $this->lock_rule_type;
           if (!$this->handle_form_data()) {
               PageLayout::postMessage(MessageBox::error(_('Die neue Sperrebene konnte nicht gespeichert werden.'), $this->msg['error']));
           } else {
               PageLayout::postMessage(MessageBox::success(_('Die neue Sperrebene wurde gespeichert')));
               $this->redirect($this->url_for('admin/lockrules/edit/' . $this->lock_rule->getid()));
           }
        }
        $actions = new ActionsWidget();
        $actions->addLink(_('Bearbeiten abbrechen'), $this->url_for('admin/lockrules'), Icon::create('decline', 'clickable'));
        $this->sidebar->addWidget($actions);
    }

    function delete_action($lock_rule_id)
    {
        $this->lock_rule = LockRule::find($lock_rule_id);
        if (!(!$this->lock_rule->isNew() && ($GLOBALS['perm']->have_perm('root') || $this->lock_rule->user_id == $GLOBALS['user']->id))) {
            throw new Trails_Exception(403);
        }
        CSRFProtection::verifyUnsafeRequest();
        if ($this->lock_rule->delete()) {
            PageLayout::postMessage(MessageBox::success(_('Die Sperrebene wurde gelöscht.')));
        }
        $this->redirect($this->url_for('admin/lockrules'));
    }

    function handle_form_data()
    {
        CSRFProtection::verifyUnsafeRequest();
        $this->lock_rule->name = Request::get('lockdata_name');
        $this->lock_rule->description = Request::get('lockdata_description');
        $this->lock_rule->permission = Request::option('lockdata_permission');
        $this->lock_rule->attributes = Request::intArray('lockdata_attributes');
        if (!$this->lock_rule->name) {
            $this->msg['error'][] = _('Bitte geben Sie einen Namen für die Sperrebene an!');
            return false;
        }
        return $this->lock_rule->store();
    }

}
