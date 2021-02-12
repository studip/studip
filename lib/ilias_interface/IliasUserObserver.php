<?php
/**
 * This class observes changes in user data and updates ILIAS users
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.3
 */
class IliasUserObserver
{
    /**
     * Observe user updates.
     */
    public static function initialize()
    {
        NotificationCenter::addObserver(self::class, 'observeIliasUser', 'UserDidUpdate');
        NotificationCenter::addObserver(self::class, 'observeIliasUser', 'UserDidDelete');
    }

    /**
     * Update user data for all ILIAS instances
     *
     * @param User $user  the observed user
     */
    public static function observeIliasUser($event, User $user)
    {
        switch ($event) {
            case 'UserDidUpdate':
                foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
                    if ($ilias_config['is_active']) {
                        $ilias = new ConnectedIlias($ilias_index);
                        $ilias->updateUser($user);
                    }
                }
                break;
            case 'UserDidDelete':
                foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
                    if ($ilias_config['delete_ilias_users']) {
                        $ilias = new ConnectedIlias($ilias_index);
                        $ilias->deleteUser($user);
                    }
                }
                break;
        }
    }
}