<?php
/**
 * Admin_Cronjobs_LogsController
 *
 * Controller class for the logs of cronjobs
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     GPL2 or any later version
 * @category    Stud.IP
 * @since       2.4
 */
class Admin_Cronjobs_LogsController extends AuthenticatedController
{
    protected $_autobind = true;

    /**
     * Set up this controller.
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/admin/config/cronjobs');
        PageLayout::setTitle(_('Cronjob-Verwaltung') . ' - ' . _('Log-Einträge'));

        if (empty($_SESSION['cronlog-filter'])) {
            $_SESSION['cronlog-filter'] = [
                'where'  => '1',
                'values' => [],
            ];
        }

        // Setup sidebar
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Cronjobs'));
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $actions = $sidebar->addWidget(new ViewsWidget());
        $actions->addLink(
            _('Cronjobs verwalten'),
            $this->url_for('admin/cronjobs/schedules')
        );
        $actions->addLink(
            _('Aufgaben verwalten'),
            $this->url_for('admin/cronjobs/tasks')
        );
        $actions->addLink(
            _('Logs anzeigen'),
            $this->url_for('admin/cronjobs/logs')
        )->setActive(true);
    }

    /**
     * Displays all available log entries according to the set filters.
     *
     * @param int $page Which page to display
     */
    public function index_action($page = 0)
    {
        $filter = $_SESSION['cronlog-filter'];

        $this->total = CronjobLog::countBySql();

        $this->pagination = Pagination::create(
            CronjobLog::countBySql($filter['where']),
            $page
        );

        $this->logs = $this->pagination->loadSORMCollection(
            CronjobLog::class,
            "{$filter['where']} ORDER BY executed DESC"
        );

        // Filters
        $this->schedules  = CronjobSchedule::findBySql('1');
        $this->tasks      = CronjobTask::findBySql('1');
        $this->filter     = $filter['values'];
    }

    /**
     * Sets the filters for the log view.
     * Filters are stored in the session.
     */
    public function filter_action()
    {
        $filter     = array_filter(Request::optionArray('filter'));
        $conditions = array();

        if (!empty($filter['status'])) {
            $conditions[] = ($filter['status'] === 'passed')
                          ? "exception IS NULL"
                          : "exception IS NOT NULL";
        }

        if (!empty($filter['schedule_id'])) {
            $conditions[] = "schedule_id = " . DBManager::get()->quote($filter['schedule_id']);
        }

        if (!empty($filter['task_id'])) {
            $temp = CronjobSchedule::findByTask_id($filter['task_id']);
            $temp = SimpleORMapCollection::createFromArray($temp);
            $schedule_ids = $temp->pluck('schedule_id') ?: null;
            $conditions[] = "schedule_id IN (" . DBManager::get()->quote($schedule_ids). ")";
        }

        $_SESSION['cronlog-filter'] = array(
            'where'  => implode(" AND " , $conditions) ?: '1',
            'values' => $filter,
        );
        $this->redirect('admin/cronjobs/logs');
    }

    /**
     * Sets the filters for the schedule view to a specific schedule id.
     *
     * @param String $schedule_id Id of the schedule in question
     */
    public function schedule_action($schedule_id)
    {
        $this->redirect('admin/cronjobs/logs/filter?filter[schedule_id]=' . $schedule_id);
    }

    /**
     * Sets the filters for the schedule view to a specific task id.
     *
     * @param String $task_id Id of the task in question
     */
    public function task_action($task_id)
    {
        $this->redirect('admin/cronjobs/logs/filter?filter[task_id]=' . $task_id);
    }

    /**
     * Displays a log entry.
     *
     * @param CronjobLog $log Log entry to display
     */
    public function display_action(CronjobLog $log)
    {
        PageLayout::setTitle(sprintf(
            _('Logeintrag für Cronjob "%s" anzeigen'),
            $log->schedule->title
        ));
    }

    /**
     * Deletes a log entry.
     *
     * @param CronjobLog $log Log entry to delete
     */
    public function delete_action(CronjobLog $log, $page = 0)
    {
        $log->delete();

        PageLayout::postSuccess(_('Der Logeintrag wurde gelöscht.'));
        $this->redirect("admin/cronjobs/logs/index/{$page}");
    }

    /**
     * Performs a bulk operation on a set of log entries. The only supported
     * operation at the moment is deleting.
     *
     * @param int    $page Return to this page afterwarsd (optional)
     */
    public function bulk_action($page = 1)
    {
        $action = Request::option('action');
        $ids    = Request::optionArray('ids');
        $logs   = CronjobLog::findMany($ids);

        if ($action === 'delete') {
            foreach ($logs as $log) {
                $log->delete();
            }

            $n = count($logs);
            $message = sprintf(ngettext('%u Logeintrag wurde gelöscht.', '%u Logeinträge wurden gelöscht.', $n), $n);
            PageLayout::postSuccess($message);
        }

        $this->redirect("admin/cronjobs/logs/index/{$page}");
    }

}
