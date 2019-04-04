<?php
/**
 * wizard.php
 * Controller for course creation wizard.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.3
 */

class Course_WizardController extends AuthenticatedController
{
    /**
     * @var Array steps the wizard has to execute in order to create a new course.
     */
    public $steps = array();

    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);
        global $perm;
        if (Request::isXhr()) {
            $this->dialog = true;
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/seminar-sidebar.png');

        $this->studygroup = Request::int('studygroup') ?: $this->flash['studygroup'];

        if (!$this->studygroup) {
            PageLayout::setTitle(_('Neue Veranstaltung anlegen'));
            $sidebar->setTitle(_('Neue Veranstaltung anlegen'));

            $navigation = new Navigation(_('Neue Veranstaltung anlegen'), 'dispatch.php/course/wizard');
            Navigation::addItem('/browse/my_courses/new_course', $navigation);
            Navigation::activateItem('/browse/my_courses/new_course');
        } else {
            $this->flash['studygroup'] = true;

            PageLayout::setTitle(_('Neue Studiengruppe anlegen'));
            $sidebar->setTitle(_('Neue Studiengruppe anlegen'));

            $navigation = new Navigation(_('Neue Studiengruppe anlegen'), 'dispatch.php/course/wizard?studygroup=1');
            Navigation::addItem('/browse/my_courses/new_course', $navigation);
            Navigation::activateItem('/browse/my_courses/new_course');
        }

        $this->steps = CourseWizardStepRegistry::findBySQL("`enabled`=1 ORDER BY `number`");

        if ($GLOBALS['user']->perms === 'user') {
            throw new AccessDeniedException();
        }
    }

    /**
     * Just some sort of placeholder for initial calling without a step number.
     */
    public function index_action()
    {
        $this->redirect('course/wizard/step/0' . ($this->studygroup ? '?studygroup=1' : ''));
    }

    /**
     * Fetches the wizard step with the given number and gets the
     * corresponding template.
     *
     * @param int $number step number to show
     * @param String $temp_id temporary ID for the course to create
     */
    public function step_action($number=0, $temp_id='')
    {
        $step = $this->getStep($number);
        if (!$temp_id) {
            $this->initialize();
            if (Request::getArray('batchcreate')) {
                $_SESSION['coursewizard'][$this->temp_id]['batchcreate'] = Request::getArray('batchcreate');
            }
        } else {
            $this->temp_id = $temp_id;
        }
        if ($number == 0) {
            $this->first_step = true;
        }

        if ($this->studygroup) {
            // Add special studygroup flag to set values.
            $this->setStepValues(
                get_class($step),
                array_merge($this->getValues(get_class($step)), array('studygroup' => 1))
            );
        }
        $this->values = $this->getValues();
        $this->content = $step->getStepTemplate($this->values, $number, $this->temp_id);
        $this->stepnumber = $number;

    }

    /**
     * Processes a finished wizard step by saving the gathered values to
     * session.
     * @param int $step_number the step we are at.
     * @param String $temp_id temporary ID for the course to create
     */
    public function process_action($step_number, $temp_id)
    {
        $this->temp_id = $temp_id;
        // Get request data and store it in session.
        $iterator = Request::getInstance()->getIterator();
        $values = array();
        while ($iterator->valid()) {
            $values[$iterator->key()] = $iterator->current();
            $iterator->next();
        }
        if ($this->steps[$step_number]['classname']) {
            $this->setStepValues($this->steps[$step_number]['classname'], $values);
        }
        // Back or forward button clicked -> set next step accordingly.
        if (Request::submitted('back')) {
            $next_step = $this->getNextRequiredStep($step_number, 'down');
        } else if (Request::submitted('next')) {
            // Validate given data.
            if ($this->getStep($step_number)->validate($this->getValues())) {
                $next_step = $this->getNextRequiredStep($step_number, 'up');
            /*
             * Validation failed -> stay on current step. Error messages are
             * provided via the called step class validation method.
             */
            } else {
                $next_step = $step_number;
            }
        // The "create" button was clicked -> create course.
        } else if (Request::submitted('create')) {
            $_SESSION['coursewizard'][$this->temp_id]['copy_basic_data'] = Request::submitted('copy_basic_data');
            if ($this->getValues()) {
                // Batch creation of several courses at once.
                if ($batch = Request::getArray('batchcreate')) {
                    $numbering = ($batch['numbering'] == 'number' ? 1 : 'A');
                    $success = 0;
                    $failed = 0;
                    // Create given number of courses.
                    for ($i = 1 ; $i <= $batch['number'] ; $i++) {
                        if ($newcourse = $this->createCourse($i == $batch['number'] ? true : false)) {
                            // Add corresponding number/letter to name or number of newly created course.
                            if ($batch['add_number_to'] == 'name') {
                                $newcourse->name .= ' ' . $numbering;
                            } else if ($batch['add_number_to'] == 'number') {
                                $newcourse->veranstaltungsnummer = $batch['numbering'] == 'number' ?
                                    $numbering : $newcourse->veranstaltungsnummer . ' ' . $numbering;
                            }
                            $newcourse->parent_course = $batch['parent'];
                            if ($newcourse->store()) {
                                $numbering++;
                                $success++;
                            } else {
                                $failed++;
                            }
                        } else {
                            $failed++;
                        }
                    }

                    // Show message for successfully created courses.
                    if ($success > 0) {
                        PageLayout::postSuccess(sprintf(_('%u Veranstaltungen wurden angelegt.'), $success));
                    }

                    // Show message for courses that couldn't be created.
                    if ($failed > 0) {
                        PageLayout::postError(sprintf(_('%u Veranstaltungen konnten nicht angelegt werden.'), $failed));
                    }

                    $this->redirect(URLHelper::getURL('dispatch.php/course/grouping/children',
                        ['cid' => $batch['parent']]));
                } else {
                    if ($this->course = $this->createCourse()) {
                        // A studygroup has been created.
                        if (in_array($this->course->status, studygroup_sem_types() ?: array())) {
                            $message = MessageBox::success(
                                sprintf(_('Die Studien-/Arbeitsgruppe "%s" wurde angelegt. ' .
                                    'Sie können sie direkt hier weiter verwalten.'),
                                    htmlReady($this->course->name)));
                            $target = $this->url_for('course/studygroup/edit/?cid=' . $this->course->id);
                            // "Normal" course.
                        } else {
                            if (Request::int('dialog')) {
                                $message = MessageBox::success(
                                    sprintf(_('Die Veranstaltung "%s" wurde angelegt.'), htmlReady($this->course->getFullname())));
                                $target = $this->url_for('admin/courses');
                            } else {
                                $message = MessageBox::success(
                                    sprintf(_('Die Veranstaltung "%s" wurde angelegt. Sie können sie direkt hier weiter verwalten.'),
                                        htmlReady($this->course->getFullname())));
                                $target = $this->url_for('course/management?cid=' . $this->course->id);
                            }
                        }
                        PageLayout::postMessage($message);
                        $this->redirect($target);
                    } else {
                        PageLayout::postMessage(MessageBox::error(
                            _('Die Veranstaltung konnte nicht angelegt werden.')));
                        $this->redirect('course/wizard');
                    }
                }
            } else {
                PageLayout::postMessage(MessageBox::error(_('Die angegebene Veranstaltung wurde bereits angelegt.')));
                $this->redirect('course/wizard');
            }
            $stop = true;
        /*
         * Something other than "back", "next" or "create" was clicked,
         * e.g. QuickSearch
         * -> stay on current step and process given values.
         */
        } else {
            $stepclass = $this->steps[$step_number]['classname'];
            $result = $this->getStep($step_number)
                ->alterValues($this->getValues());
            $_SESSION['coursewizard'][$temp_id][$stepclass] = $result;
            $next_step = $step_number;
        }
        if (!$stop) {
            // We are after the last step -> all done, show summary.
            if ($next_step >= sizeof($this->steps)) {
                $this->redirect($this->url_for('course/wizard/summary', $next_step, $temp_id));
            // Redirect to next step.
            } else {
                $this->redirect($this->url_for('course/wizard/step', $next_step, $this->temp_id));
            }
        }
    }

    /**
     * We are after last step: all set and ready to create a new course.
     */
    public function summary_action($stepnumber, $temp_id)
    {
        $this->stepnumber = $stepnumber;
        $this->temp_id = $temp_id;
        if (!$this->getValues()) {
            throw new UnexpectedValueException('no data found');
        }
        if (isset($_SESSION['coursewizard'][$this->temp_id]['source_id'])) {
            $this->source_course = Course::find($_SESSION['coursewizard'][$this->temp_id]['source_id']);
        }
    }

    /**
     * Wrapper for ajax calls to step classes. Three things must be given
     * via Request:
     * - step number
     * - method to call in target step
     * - parameters for the target method (will be passed in given order)
     */
    public function ajax_action()
    {
        $stepNumber = Request::int('step');
        $method = Request::get('method');
        $parameters = Request::getArray('parameter');
        $result = call_user_func_array(array($this->getStep($stepNumber), $method), $parameters);
        if (is_array($result) || is_object($result)) {
            $this->render_json($result);
        } else {
            $this->render_text($result);
        }

    }

    public function forward_action($step_number, $temp_id)
    {
        $this->temp_id = $temp_id;
        $stepclass = $this->steps[$step_number]['classname'];
        $result = $this->getStep($step_number)->alterValues($this->getValues() ?: array());
        $this->setStepValues($stepclass, $result);
        $this->redirect($this->url_for('course/wizard/step', $step_number, $this->temp_id));
    }

    /**
     * Copy an existing course.
     */
    public function copy_action($id) {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $id)
            || LockRules::Check($id, 'seminar_copy')) {
            throw new AccessDeniedException(_("Sie dürfen diese Veranstaltung nicht kopieren"));
        }
        $course = Course::find($id);
        $values = array();
        for ($i = 0 ; $i < sizeof($this->steps) ; $i++) {
            $step = $this->getStep($i);
            $values = $step->copy($course, $values);
        }
        $values['source_id'] = $course->id;
        $this->initialize();
        $_SESSION['coursewizard'][$this->temp_id] = $values;
        $this->redirect($this->url_for('course/wizard/step/0/' . $this->temp_id, array('cid' => '')));
    }

    /**
     * Creates a temporary ID for storing the wizard values in session.
     */
    private function initialize()
    {
        $temp_id = md5(uniqid(microtime()));
        $_SESSION['coursewizard'][$temp_id] = array();
        $this->temp_id = $temp_id;
    }

    /**
     * Wizard finished: we can create the course now. First store an empty,
     * invisible course for getting an ID. Then, iterate through steps and
     * set values from each step.
     * @param bool $cleanup cleanup session after course creation?
     * @return Course
     * @throws Exception
     */
    private function createCourse($cleanup = true)
    {

        foreach (array_keys($this->steps) as $n) {
            $step = $this->getStep($n);
            if ($step->isRequired($this->getValues())) {
                if (!$step->validate($this->getValues())) {
                    unset($_SESSION['coursewizard'][$this->temp_id]);
                    return false;
                }
            }
        }
        // Create a new (empty) course so that we get an ID.
        $course = new Course();
        $course->visible = 0;
        $course->setId($course->getNewId());
        $course_id = $course->id;
        // Each (required) step stores its own values at the course object.
        for ($i = 0; $i < sizeof($this->steps) ; $i++) {
            $step = $this->getStep($i);
            if ($step->isRequired($this->getValues())) {
                if ($stored = $step->storeValues($course, $this->getValues())) {
                    $course = $stored;
                } else {
                    $course = false;
                    unset($_SESSION['coursewizard'][$this->temp_id]);
                    break;
                    //throw new Exception(_('Die Daten aus Schritt ' . $i . ' konnten nicht gespeichert werden, breche ab.'));
                }
            }
        }
        // Cleanup session data if necessary.
        if ($cleanup) {
            unset($_SESSION['coursewizard'][$this->temp_id]);
        }
        return $course;
    }

    /**
     * Fetches the class belonging to the wizard step at the given index.
     * @param $number
     * @return mixed
     */
    private function getStep($number)
    {
        $classname = $this->steps[$number]['classname'];
        return new $classname();
    }

    /**
     * Not all steps are required for each course type, some sem_classes must
     * not have study areas, for example. So we need to check which step is
     * required next, starting from an index and going up or down, according
     * to navigation through the wizard.
     * @param $number
     * @param string $direction
     * @return mixed
     */
    private function getNextRequiredStep($number, $direction='up')
    {
        $found = false;
        switch ($direction) {
            case 'up':
                $i = $number + 1;
                while (!$found && $i < sizeof($this->steps)) {
                    $step = $this->getStep($i);
                    if ($step->isRequired($this->getValues())) {
                        $found = true;
                    } else {
                        $i++;
                    }
                }
                break;
            case 'down':
                $i = $number - 1;
                while (!$found && $i >= 0) {
                    $step = $this->getStep($i);
                    if ($step->isRequired($this->getValues())) {
                        $found = true;
                    } else {
                        $i--;
                    }
                }
                break;
        }
        return $i;
    }

    /**
     * Gets values stored in session for a given step, or all
     * @param string $classname the step to get values for, or all
     * @return Array
     */
    private function getValues($classname='')
    {
        if ($classname) {
            return $_SESSION['coursewizard'][$this->temp_id][$classname] ?: array();
        } else {
            return $_SESSION['coursewizard'][$this->temp_id] ?: array();
        }
    }

    /**
     * @param $stepclass class name of the current step.
     * @return Array
     */
    private function setStepValues($stepclass, $values) {
        $_SESSION['coursewizard'][$this->temp_id][$stepclass] = $values;
    }

}
