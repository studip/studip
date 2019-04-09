<?php
/**
 * holidays.php - controller class for the holidays administration
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author    Hermann Schröder <hermann.schroeder@uni-oldenburg.de>
 * @author    Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @package   admin
 * @since     3.2
 */
class Admin_HolidaysController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     *
     * @param String $action Action that has been called
     * @param Array  $args   List of arguments
     */
    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        //setting title and navigation
        PageLayout::setTitle(_('Verwaltung von Ferien'));
        Navigation::activateItem('/admin/locations/holidays');

        // Extract and bind filter option
        $this->filter = Request::option('filter');
        if ($this->filter) {
            URLHelper::addLinkParam('filter', $this->filter);
        }

        $this->setSidebar();
    }

    /**
     * Display all informations about the holidays
     */
    public function index_action()
    {
        $this->holidays = array_reverse(SemesterHoliday::getAll());

        // Filter data?
        if ($this->filter === 'current') {
            $this->holidays = array_filter($this->holidays, function ($holiday) {
                return $holiday->ende > time();
            });
        } elseif ($this->filter === 'past') {
            $this->holidays = array_filter($this->holidays, function ($holiday) {
                return $holiday->ende <= time();
            });
        }
    }

    /**
     * This method edits existing holidays or creates new holidays
     *
     * @param mixed $id Id of the holiday or null to create one
     */
    public function edit_action($id = null)
    {
        $this->holiday = new SemesterHoliday($id);

        PageLayout::setTitle($this->holiday->isNew() ? _('Ferien anlegen') : _('Ferien bearbeiten'));

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $this->holiday->name        = Request::get('name');
            $this->holiday->description = Request::get('description');
            $this->holiday->beginn      = $this->getTimeStamp('beginn');
            $this->holiday->ende        = $this->getTimeStamp('ende', '23:59:59');

            $errors = [];
            if (!$this->holiday->name) {
                $errors[] = _('Bitte geben Sie einen Namen ein.');
            }
            if (!$this->holiday->beginn) {
                $errors[] = _('Bitte geben Sie einen Ferienbeginn ein.');
            }
            if (!$this->holiday->ende) {
                $errors[] = _('Bitte geben Sie ein Ferienende ein.');
            }
            if ($this->holiday->beginn > $this->holiday->ende) {
                $errors[] = _('Das Ferienende liegt vor dem Beginn.');
            }
            if (!empty($errors)) {
                PageLayout::postMessage(MessageBox::error(_('Ihre eingegebenen Daten sind ungültig.'), $errors));
            } elseif ($this->holiday->isDirty() && !$this->holiday->store()) {
                PageLayout::postMessage(MessageBox::error(_('Die Ferien konnten nicht gespeichert werden.')));
            } else {
                PageLayout::postMessage(MessageBox::success(_('Die Ferien wurden erfolgreich gespeichert.')));
                $this->relocate('admin/holidays');
            }
        }
    }

    /**
     * This method deletes a holiday or a bundle of holidays.
     *
     * @param string $id Id of the holiday (or 'bulk' for a bulk operation)
     */
    public function delete_action($id)
    {
        $ids = $id === 'bulk'
             ? Request::optionArray('ids')
             : [$id];

        if (count($ids)) {
            $holidays = SemesterHoliday::findMany($ids);
            foreach ($holidays as $holiday) {
                $holiday->delete();
            }

            PageLayout::postMessage(MessageBox::success(_('Die Ferien wurden erfolgreich gelöscht')));
        }

        $this->redirect('admin/holidays');
    }

    /**
     * Checks a string if it is a valid date and returns the according
     * unix timestamp if valid.
     *
     * @param string $name  Parameter name to extract from request
     * @param string $time Optional time segment
     * @return mixed Unix timestamp or false if not valid
     */
    private function getTimeStamp($name, $time = '0:00:00')
    {
        $date = Request::get($name);
        if ($date) {
            list($day, $month, $year) = explode('.', $date);
            if (checkdate($month, $day, $year)) {
                return strtotime($date . ' ' . $time);
            }
        }
        return false;
    }

    /**
     * Adds the content to sidebar
     */
    private function setSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Ferien'));
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(_('Alle Einträge'),
                        $this->url_for('admin/holidays', ['filter' => null]))
              ->setActive(!$this->filter);
        $views->addLink(_('Aktuelle/zukünftige Einträge'),
                        $this->url_for('admin/holidays', ['filter' => 'current']))
              ->setActive($this->filter === 'current');
        $views->addLink(_('Vergangene Einträge'),
                        $this->url_for('admin/holidays', ['filter' => 'past']))
              ->setActive($this->filter === 'past');
        $sidebar->addWidget($views);

        $links = new ActionsWidget();
        $links->addLink(_('Neue Ferien anlegen'),
                        $this->url_for('admin/holidays/edit', ['filter' => null]),
                        Icon::create('add', 'clickable'))
              ->asDialog('size=auto');
        $sidebar->addWidget($links);
    }
}
