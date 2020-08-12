<?php
/*
 * AvatarNavigation.php - navigation for menu "below" the avatar in header
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class AvatarNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Avatar-Menü'));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        // Link to profile
        $navigation = new Navigation(_('Profil'), 'dispatch.php/profile/index');
        $navigation->setImage(Icon::create('person'));
        $this->addSubNavigation('profile', $navigation);

        if ($GLOBALS['perm']->have_perm('autor')) {
            // Link to user data
            $navigation = new Navigation(_('Persönliche Angaben'), 'dispatch.php/settings/account');
            $navigation->setImage(Icon::create('key'));
            $this->addSubNavigation('account', $navigation);

            // Link to user settings
            $navigation = new Navigation(_('Einstellungen'), 'dispatch.php/settings/general');
            $navigation->setImage(Icon::create('admin'));
            $this->addSubNavigation('settings', $navigation);
        }

        // Link to logout
        $navigation = new Navigation(_('Logout'), 'logout.php');
        $navigation->setImage(Icon::create('door-leave'));
        $this->addSubNavigation('logout', $navigation);
    }
}
