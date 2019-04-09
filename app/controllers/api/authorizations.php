<?php

require_once 'lib/bootstrap-api.php';

/**
 *
 **/
class Api_AuthorizationsController extends AuthenticatedController
{
    /**
     *
     **/
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('autor');

        Navigation::activateItem('/profile/settings/api');
        PageLayout::setTitle(_('Applikationen'));

        $this->types = [
            'website' => _('Website'),
            'program' => _('Herkömmliches Desktopprogramm'),
            'app'     => _('Mobile App')
        ];
    }

    /**
     *
     **/
    public function index_action()
    {
        $this->consumers = RESTAPI\UserPermissions::get($GLOBALS['user']->id)->getConsumers();
        $this->types = [
            'website' => _('Website'),
            'program' => _('Herkömmliches Desktopprogramm'),
            'app'     => _('Mobile App')
        ];

        $widget = new SidebarWidget();
        $widget->setTitle(_('Informationen'));
        $widget->addElement(new WidgetElement(_('Dies sind die Apps, die Zugriff auf Ihren Account haben.')));
        Sidebar::Get()->addWidget($widget);
    }

    /**
     *
     **/
    public function revoke_action($id)
    {
        $consumer = new RESTAPI\Consumer\OAuth($id);
        $consumer->revokeAccess($GLOBALS['user']->id);

        PageLayout::postMessage(MessageBox::success(_('Der Applikation wurde der Zugriff auf Ihre Daten untersagt.')));
        $this->redirect('api/authorizations');
    }
}