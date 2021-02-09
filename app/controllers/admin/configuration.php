<?php

/**
 * configuration.php - controller class for the configuration
 *
 * @author  Jan-Hendrik Willms <tleilax+stuip@gmail.com>
 * @author  Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author  Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license GPL2 or any later version
 * @package admin
 * @since   2.0
 */
class Admin_ConfigurationController extends AuthenticatedController
{
    /**
     * Common before filter for all actions.
     *
     * @param String $action Called actions
     * @param Array $args Passed arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        // set navigation
        Navigation::activateItem('/admin/config/configuration');

        $this->range_type = 'global';
        foreach (['range', 'user', 'course', 'institute'] as $range_type) {
            if (mb_strpos($action, $range_type) !== false) {
                $this->range_type = $range_type;
            }
        }

        $this->setupSidebar($this->range_type);
    }

    /**
     * Maintenance view for the configuration parameters
     *
     * @param mixed $section Open section
     */
    public function configuration_action($open_section = null)
    {
        PageLayout::setTitle(_('Verwaltung von Systemkonfigurationen'));

        // Display only one section?
        $section = Request::option('section');
        if ($section == '-1') {
            $section = null;
        }

        // Search for specific entries?
        $needle = trim(Request::get('needle')) ?: null;
        if ($needle) {
            $this->subtitle = _('Suchbegriff:') . ' "' . htmlReady($needle) . '"';
        }

        // set variables for view
        $this->only_section = $section;
        $this->open_section = $open_section ?: $section;
        $this->needle = $needle;
        $this->sections = ConfigurationModel::getConfig($section, $needle);

        $this->title = _('Verwaltung von Systemkonfigurationen');
        $this->linkchunk = 'admin/configuration/edit_configuration';
        $this->has_sections = true;

        if ($needle && empty($this->sections)) {
            PageLayout::postError(sprintf(_('Es wurden keine Ergebnisse zu dem Suchbegriff "%s" gefunden.'), htmlReady($needle)));
            $this->redirect('admin/configuration/configuration');
        }
    }

