<?php
/**
 * ProfilNavigation.php - navigation for user profile page
 *
 * Navigation for the user's profile page. This page includes all
 * information about a user and allows editing this data.
 *
 * @author   Elmar Ludwig
 * @license  GPL version 2 or any later version
 * @category Stud.IP
 */
class ProfileNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Profil'));
    }

    public function initItem()
    {
        parent::initItem();

        $this->setURL('dispatch.php/profile');
        $this->setImage(Icon::create('person', Icon::ROLE_NAVIGATION));
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        $active = parent::isActive();

        if ($active) {
            URLHelper::addLinkParam('username', Request::username('username'));
        }

        return $active;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $user, $perm;

        parent::initSubNavigation();

        $username = Request::username('username', $user->username);
        $current_user = User::findByUsername($username);

        // profile
        $navigation = new Navigation(_('Profil'), 'dispatch.php/profile/index');
        $this->addSubNavigation('index', $navigation);

        if ($current_user && $perm->have_profile_perm('user', $current_user->user_id)) {
            // profile data
            $navigation = new Navigation(_('Persönliche Angaben'));
            $navigation->addSubNavigation('profile', new Navigation(_('Grunddaten'), 'dispatch.php/settings/account'));
            if (($perm->get_profile_perm($current_user->user_id) == 'user'
                || ($perm->have_perm('root') && Config::get()->ALLOW_ADMIN_USERACCESS))) {
                $navigation->addSubNavigation('password', new Navigation(
                    _('Passwort ändern'),
                    'dispatch.php/settings/password'
                ));
            }
            $navigation->addSubNavigation('details', new Navigation(_('Weitere Daten'), 'dispatch.php/settings/details'));

            if (!in_array($current_user->perms, words('user admin root'))) {
                $navigation->addSubNavigation('studies', new Navigation(_('Studiendaten'), 'dispatch.php/settings/studies'));
            }

            if ($current_user->perms !== 'root') {
                if (count(UserDomain::getUserDomains()) > 0) {
                    $navigation->addSubNavigation('userdomains', new Navigation(_('Nutzerdomänen'), 'dispatch.php/settings/userdomains'));
                }

                if ($perm->is_staff_member($current_user->user_id)) {
                    $navigation->addSubNavigation('statusgruppen', new Navigation(_('Einrichtungsdaten'), 'dispatch.php/settings/statusgruppen'));
                }
            }

            $this->addSubNavigation('edit', $navigation);

            if ($perm->have_perm('autor')) {
                $navigation = new Navigation(_('Einstellungen'));

                $navigation->addSubNavigation('general', new Navigation(_('Allgemeines'), 'dispatch.php/settings/general'));
                $navigation->addSubNavigation('privacy', new Navigation(_('Privatsphäre'), 'dispatch.php/settings/privacy'));
                $navigation->addSubNavigation('messaging', new Navigation(_('Nachrichten'), 'dispatch.php/settings/messaging'));

                if (Config::get()->CALENDAR_ENABLE) {
                    $navigation->addSubNavigation('calendar_new', new Navigation(_('Terminkalender'), 'dispatch.php/settings/calendar'));
                }

                if (!$perm->have_perm('admin') && Config::get()->MAIL_NOTIFICATION_ENABLE) {
                    $navigation->addSubNavigation('notification', new Navigation(_('Benachrichtigung'), 'dispatch.php/settings/notification'));
                }

                if (Deputy::isActivated() && $perm->get_perm($current_user->user_id) === 'dozent') {
                    $navigation->addSubNavigation('deputies', new Navigation(_('Standardvertretung'), 'dispatch.php/settings/deputies'));
                }

                if (Config::Get()->API_ENABLED) {
                    $navigation->addSubNavigation('api', new Navigation(_('API-Berechtigungen'), 'dispatch.php/api/authorizations'));
                }

                if (TwoFactorAuth::isEnabledForUser()) {
                    $navigation->addSubNavigation('tfa', new Navigation(_('Zwei-Faktor-Authentifizierung'), 'dispatch.php/tfa'));
                }

                $this->addSubNavigation('settings', $navigation);
            }

            // user defined sections
            $navigation = new Navigation(_('Kategorien'), 'dispatch.php/settings/categories');
            $this->addSubNavigation('categories', $navigation);
        }

        // Add consultations if appropriate
        if (Config::get()->CONSULTATION_ENABLED) {
            $plugin = PluginEngine::getPlugin('ConsultationModule');
            if ($current_user && $plugin && $plugin->isActivated($current_user->id, 'user')) {
                $this->addSubNavigation('consultation', new ConsultationNavigation($current_user));
            }
        }
    }
}
