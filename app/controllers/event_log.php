<?php
/**
 * event_log.php - event logging admin controller
 *
 * @author    Elmar Ludwig <ludwig@uos.de>
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright 2009 Authors
 * @license   GPL2 or any later version
 */

require_once 'app/models/event_log.php';

class EventLogController extends AuthenticatedController
{
    protected $_autobind = true;

    private $event_log;
    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        $this->event_log = new EventLog();
    }

    /**
     * show and search log events
     */
    public function show_action()
    {
        PageLayout::setTitle(_('Anzeige der Log-Events'));
        Navigation::activateItem('/admin/log/show');

        $this->page = Request::int('page', 0);

        $this->action_id   = Request::option('action_id');
        $this->object_id   = Request::option('object_id');
        $this->format      = Request::option('format');
        $this->search      = trim(Request::get('search'));
        $this->log_actions = LogAction::getUsed(true);
        $this->types       = $this->event_log->get_object_types();

        // restrict log events to object scope
        if ($this->search) {
            $this->type = Request::option('type');
            $objects = $this->event_log->find_objects(
                $this->type,
                $this->search,
                $this->action_id
            );

            if (count($objects) > 0) {
                $this->objects = $objects;
            } else {
                PageLayout::postError(_('Kein passendes Objekt gefunden.'));
            }
        }

        // find all matching log events
        if (!$this->search || isset($this->object_id)) {
            $this->num_entries =$this->event_log->count_log_events(
                $this->action_id,
                $this->object_id
            );

            $this->log_events = $this->event_log->get_log_events(
                $this->action_id,
                $this->object_id,
                $this->page * 50
            );
        }
    }

    /**
     * configure log action
     */
    public function admin_action()
    {
        PageLayout::setTitle(_('Konfiguration der Logging-Funktionen'));
        Navigation::activateItem('/admin/log/admin');

        $this->log_actions = $this->event_log->get_log_actions();
    }

    /**
     * edit an existing log action
     */
    public function edit_action(LogAction $action)
    {
        PageLayout::setTitle(sprintf(
            _('Log-Aktion %s bearbeiten'),
            $action->name
        ));
    }

    /**
     * save changes to a log action
     */
    public function save_action(LogAction $action)
    {
        $action->description   = Request::get('description');
        $action->info_template = Request::get('info_template');
        $action->active        = (bool) Request::int('active', 0);
        $action->expires       = Request::int('expires', 0) * 86400;

        // Validate
        $errors = [];
        if (!$action->description) {
            $errors[] = _('Keine Beschreibung angegeben.');
        }
        if (!$action->info_template) {
            $errors[] = _('Kein Info-Template angegeben.');
        }
        if ($action->expires < 0) {
            $errors[] = _('Ablaufzeit darf nicht negativ sein.');
        }

        if (count($errors) > 0) {
            PageLayout::postError(_('Es sind Fehler aufgetreten.'), $errors);
            $this->render_action('edit');
            return;
        }

        $action->store();
        if (Request::isXhr()) {
            $this->response->add_header('X-Dialog-Close', 1);
            $this->render_nothing();
        } else {
            $this->redirect($this->admin());
        }
    }
}