    /**
     * Editview: Edit the configuration parameters: value, comment, section
     */
    public function edit_configuration_action()
    {


        $field = Request::get('field');
        $value = Request::get('value');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->validateInput($field, $value)) {
                $section = Request::get('section_new') ?: Request::get('section');
                $comment = Request::get('comment');

                Config::get()->store($field, compact(words('value section comment')));

                PageLayout::postSuccess(sprintf(
                    _('Der Konfigurationseintrag "%s" wurde erfolgreich übernommen!'),
                    htmlReady($field)
                ));

                $this->relocate('admin/configuration/configuration/' . $section);
                return;
            }
        }

        // set variables for view
        $this->config = ConfigurationModel::getConfigInfo($field);
        $this->allconfigs = ConfigurationModel::getConfig();

        PageLayout::setTitle(sprintf(_('Konfigurationsparameter: %s editieren'), $this->config['field']));
    }

    /**
     * Rangeview: Show all user-parameter for a Range or show the system range-parameter
     */
    public function range_configuration_action()
    {
        PageLayout::setTitle(_('Verwalten von Range-Konfigurationen'));

        $range_id = Request::option('id');
        if ($range_id) {
            $range = RangeFactory::find($range_id);

            $this->configs = ConfigurationModel::searchConfiguration($range);
            $this->title = sprintf(
                _('Vorhandene Konfigurationsparameter für "%s"'),
                $range->getFullname()
            );
            $this->linkchunk = 'admin/configuration/edit_range_config/' . $range_id;
        } else {
            $this->configs = ConfigurationModel::searchConfiguration(null);
            $this->title = _('Globale Konfigurationsparameter für alle Ranges');
            $this->linkchunk = 'admin/configuration/edit_configuration/';
        }
        $this->has_sections = false;
    }

    /**
     * Editview: Change range-parameter for one range (value)
     *
     * @param String $range_id
     */
    public function edit_range_config_action($range_id)
    {
        $field = Request::get('field');
        $range = RangeFactory::find($range_id);

        PageLayout::setTitle(_('Bearbeiten von Konfigurationsparametern für die Range: ') . $range->getFullname());

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $value = Request::get('value');
            if ($this->validateInput($field, $value)) {
                $range->getConfiguration()->store($field, $value);

                PageLayout::postSuccess(sprintf(
                    _('Der Konfigurationseintrag: %s wurde erfolgreich geändert!'),
                    htmlReady($field)
                ));

                $this->relocate('admin/configuration/range_configuration?id=' . $range_id);
                return;
            }
        }

        $this->config = ConfigurationModel::showConfiguration($range, $field);
        $this->range = $range;
        $this->field = $field;
    }

    /**
     * Userview: Show all user-parameter for a user or show the system user-parameter
     */
    public function user_configuration_action()
    {
        PageLayout::setTitle(_('Verwalten von Personenkonfigurationen'));

        $user_id = Request::option('id');
        $user = new User($user_id);

        if (!$user->isNew()) {
            $this->configs = ConfigurationModel::searchConfiguration($user);
            $this->title = sprintf(
                _('Vorhandene Konfigurationsparameter für "%s"'),
                $user->getFullname()
            );
            $this->linkchunk = 'admin/configuration/edit_user_config/' . $user_id;
        } else {
            $this->configs = ConfigurationModel::searchConfiguration($user);
            $this->title = _('Globale Konfigurationsparameter für alle Personen');
            $this->linkchunk = 'admin/configuration/edit_configuration';
        }
        $this->has_sections = false;
        $this->render_action('range_configuration');
    }

    /**
     * Editview: Change user-parameter for one user (value)
     *
     * @param String $user_id
     */
    public function edit_user_config_action(User $user)
    {
        PageLayout::setTitle(_('Bearbeiten von Konfigurationsparametern für die Person: ') . $user->getFullname());

        $field = Request::get('field');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $value = Request::get('value');
            if ($this->validateInput($field, $value)) {
                $user->getConfiguration()->store($field, $value);

                PageLayout::postSuccess(sprintf(
                    _('Der Konfigurationseintrag: %s wurde erfolgreich geändert!'),
                    htmlReady($field)
                ));

                $this->relocate('admin/configuration/user_configuration?id=' . $user->id);
                return;
            }
        }

        $this->config = ConfigurationModel::showConfiguration($user, $field);
        $this->range = $user;
        $this->field = $field;

        $this->render_action('edit_range_config');
    }

    /**
     * Show all parameters for a course or show the system course parameters
     */
    public function course_configuration_action()
    {
        PageLayout::setTitle(_('Verwalten von Veranstaltungskonfigurationen'));

        $course_id = Request::option('id');
        $course = new Course($course_id);
        if (!$course->isNew()) {
            $this->configs = ConfigurationModel::searchConfiguration($course);
            $this->title = sprintf(
                _('Vorhandene Konfigurationsparameter für "%s"'),
                $course->getFullname()
            );
            $this->linkchunk = 'admin/configuration/edit_course_config/' . $course_id;
        } else {
            $this->configs = ConfigurationModel::searchConfiguration($course);
            $this->title = _('Globale Konfigurationsparameter für alle Veranstaltungen');
            $this->linkchunk = 'admin/configuration/edit_configuration';
        }
        $this->has_sections = false;
        $this->render_action('range_configuration');
    }

    /**
     * Change course parameter for one course (value)
     *
     * @param String $course_id
     */
    public function edit_course_config_action(Course $course)
    {
        PageLayout::setTitle(_('Bearbeiten von Konfigurationsparametern für die Veranstaltung: ') . $course->getFullname());

        $field = Request::get('field');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $value = Request::get('value');
            if ($this->validateInput($field, $value)) {
                $course->getConfiguration()->store($field, $value);

                PageLayout::postSuccess(sprintf(
                    _('Der Konfigurationseintrag: %s wurde erfolgreich geändert!'),
                    htmlReady($field)
                ));

                $this->relocate('admin/configuration/course_configuration?id=' . $course->id);
                return;
            }
        }

        $this->config = ConfigurationModel::showConfiguration($course, $field);
        $this->range = $course;
        $this->field = $field;

        $this->render_action('edit_range_config');
    }

    /**
     * Show all parameters for an institute or show the system institute parameters
     */
    public function institute_configuration_action()
    {
        PageLayout::setTitle(_('Verwalten von Einrichtungskonfigurationen'));

        $institute_id = Request::option('id');
        $institute = new Institute($institute_id);
        if (!$institute->isNew()) {
            $this->configs = ConfigurationModel::searchConfiguration($institute);
            $this->title = sprintf(
                _('Vorhandene Konfigurationsparameter für "%s"'),
                $institute->getFullname()
            );
            $this->linkchunk = 'admin/configuration/edit_institute_config/' . $institute_id;
        } else {
            $this->configs = ConfigurationModel::searchConfiguration($institute);
            $this->title = _('Globale Konfigurationsparameter für alle Einrichtungen');
            $this->linkchunk = 'admin/configuration/edit_configuration';
        }
        $this->has_sections = false;
        $this->render_action('range_configuration');
    }

    /**
     * Change institute parameter for one institute (value)
     *
     * @param String $institute
     */
    public function edit_institute_config_action(Institute $institute)
    {
        PageLayout::setTitle(_('Bearbeiten von Konfigurationsparametern für die Einrichtung: ') . $institute->getFullname());

        $field = Request::get('field');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $value = Request::get('value');
            if ($this->validateInput($field, $value)) {
                $institute->getConfiguration()->store($field, $value);

                PageLayout::postSuccess(sprintf(_('Der Konfigurationseintrag: %s wurde erfolgreich geändert!'), htmlReady($field)));

                $this->relocate('admin/configuration/institute_configuration?id=' . $institute->id);
                return;
            }
        }

        $this->config = ConfigurationModel::showConfiguration($institute, $field);
        $this->range = $institute;
        $this->field = $field;

        $this->render_action('edit_range_config');
    }

    /**
     * Validates given input
     *
     * @param String $field Config field to validate
     * @param String $value Value that has been input
     * @return boolean indicating whether the value is valid
     */
    protected function validateInput($field, &$value)
    {
        $config = Config::get()->getMetadata($field);

        // Step 1: Prepare input
        if ($config['type'] === 'array') {
            $value = json_decode($value, true);
        } elseif ($config['type'] === 'i18n') {
            $value = Request::i18n('value');
        }

        // Step 2: Validate
        if ($config['type'] === 'integer' && !is_numeric($value)) {
            $error = _('Bitte geben Sie bei Parametern vom Typ "integer" nur Zahlen ein!');
        } elseif ($config['type'] === 'array' && !is_array($value)) {
            $error = _('Bitte geben Sie bei Parametern vom Typ "array" ein Array oder Objekt in korrekter JSON Notation ein!');
        } else {
            return true;
        }

        PageLayout::postError($error);

        return false;
    }

    /**
     * Sets up the sidebar
     *
     * @param bool $range_type Determine the sidebar search type
     */
    protected function setupSidebar($range_type)
    {
        // Basic info and layout
        $sidebar = Sidebar::Get();

        // Views
        $views = $sidebar->addWidget(new ViewsWidget());
        $views->addLink(
            _('Globale Konfiguration'),
            $this->url_for('admin/configuration/configuration')
        )->setActive($range_type === 'global');
        $views->addLink(
            _('Range-Konfiguration'),
            $this->url_for('admin/configuration/range_configuration')
        )->setActive($range_type === 'range');
        $views->addLink(
            _('Personenkonfiguration'),
            $this->url_for('admin/configuration/user_configuration')
        )->setActive($range_type === 'user');
        $views->addLink(
            _('Veranstaltungskonfiguration'),
            $this->url_for('admin/configuration/course_configuration')
        )->setActive($range_type === 'course');
        $views->addLink(
            _('Einrichtungskonfiguration'),
            $this->url_for('admin/configuration/institute_configuration')
        )->setActive($range_type === 'institute');

        // Add section selector when not in user mode
        if ($range_type === 'global') {
            $options = [];
            foreach (ConfigurationModel::getConfig() as $key => $value) {
                $options[$key] = $key ?: '- ' . _('Ohne Kategorie') . ' -';
            }
            $widget = new SelectWidget(
                _('Anzeigefilter'),
                $this->url_for('admin/configuration/configuration'),
                'section',
                'get'
            );
            $widget->addElement(new SelectElement(-1, _('alle anzeigen')));
            $widget->setOptions($options);
            $sidebar->addWidget($widget);
        }

        // Add specific searches (specific user when in user mode, keyword
        // otherwise)
        if ($range_type === 'range') {
            $search = new SearchWidget($this->url_for('admin/configuration/range_configuration'));
            $search->addNeedle(
                _('Range suchen'), 'id', true,
                new RangeSearch(),
                'function () { $(this).closest("form").submit(); }',
                Request::option('id')
            );
        } elseif ($range_type === 'user') {
            $search = new SearchWidget($this->url_for('admin/configuration/user_configuration'));
            $search->addNeedle(
                _('Person suchen'), 'id', true,
                new StandardSearch('user_id'),
                'function () { $(this).closest("form").submit(); }',
                Request::option('id')
            );
        } else if ($range_type === 'course') {
            $search = new SearchWidget($this->url_for('admin/configuration/course_configuration'));
            $search->addNeedle(
                _('Veranstaltung suchen'), 'id', true,
                new StandardSearch('Seminar_id'),
                'function () { $(this).closest("form").submit(); }',
                Request::option('id')
            );
        } else if ($range_type === 'institute') {
            $search = new SearchWidget($this->url_for('admin/configuration/institute_configuration'));
            $search->addNeedle(
                _('Einrichtungen suchen'), 'id', true,
                new StandardSearch('Institut_id'),
                'function () { $(this).closest("form").submit(); }',
                Request::option('id')
            );
        } else {
            $search = new SearchWidget($this->url_for('admin/configuration/configuration'));
            $search->addNeedle(_('Suchbegriff'), 'needle', true);
        }
        $sidebar->addWidget($search);
    }
}
