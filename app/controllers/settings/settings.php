<?php
/**
 * SettingsController - Base controller for all setting related pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'lib/messaging.inc.php';

class Settings_SettingsController extends AuthenticatedController
{
    // Stores message which shall be send to the user via email
    protected $private_messages = [];

    /**
     * Sets up the controller
     *
     * @param String $action Which action shall be invoked
     * @param Array $args Arguments passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        // Abwärtskompatibilität, erst ab 1.1 bekannt
        if (!isset(Config::get()->ALLOW_CHANGE_NAME)) {
            Config::get()->ALLOW_CHANGE_NAME = true;
        }

        parent::before_filter($action, $args);

        // Ensure user is logged in
        $GLOBALS['auth']->login_if($action !== 'logout' && $GLOBALS['auth']->auth['uid'] === 'nobody');

        // extract username
        $username   = Request::username('username', $GLOBALS['user']->username);
        $this->user = User::findByUsername($username);

        if (!$GLOBALS['perm']->have_profile_perm('user', $this->user->user_id)) {
            $exception = new AccessDeniedException(_('Sie dürfen dieses Profil nicht bearbeiten'));
            $exception->setDetails([
                _("Wahrscheinlich ist Ihre Session abgelaufen. Bitte "
                 ."nutzen Sie in diesem Fall den untenstehenden Link, "
                 ."um zurück zur Anmeldung zu gelangen.\n\n"
                 ."Eine andere Ursache kann der Versuch des Zugriffs "
                 ."auf Userdaten, die Sie nicht bearbeiten dürfen, sein. "
                 ."Nutzen Sie den untenstehenden Link, um zurück auf "
                 ."die Startseite zu gelangen."),
            ]);
            throw $exception;
        }

        URLHelper::addLinkParam('username', $this->user->username);

        $this->restricted = $GLOBALS['perm']->get_profile_perm($this->user->user_id) !== 'user'
                         && $username !== $GLOBALS['user']->username;
        $this->config     = UserConfig::get($this->user->user_id);
        $this->validator  = new email_validation_class(); # Klasse zum Ueberpruefen der Eingaben
        $this->validator->timeout = 10;

        // Default auth plugin to standard
        if (!$this->user->auth_plugin) {
            $this->user->auth_plugin = 'standard';
        }

        // Show info message if user is not on his own profile
        if ($username !== $GLOBALS['user']->username) {
            $message = sprintf(
                _('Daten von: %1$s (%2$s), Status: %3$s'),
                htmlReady($this->user->getFullName()),
                htmlReady($username),
                htmlReady($this->user->perms)
            );
            $mbox = MessageBox::info($message);
            PageLayout::postMessage($mbox, 'settings-user-anncouncement');
        }

        Sidebar::get()->setImage('sidebar/person-sidebar.png');
    }

    /**
     * Generic ticket check
     *
     * @throws AccessDeniedException if ticket is missing or invalid
     */
    protected function check_ticket()
    {
        $ticket = Request::get('studip_ticket');
        if (!$ticket || !check_ticket($ticket)) {
            throw new InvalidSecurityTokenException();
        }
    }

    /**
     * Adjust url_for so it imitates the parameters behaviour of URLHelper.
     * This way you can add parameters by adding an associative array as last
     * argument.
     *
     * @param mixed $to Path segments of the url (String) or url parameters
     *                  (Array)
     * @return String Generated url
     */
    public function url_for($to = ''/*, ...*/)
    {
        $arguments  = func_get_args();
        $parameters = is_array(end($arguments)) ? array_pop($arguments) : [];
        $url        = call_user_func_array('parent::url_for', $arguments);
        return URLHelper::getURL($url, $parameters);
    }

    /**
     * Gets the default template for an action.
     *
     * @param String $action Which action was invoked
     * @return String File name of the template
     */
    public function get_default_template($action)
    {
        $class = get_class($this);
        $controller_name = Trails_Inflector::underscore(mb_substr($class, 0, -10));
        return file_exists($this->dispatcher->trails_root . '/views/' . $controller_name . '.php')
            ? $controller_name
            : $controller_name . '/' . $action;
    }

    /**
     * Render nothing but with a layout
     *
     * @param String $text Optional nothing text
     * @return String Rendered output
     */
    public function render_nothing($text = '')
    {
        if ($this->layout) {
            $factory = $this->get_template_factory();
            $layout = $factory->open($this->layout);
            $layout->content_for_layout = $text;
            $text = $layout->render();
        }

        return parent::render_text($text);
    }

    /**
     * Determines whether a user is permitted to change a certain value
     * and if provided, whether the value has actually changed.
     *
     * @param String $field Which db field shall change
     * @param mixed $attribute Which attribute is related (optional,
     *                         automatically guessedif missing)
     * @param mixed $value Optional new value of the field (used to determine
     *                     whether the value has actually changed)
     * @return bool Indicates whether the value shall actually change
     */
    public function shallChange($field, $attribute = null, $value = null)
    {
        $column = end(explode('.', $field));
        $attribute = $attribute ?: mb_strtolower($column);

        $global_mapping = [
            'email'    => 'ALLOW_CHANGE_EMAIL',
            'name'     => 'ALLOW_CHANGE_NAME',
            'title'    => 'ALLOW_CHANGE_TITLE',
            'username' => 'ALLOW_CHANGE_USERNAME',
        ];

        if (isset($global_mapping[$attribute]) and !Config::get()->{$global_mapping[$attribute]}) {
            return false;
        }

        return !($field && StudipAuthAbstract::CheckField($field, $this->user->auth_plugin))
            && !LockRules::check($this->user->user_id, $attribute)
            && (($value === null) || ($this->user->$column != $value));
    }

    /**
     * Add to the private messages
     *
     * @param String $message Message to store
     * @return Object Returns $this to allow chaining
     */
    protected function postPrivateMessage($message/*, $args */)
    {
        $message = vsprintf($message, array_slice(func_get_args(), 1));

        $this->private_messages[] = trim($message);
        return $this;
    }

    /**
     * The after filter handles the sending of private messages via email, if
     * present. Also, if an action requires the user to be logged out, this is
     * accomplished here.
     *
     * @param String $action Name of the action that has been invoked
     * @param Array  $args   Arguments of the action
     */
    public function after_filter($action, $args)
    {
        if ($this->restricted && count($this->private_messages) > 0) {
            setTempLanguage($this->user->user_id);

            $message = _("Ihre persönliche Seite wurde von Admin verändert.\n "
                        ."Folgende Veränderungen wurden vorgenommen:\n \n")
                        . '- ' . implode("\n- ", $this->private_messages);
            $subject = _('Systemnachricht: Profil verändert');

            restoreLanguage();

            $messaging = new messaging;
            $messaging->insert_message($message, $this->user->username, '____%system%____', null, null, true, '', $subject);
        }

        // Check whether the user should be logged out, the token is
        // neccessary since the user could reload the page and will be logged
        // out immediately after, resulting in a login/logout-loop.
        $should_logout = $action === 'logout' && $this->flash['logout-token'] === Request::get('token');

        if ($should_logout) {
            $GLOBALS['sess']->delete();
            $GLOBALS['auth']->logout();
        }

        parent::after_filter($action, $args);

        if ($should_logout) {
            $GLOBALS['user']->set_last_action(time() - 15 * 60);
        }
    }
}
