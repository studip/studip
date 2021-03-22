<?php
class WebMigrateController extends StudipController
{
    public function __construct($dispatcher)
    {
        if (basename($dispatcher->trails_uri, '.php') !== 'web_migrate') {
            throw new Exception('Web Migrator cannot be invoked via standard dispatcher.');
        }

        parent::__construct($dispatcher);
    }

    public function before_filter(&$action, &$args)
    {
        $GLOBALS['auth']->login_if(!$GLOBALS['perm']->have_perm('root'));
        $GLOBALS['perm']->check('root');

        parent::before_filter($action, $args);

        $this->target   = Request::int('target');
        $this->version  = new DBSchemaVersion('studip');
        $this->migrator = new Migrator(
            "{$GLOBALS['STUDIP_BASE_PATH']}/db/migrations",
            $this->version,
            true
        );

        FileLock::setDirectory($GLOBALS['TMP_PATH']);
        $this->lock = new FileLock('web-migrate');

        $this->setupSidebar($action);

        PageLayout::setTitle(_('Stud.IP Web-Migrator'));
    }

    public function index_action()
    {
        $this->migrations = $this->migrator->relevantMigrations($this->target);
    }

    public function migrate_action()
    {
        ob_start();
        set_time_limit(0);

        $this->lock->lock(['timestamp' => time(), 'user_id' => $GLOBALS['user']->id]);

        foreach (Request::getArray('versions') as $version) {
            $this->migrator->execute($version, 'up');
        }

        $this->lock->release();

        $announcements = ob_get_clean();
        PageLayout::postSuccess(
            _('Die Datenbank wurde erfolgreich migriert.'),
            array_filter(explode("\n", $announcements))
        );

        $_SESSION['migration-check'] = [
            'timestamp' => time(),
            'count'     => 0,
        ];

        $this->redirect('index');
    }

    public function release_action($target)
    {
        if ($this->lock->isLocked()) {
            $this->lock->release();

            PageLayout::postSuccess(_('Die Sperre wurde aufgehoben.'));
        }

        $this->redirect($this->url_for('index', compact('target')));
    }

    public function history_action()
    {
        $this->history = array_diff_key(
            $this->migrator->relevantMigrations(0),
            $this->migrator->relevantMigrations(null)
        );
    }

    public function revert_action()
    {
        ob_start();
        set_time_limit(0);

        $this->lock->lock(['timestamp' => time(), 'user_id' => $GLOBALS['user']->id]);

        foreach (Request::getArray('versions') as $version) {
            $this->migrator->execute($version, 'down');
        }

        $this->lock->release();

        $announcements = ob_get_clean();
        PageLayout::postSuccess(
            _('Die Datenbank wurde erfolgreich migriert.'),
            array_filter(explode("\n", $announcements))
        );

        $_SESSION['migration-check'] = [
            'timestamp' => time(),
            'count'     => 0,
        ];

        $this->redirect('history');
    }

    public function setupSidebar($action)
    {
        $views = Sidebar::get()->addWidget(new ViewsWidget());
        $views->addLink(
            _('Offene Migrationen'),
            $this->url_for('index')
        )->setActive($action === 'index');
        $views->addLink(
            _('Ausgeführte Migrationen'),
            $this->url_for('history')
        )->setActive($action === 'history');

        $widget = Sidebar::get()->addWidget(new SidebarWidget());
        $widget->setTitle(_('Aktueller Versionsstand'));
        $widget->addElement(new WidgetElement($this->version->get()));
    }
}
