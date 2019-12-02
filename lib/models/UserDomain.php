<?php
/**
 * Class representing a user domain in Stud.IP
 *
 * @author Elmar Ludwig <ludwig@uos.de>
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright 2008
 * @license GPL2 or any later version
 */
class UserDomain extends SimpleORMap
{
    const REGEXP = '^[\\w\.\-]{1,32}$';

    protected static function configure($config = [])
    {
        $config['db_table'] = 'userdomains';

        $config['has_and_belongs_to_many']['users'] = [
            'class_name'        => 'User',
            'thru_table'        => 'user_userdomains',
            'assoc_foreign_key' => 'user_id',
            'ondelete'          => 'delete',
            'onstore'           => 'store',
        ];

        $config['has_and_belongs_to_many']['courses'] = [
            'class_name'        => 'Course',
            'thru_table'        => 'seminar_userdomains',
            'ondelete'          => 'delete',
            'onstore'           => 'store',
        ];

        $config['registered_callbacks']['before_store'][] = function ($domain) {
            if (!preg_match('/' . self::REGEXP . '/', $domain->id)) {
                throw new Exception(_('Ungültige ID für Nutzerdomäne') . ': ' . $domain->id);
            }
        };

        parent::configure($config);
    }

    /**
     * Get an array of all defined user domains.
     * Returns an array of UserDomain objects.
     */
    public static function getUserDomains ()
    {
        return self::findBySQL('1 ORDER BY name');
    }

    /**
     * Add a user to this user domain.
     */
    public function addUser ($user_id)
    {
        $query = "INSERT IGNORE INTO user_userdomains (user_id, userdomain_id)
                  VALUES (:user_id, :id)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':user_id' => $user_id,
            ':id'      => $this->id,
        ]);
        NotificationCenter::postNotification('UserDomainUserDidCreate', $this->id, $user_id);
    }

    /**
     * Remove a user from this user domain.
     */
    public function removeUser ($user_id)
    {
        $query = "DELETE FROM user_userdomains
                  WHERE user_id = :user_id
                    AND userdomain_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':user_id' => $user_id,
            ':id'      => $this->id,
        ]);
        NotificationCenter::postNotification('UserDomainUserDidDelete', $this->id, $user_id);
    }

    /**
     * Get an array of all user domains for a specific user.
     * Returns an array of UserDomain objects.
     */
    public static function getUserDomainsForUser ($user_id)
    {
        $domains = User::find($user_id)->domains;
        return $domains ? $domains->getArrayCopy() : [];
    }

    /**
     * Remove all user domains for a specific user.
     */
    public static function removeUserDomainsForUser ($user_id)
    {
        $query = "DELETE FROM user_userdomains
                  WHERE user_id = :user_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':user_id' => $user_id,
        ]);
        NotificationCenter::postNotification('UserDomainUserDidDelete', 'all', $user_id);
    }

    /**
     * Add a seminar to this user domain.
     */
    public function addSeminar ($seminar_id)
    {
        $query = "INSERT IGNORE INTO seminar_userdomains (seminar_id, userdomain_id)
                  VALUES (:seminar_id, :id)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':seminar_id' => $seminar_id,
            ':id'         => $this->id,
        ]);
        NotificationCenter::postNotification('UserDomainSeminarDidCreate', $this->id, $seminar_id);
    }

    /**
     * Remove a seminar from this user domain.
     */
    public function removeSeminar ($seminar_id)
    {
        $query = "DELETE FROM seminar_userdomains
                  WHERE seminar_id = :seminar_id
                    AND userdomain_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':seminar_id' => $seminar_id,
            ':id'         => $this->id,
        ]);
        NotificationCenter::postNotification('UserDomainSeminarDidDelete', $this->id, $seminar_id);
    }

    /**
     * Get an array of all user domains for a specific seminar.
     * Returns an array of UserDomain objects.
     */
    public static function getUserDomainsForSeminar ($seminar_id)
    {
        return Course::find($seminar_id)->domains->getArrayCopy();
    }

    /**
     * Remove all user domains for a specific seminar.
     */
    public static function removeUserDomainsForSeminar ($seminar_id)
    {
        $query = "DELETE FROM seminar_userdomains
                  WHERE seminar_id = :seminar_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':seminar_id' => $seminar_id,
        ]);
        NotificationCenter::postNotification('UserDomainSeminarDidDelete', 'all', $seminar_id);
    }

    /**
     * Check the visibility for two sets of domains. The visibility is only
     * given when any of the following cases is true:
     *
     * - both sets of domains are empty
     * - the check is not strict and no domains are owned
     * - any of the owned domains has no restricted access
     * - the check is not strict and any of the domains to check has no
     *   restricted access
     *  - the two sets of domains contain at least one same domain
     *
     * @param  array   $domains_owned    Domains owned by the object
     * @param  array   $domains_to_check Domains to check against
     * @param  boolean $strict           Perform a strict check;
     *                                   optional, default: false
     * @return bool indicating whether visibility is given or not
     */
    public static function checkUserVisibility($domains_owned, $domains_to_check)
    {
        if ($domains_owned instanceof StudipArrayObject) {
            $domains_owned = $domains_owned->getArrayCopy();
        } else {
            $domains_owned = (array) $domains_owned;
        }
        if ($domains_to_check instanceof StudipArrayObject) {
            $domains_to_check = $domains_to_check->getArrayCopy();
        } else {
            $domains_to_check = (array) $domains_to_check;
        }

        // Empty sets of domains on both sides
        if (count($domains_owned) === 0 && count($domains_to_check) === 0) {
            return true;
        }

        // No domains owned, visibility is given if any domain to check is
        // unrestricted
        if (count($domains_owned) === 0) {
            return (bool) array_filter($domains_to_check, function ($domain) {
                return !$domain->restricted_access;
            });
        }

        // No domains to check against, visibility is given if any domain owned
        // is unrestricted
        if (count($domains_to_check) === 0) {
            return (bool) array_filter($domains_owned, function ($domain) {
                return !$domain->restricted_access;
            });
        }

        foreach ($domains_owned as $owned) {
            foreach ($domains_to_check as $check) {
                // Domain is the same
                if ($owned->id === $check->id) {
                    return true;
                }

                // Both domains are not restricred
                if (!$owned->restricted_access && !$check->restricted_access) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function checkCourseVisibility($domains_owned, $domains_to_check)
    {
        // No strict check and no owned domains
        if (count($domains_owned) === 0) {
            return true;
        }

        foreach ($domains_owned as $domain) {
            // Domain owned is not restricted
            if (!$domain->restricted_access) {
                return true;
            }

            foreach ($domains_to_check as $other) {
                // Domain are the same or domain to check is not restricted
                if ($domain->id === $other->id || !$other->restricted_access) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Converts the user domain into a string. This is required for array_diff()
     * purposes in order to check whether userdomains of users or user and
     * course match up.
     *
     * @return string representation of the user domain
     */
    public function __toString()
    {
        return $this->id;
    }
}
