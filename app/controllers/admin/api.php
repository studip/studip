<?php
/**
 *
 **/
class Admin_ApiController extends AuthenticatedController
{
    /**
     *
     **/
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        require_once 'lib/bootstrap-api.php';

        $GLOBALS['perm']->check('root');

        Navigation::activateItem('/admin/config/api');
        PageLayout::setTitle(_('API Verwaltung'));

        $this->types = [
            'website' => _('Website'),
            'desktop' => _('Herkömmliches Desktopprogramm'),
            'mobile'  => _('Mobile App')
        ];

        // Sidebar
        $views = new ViewsWidget();
        $views->addLink(_('Registrierte Applikationen'),
                        $this->url_for('admin/api'))
              ->setActive($action === 'index');
        $views->addLink(_('Globale Zugriffseinstellungen'),
                        $this->url_for('admin/api/permissions'))
              ->setActive($action == 'permissions');
        $views->addLink(_('Konfiguration'),
                        $this->url_for('admin/api/config'))
              ->setActive($action == 'config');
        Sidebar::get()->addWidget($views);

        $actions = new ActionsWidget();
        $actions->addLink(_('Neue Applikation registrieren'),
                          $this->url_for('admin/api/edit'),
                          Icon::create('add', 'clickable'))
                ->asDialog();
        Sidebar::get()->addWidget($actions);
    }

    /**
     *
     **/
    public function index_action()
    {
        $this->consumers = RESTAPI\Consumer\Base::findAll();
        $this->routes = RESTAPI\Router::getInstance()->getRoutes(true);
    }

    /**
     *
     **/
    public function render_keys($id)
    {
        $consumer = RESTAPI\Consumer\Base::find($id);

        return [
            'Consumer Key = ' . $consumer->auth_key,
            'Consumer Secret = ' . $consumer->auth_secret,
        ];
    }

    /**
     *
     **/
    public function keys_action($id)
    {
        $details = $this->render_keys($id);

        if (Request::isXhr()) {
            $this->render_text(implode('<br>', $details));
        } else {
            PageLayout::postMessage(MessageBox::info(_('Die Schlüssel in den Details dieser Meldung sollten vertraulich behandelt werden!'), $details, true));
            $this->redirect('admin/api/#' . $id);
        }
    }

    /**
     *
     **/
    public function edit_action($id = null)
    {
        $consumer = $id
                  ? RESTAPI\Consumer\Base::find($id)
                  : RESTAPI\Consumer\Base::create(Request::option('consumer_type') ?: 'oauth');

        if (Request::submitted('store')) {
            $errors = [];

            $consumer->active      = (bool) Request::int('active');
            $consumer->title       = Request::get('title');
            $consumer->contact     = Request::get('contact');
            $consumer->email       = Request::get('email');
            $consumer->callback    = Request::get('callback');
            $consumer->url         = Request::get('url');
            $consumer->type        = Request::get('type') ?: null;
            $consumer->commercial  = Request::int('commercial');
            $consumer->notes       = Request::get('notes');
            $consumer->description = Request::get('description');

            if (!empty($errors)) {
                $message = MessageBox::error(_('Folgende Fehler sind aufgetreten:'), $errors);
                PageLayout::postMessage($message);
                return;
            }

            $consumer->store();

            if ($id) {
                $message = MessageBox::success(_('Die Applikation wurde erfolgreich gespeichert.'));
            } else {
                $details  = $this->render_keys($consumer->id);
                $message = MessageBox::success(_('Die Applikation wurde erfolgreich erstellt, die Schlüssel finden Sie in den Details dieser Meldung.'), $details, true);
            }
            PageLayout::postMessage($message);
            $this->redirect('admin/api/index#' . $consumer->id);
            return;
        }

        $this->consumer = $consumer;
        $this->id = $id;
    }

    /**
     *
     **/
    public function toggle_action($id, $state = null)
    {
        $consumer = RESTAPI\Consumer\Base::find($id);

        $consumer->active = $state === null ? !$consumer->active : ($state === 'on');
        $consumer->store();

        $message = $state
                 ? _('Die Applikation wurde erfolgreich aktiviert.')
                 : _('Die Applikation wurde erfolgreich deaktiviert.');

        PageLayout::postMessage(MessageBox::success($message));
        $this->redirect('admin/api/#' . $consumer->id);
    }

    /**
     *
     **/
    public function delete_action($id)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }
        if ($consumer = RESTAPI\Consumer\Base::find($id)) {
            $consumer->delete();

            PageLayout::postSuccess(_('Die Applikation wurde erfolgreich gelöscht.'));
        }
        $this->redirect('admin/api');
    }

    /**
     *
     **/
    public function permissions_action($consumer_id = null)
    {
        if (Request::submitted('store')) {
            $perms       = Request::getArray('permission');
            $permissions = RESTAPI\ConsumerPermissions::get($consumer_id ?: 'global');

            foreach ($perms as $route => $methods) {
                foreach ($methods as $method => $granted) {
                    $permissions->set(urldecode($route), urldecode($method), (bool)$granted, true);
                }
            }

            $permissions->store();

            PageLayout::postMessage(MessageBox::success(_('Die Zugriffsberechtigungen wurden erfolgreich gespeichert')));
            $this->redirect($consumer_id ? 'admin/api' : 'admin/api/permissions');
            return;
        }

        $title = $consumer_id ? _('Zugriffsberechtigungen') : _('Globale Zugriffsberechtigungen');
        $title .= ' - ' . PageLayout::getTitle();
        PageLayout::setTitle($title);

        $this->consumer_id = $consumer_id;
        $this->router      = RESTAPI\Router::getInstance();
        $this->routes      = $this->router->getRoutes(true, false);
        $this->permissions = RESTAPI\ConsumerPermissions::get($consumer_id ?: 'global');
        $this->global      = $consumer_id ? RESTAPI\ConsumerPermissions::get('global') : false;
    }

    public function config_action()
    {
        $this->config = Config::get();

        if (Request::isPost()) {
            $this->config->store('API_ENABLED', Request::int('active', 0));
            $this->config->store('API_OAUTH_AUTH_PLUGIN', Request::option('auth'));

            PageLayout::postMessage(MessageBox::success(_('Die Einstellungen wurden gespeichert.')));
            $this->redirect('admin/api/config');
        }
    }
}
