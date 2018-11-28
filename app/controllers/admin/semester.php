<?php
/**
 * semester.php - controller class for the semester administration
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author    Hermann Schröder <hermann.schroeder@uni-oldenburg.de>
 * @author    Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @package   admin
 * @since     2.1
 */

class Admin_SemesterController extends AuthenticatedController
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
        PageLayout::setTitle(_('Verwaltung von Semestern'));
        Navigation::activateItem('/admin/locations/semester');

        // Extract and bind filter option
        $this->filter = Request::option('filter');
        if ($this->filter) {
            URLHelper::addLinkParam('filter', $this->filter);
        }

        // Setup sidebar
        $this->setSidebar();
    }

    /**
     * Display all informations about the semesters
     */
    public function index_action()
    {
        $this->semesters = array_reverse(Semester::getAll());

        // Filter data?
        if ($this->filter === 'current') {
            $this->semesters = array_filter($this->semesters, function ($semester) {
                return !$semester->past;
            });
        } elseif ($this->filter === 'past') {
            $this->semesters = array_filter($this->semesters, function ($semester) {
                return $semester->past;
            });
        }
    }

    /**
     * This method edits an existing semester or creates a new semester.
     *
     * @param mixed $id Id of the semester or null to create a semester.
     */
    public function edit_action($id = null)
    {
        $this->semester = new Semester($id);

        PageLayout::setTitle($this->semester->isNew() ? _('Semester anlegen') : _('Semester bearbeiten'));

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            // Extract values
            $this->semester->name           = Request::get('name');
            $this->semester->description    = Request::get('description');
            $this->semester->semester_token = Request::get('token');
            $this->semester->beginn         = $this->getTimeStamp('beginn');
            $this->semester->ende           = $this->getTimeStamp('ende', '23:59:59');
            $this->semester->vorles_beginn  = $this->getTimeStamp('vorles_beginn');
            $this->semester->vorles_ende    = $this->getTimeStamp('vorles_ende', '23:59:59');

            // Validate
            $errors = $this->validateSemester($this->semester);

            // If valid, try to store the semester
            if (empty($errors) && $this->semester->isDirty() && !$this->semester->store()) {
                $errors[] = _('Fehler bei der Speicherung Ihrer Daten. Bitte überprüfen Sie Ihre Angaben.');
            }

            // Output potential errors or show success message and relocate
            if (count($errors) === 1) {
                $error = reset($errors);
                PageLayout::postMessage(MessageBox::error($error));
            } elseif (!empty($errors)) {
                $message = _('Ihre eingegebenen Daten sind ungültig.');
                PageLayout::postMessage(MessageBox::error($message, $errors));
            } else {
                $message = _('Das Semester wurde erfolgreich gespeichert.');
                PageLayout::postMessage(MessageBox::success($message));

                $this->relocate('admin/semester');
            }

            $this->errors = $errors;
        }
    }

    /**
     * This method deletes a semester or a bundle of semesters.
     *
     * @param string $id Id of the semester (or 'bulk' for a bulk operation)
     */
    public function delete_action($id)
    {
        $ids = $id === 'bulk'
             ? Request::optionArray('ids')
             : array($id);

        if (count($ids)) {
            $errors  = array();
            $deleted = 0;

            $semesters = Semester::findMany($ids);
            foreach ($semesters as $semester) {
                if ($semester->absolute_seminars_count > 0) {
                    $errors[] = sprintf(_('Das Semester "%s" hat noch Veranstaltungen und kann daher nicht gelöscht werden.'), $semester->name);
                } elseif (!$semester->delete()) {
                    $errors[] = sprintf(_('Fehler beim Löschen des Semesters "%s".'), $semester->name);
                } else {
                    $deleted += 1;
                }
            }

            if (count($errors) === 1) {
                PageLayout::postMessage(MessageBox::error($errors[0]));
            } elseif (!empty($errors)) {
                $message = _('Beim Löschen der Semester sind folgende Fehler aufgetreten.');
                PageLayout::postMessage(MessageBox::error($message, $errors));
            }
            if ($deleted > 0) {
                $message = sprintf(_('%u Semester wurde(n) erfolgreich gelöscht.'), $deleted);
                PageLayout::postMessage(MessageBox::success($message));
            }
        }

        $this->redirect('admin/semester');
    }


    /**
     * Validates the semester for required valies, properness of values
     * and possible overlaps with other semesters.
     *
     * The validation is also divided into these three steps, so the next
     * validation step only occurs when the previous one succeeded.
     *
     * @param Semester $semester Semester (data) to validate
     * @return Array filled with errors
     */
    protected function validateSemester(Semester $semester)
    {
        // Validation, step 1: Check required values
        $errors = array();
        if (!$this->semester->name) {
            $errors['name'] = _('Sie müssen den Namen des Semesters angeben.');
        }
        if (!$this->semester->beginn) {
            $errors['beginn'] = _('Sie müssen den Beginn des Semesters angeben.');
        }
        if (!$this->semester->ende) {
            $errors['ende'] = _('Sie müssen das Ende des Semesters angeben.');
        }
        if (!$this->semester->vorles_beginn) {
            $errors['vorles_beginn'] = _('Sie müssen den Beginn der Vorlesungzeit angeben.');
        }
        if (!$this->semester->vorles_ende) {
            $errors['vorles_ende'] = _('Sie müssen das Ende der Vorlesungzeit angeben.');
        }

        // Validation, step 2: Check properness of values
        if (empty($errors)) {
            if ($this->semester->beginn > $this->semester->vorles_beginn) {
                $errors['beginn'] = _('Der Beginn des Semester muss vor dem Beginn der Vorlesungszeit liegen.');
            }
            if ($this->semester->vorles_beginn > $this->semester->vorles_ende) {
                $errors['vorles_beginn'] = _('Der Beginn der Vorlesungszeit muss vor ihrem Ende liegen.');
            }
            if ($this->semester->vorles_ende > $this->semester->ende) {
                $errors['vorles_ende'] = _('Das Ende der Vorlesungszeit muss vor dem Semesterende liegen.');
            }
        }

        // Validation, step 3: Check overlapping with other semesters
        if (empty($errors)) {
            $all_semester = SimpleCollection::createFromArray(Semester::getAll())->findBy('id', $this->semester->id, '<>');
            $collisions = $all_semester->findBy('beginn', array($this->semester->beginn, $this->semester->ende), '>=<=');
            $collisions->merge($all_semester->findBy('ende', array($this->semester->beginn, $this->semester->ende), '>=<='));
            if ($collisions->count()) {
                $errors[] = sprintf(_('Der angegebene Zeitraum des Semester überschneidet sich mit einem anderen Semester (%s)'), join(', ', $collisions->pluck('name')));
            }
        }

        return $errors;
    }

    /**
     * Checks a string if it is a valid date and returns the according
     * unix timestamp if valid.
     *
     * @param string $name  Parameter name to extract from request
     * @param string $time Optional time segment
     * @return mixed Unix timestamp or false if not valid
     */
    protected function getTimeStamp($name, $time = '0:00:00')
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
    protected function setSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Semester'));
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(_('Alle Einträge'),
                        $this->url_for('admin/semester', array('filter' => null)))
              ->setActive(!$this->filter);
        $views->addLink(_('Aktuelle/zukünftige Einträge'),
                        $this->url_for('admin/semester', array('filter' => 'current')))
              ->setActive($this->filter === 'current');
        $views->addLink(_('Vergangene Einträge'),
                        $this->url_for('admin/semester', array('filter' => 'past')))
              ->setActive($this->filter === 'past');
        $sidebar->addWidget($views);

        $links = new ActionsWidget();
        $links->addLink(_('Neues Semester anlegen'),
                        $this->url_for('admin/semester/edit', array('filter' => null)),
                        Icon::create('add', 'clickable'))
              ->asDialog('size=auto');
        $sidebar->addWidget($links);
    }
}