<?php
/**
 * start.php - start page controller
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   André Klaßen <klassen@elan-ev.de>
 * @author   Nadine Werner <nadine.werner@uni-osnabrueck.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.1
 */
class StartController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/start');
        PageLayout::setTabNavigation(NULL); // disable display of tabs
        PageLayout::setHelpKeyword('Basis.Startseite'); // set keyword for new help
        PageLayout::setTitle(_('Startseite'));
    }

    /**
     * Entry point of the controller that displays the start page of Stud.IP
     *
     * @param string $action
     * @param string $widgetId
     *
     * @return void
     */
    public function index_action($action = false, $widgetId = null)
    {
        if (!WidgetHelper::hasUserWidgets($GLOBALS['user']->id)) {
            WidgetHelper::setInitialPositions();
        }

        $this->left = WidgetHelper::getUserWidgets($GLOBALS['user']->id, 0);
        $this->right = WidgetHelper::getUserWidgets($GLOBALS['user']->id, 1);

        WidgetHelper::setActiveWidget(Request::get('activeWidget'));

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/home-sidebar.png');
        $sidebar->setTitle(_('Meine Startseite'));

        $nav = $sidebar->addWidget(new NavigationWidget());
        $nav->setTitle(_('Sprungmarken'));
        foreach (array_merge($this->left, $this->right) as $widget) {
            $nav->addLink(
                $widget->getPluginName(),
                $this->url_for("start#widget-{$widget->widget_id}")
            );
        }

        // Show action to add widget only if not all widgets have already been added.
        $actions = $sidebar->addWidget(new ActionsWidget());

        if (WidgetHelper::getAvailableWidgets($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Widgets hinzufügen'),
                $this->url_for('start/add'),
                Icon::create('add')
            )->asDialog();
        }

        $actions->addLink(
            _('Standard wiederherstellen'),
            $this->url_for('start/reset'),
            Icon::create('accept')
        );

        // Root may set initial positions
        if ($GLOBALS['perm']->have_perm('root')) {
            $settings = $sidebar->addWidget(new ActionsWidget());
            $settings->setTitle(_('Einstellungen'));
            $settings->addElement(new WidgetElement(_('Standard-Startseite bearbeiten:')));
            foreach ($GLOBALS['perm']->permissions as $permission => $useless) {
                $settings->addLink(
                    ucfirst($permission),
                    $this->url_for("start/edit_defaults/{$permission}"),
                    Icon::create('link-intern')
                )->asDialog();
            }
        }
        if ($GLOBALS['perm']->get_perm() == 'user') {
            PageLayout::postInfo(_('Sie haben noch nicht auf Ihre Bestätigungsmail geantwortet.'), [
                _('Bitte holen Sie dies nach, um Stud.IP Funktionen wie das Belegen von Veranstaltungen nutzen zu können.'),
                sprintf(_('Bei Problemen wenden Sie sich an: %s'), '<a href="mailto:'.$GLOBALS['UNI_CONTACT'].'">'.$GLOBALS['UNI_CONTACT'].'</a>')
            ]);

            $details = Studip\LinkButton::create(
                _('Bestätigungsmail erneut verschicken'),
                $this->url_for('start/resend_validation_mail')
            );

            if (!StudipAuthAbstract::CheckField('auth_user_md5.Email', $GLOBALS['user']->auth_plugin) && !LockRules::check($GLOBALS['user']->id, 'email')) {
                $details .= ' ';
                $details .= Studip\LinkButton::create(
                    _('E-Mail-Adresse ändern'),
                    $this->url_for('start/edit_mail_address'),
                    [
                        'data-dialog' => 'size=auto',
                        'title'       => _('E-Mail-Adresse')
                    ]
                );
            }
            PageLayout::postInfo(
                sprintf(_('Haben Sie die Bestätigungsmail an Ihre Adresse "%s" nicht erhalten?'), htmlReady($GLOBALS['user']->Email)),
                [$details]
            );
        }
    }

    /**
     *  This action adds one or more new widgets to the start page
     *
     * @return void
     */
    public function add_action()
    {
        PageLayout::setTitle(_('Widgets hinzufügen'));

        if (Request::isPost()) {
            $ticket   = Request::get('studip_ticket');
            $widgets  = Request::intArray('widget_id');
            $position = Request::int('position');

            $post_url = '';
            if (check_ticket($ticket)) {
                foreach ($widgets as $widget) {
                    $id = WidgetHelper::addWidget($widget, $GLOBALS['user']->id);
                    if (!$post_url) {
                        $post_url = '#widget-' . $id;
                    }
                }
            }
            $this->redirect('start' . $post_url);
        }
        $this->widgets = WidgetHelper::getAvailableWidgets($GLOBALS['user']->id);
    }


    /**
     * Edit the default startpage configuration for users by permissions
     *
     * @param string $permission
     *
     * @throws InvalidArgumentException
     */
    public function edit_defaults_action($permission)
    {
        if (!in_array($permission, array_keys($GLOBALS['perm']->permissions))) {
            throw new InvalidArgumentException('There is no such permission!');
        }

        PageLayout::setTitle(sprintf(_('Standard-Startseite für "%s" bearbeiten'), ucfirst($permission)));

        $this->widgets = WidgetHelper::getAvailableWidgets();
        $this->permission = $permission;

        $this->initial_widgets = WidgetHelper::getInitialPositions($permission);
        $available_plugin_ids = array_keys($this->widgets);
        $this->initial_widgets[0] = array_intersect((array)$this->initial_widgets[0], $available_plugin_ids);
        $this->initial_widgets[1] = array_intersect((array)$this->initial_widgets[1], $available_plugin_ids);

    }

    /**
     * Store the edited default startpage configuration for users by permissions
     *
     * @param string $permission
     *
     * @throws InvalidArgumentException
     */
    public function update_defaults_action($permission)
    {
        $GLOBALS['perm']->check('root');

        if (!in_array($permission, array_keys($GLOBALS['perm']->permissions))) {
            throw new InvalidArgumentException('There is no such permission!');
        }

        WidgetHelper::storeInitialPositions(0, Request::getArray('left'), $permission);
        WidgetHelper::storeInitialPositions(1, Request::getArray('right'), $permission);

        $this->render_nothing();
    }

    /**
     *  This actions removes a new widget from the start page
     *
     * @param string $widgetId
     * @param string $approveDelete
     * @param string $studipticket
     */
    public function delete_action($id)
    {
        if (Request::isPost()) {
            if (Request::submitted('yes')) {
                $name = WidgetHelper::getWidgetName($id);
                if (WidgetHelper::removeWidget($id, $name, $GLOBALS['user']->id)) {
                    $message = sprintf(
                        _('Widget "%s" wurde entfernt.'),
                        htmlReady($name)
                    );
                    PageLayout::postSuccess($message);
                } else {
                    $message = sprintf(
                        _('Widget "%s" konnte nicht entfernt werden.'),
                        htmlReady($name)
                    );
                    PageLayout::postError($message);
                }
            }
        } elseif ($widget_name = WidgetHelper::getWidgetName($id)) {
            PageLayout::postQuestion(
                sprintf(
                    _('Sind Sie sicher, dass Sie das Widget "%s" von der Startseite entfernen möchten?'),
                    htmlReady($widget_name)
                ),
                $this->url_for("start/delete/{$id}")
            );
        }
        $this->redirect('start');
    }

    /**
     * Resets widget to initial default state.
     */
    public function reset_action()
    {
        $widgets = array_merge(
            WidgetHelper::getUserWidgets($GLOBALS['user']->id, 0),
            WidgetHelper::getUserWidgets($GLOBALS['user']->id, 1)
        );

        foreach ($widgets as $widget) {
            $name = WidgetHelper::getWidgetName($widget->widget_id);
            WidgetHelper::removeWidget($widget->widget_id, $name, $GLOBALS['user']->id);
        }

        WidgetHelper::setInitialPositions();

        $message = _('Die Widgets wurden auf die Standardkonfiguration zurückgesetzt.');
        PageLayout::postSuccess($message);
        $this->redirect('start');
    }

    /**
     *  Action to store the widget placements
     *
     * @return void
     */
    public function storeNewOrder_action()
    {
        WidgetHelper::storeNewPositions(
            Request::get('widget'),
            Request::get('position'),
            Request::get('column')
        );
        $this->render_nothing();
    }

    /**
     * Resend the validation mail for the current user
     *
     * @return void
     */
    public function resend_validation_mail_action()
    {
        if ($GLOBALS['perm']->get_perm() === 'user') {
            Seminar_Register_Auth::sendValidationMail($GLOBALS['user']);
            PageLayout::postSuccess(
                _('Die Bestätigungsmail wurde erneut verschickt.')
            );
        }

        $this->redirect('start');
    }

    /**
     * Show form to change the mail-address for the validation mail
     *
     * @return void
     */
    public function edit_mail_address_action()
    {
        // only allow editing of mail-address here if user has not yet validated
        if ($GLOBALS['perm']->get_perm() !== 'user') {
            $this->redirect('start');
            return;
        }

        $this->restricted = StudipAuthAbstract::CheckField('auth_user_md5.Email', $GLOBALS['user']->auth_plugin)
                         && LockRules::check($GLOBALS['user']->id, 'email');
        $this->email = $GLOBALS['user']->Email;
    }

    /**
     * Change the mail-address and resend validation mail
     *
     * @return void
     */
    public function change_mail_address_action()
    {
        $email1 = Request::get('email1');
        $email2 = Request::get('email2');
        if ($GLOBALS['perm']->get_perm() == 'user') {

            if($email1 != $email2) {
                PageLayout::postError(_('Die Wiederholung der E-Mail-Adresse stimmt nicht mit Ihrer Eingabe überein.'));
                $this->redirect('start/edit_mail_address');
                return;
            }
            $user = new User($GLOBALS['user']->id);
            $user->Email = $email1;
            $user->store();

            $GLOBALS['user']->Email = $user->Email;

            Seminar_Register_Auth::sendValidationMail($user);
            PageLayout::postMessage(MessageBox::success(
                _('Ihre Mailadresse wurde geändert und die Bestätigungsmail erneut verschickt.')
            ));
        }

        $this->relocate('start');
    }
}
