<?php

/**
 * Seminar_Perm.class.php
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2000 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class Seminar_Perm
{

    /**
     * @var array
     */
    public $permissions = [
        "user"   => 1,
        "autor"  => 3,
        "tutor"  => 7,
        "dozent" => 15,
        "admin"  => 31,
        "root"   => 63
    ];
    /**
     * @var array
     */
    private $studip_perms = [];
    /**
     * @var array
     */
    private $fak_admins = [];

    /**
     * @return Seminar_Perm
     */
    public static function get()
    {
        if (is_object($GLOBALS['perm'])) {
            return $GLOBALS['perm'];
        } else {
            return new Seminar_Perm();
        }
    }

    /**
     * @param $must_have
     * @throws AccessDeniedException
     */
    public function check($must_have)
    {
        if (!$this->have_perm($must_have)) {
            if ($GLOBALS['user']->id === 'nobody') {
                $message = _('Sie sind nicht im System angemeldet und können daher nicht auf diesen Teil des Systems zugreifen. '
                           . 'Um den vollen Funktionsumfang des Systems benutzen zu können, müssen Sie sich mit Ihrem Nutzernamen und Passwort anmelden.');
                throw new AccessDeniedException($message);
            } else {
                throw new AccessDeniedException();
            }
        }
    }

    /**
     * @param bool $user_id
     * @return string
     */
    public function get_perm($user_id = false)
    {
        global $user;
        if (!$user_id) {
            $user_id = $user->id;
        }
        if ($user_id && $user_id == $user->id) {
            return $user->perms;
        }
        if ($user_id && isset($this->studip_perms['studip'][$user_id])) {
            return $this->studip_perms['studip'][$user_id];
        }
        if ($user_id && $user_id !== 'nobody') {
            $query = "SELECT perms FROM auth_user_md5 WHERE user_id = :user_id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $user_id);
            $statement->execute();
            $perms = $statement->fetchColumn();

            return $this->studip_perms['studip'][$user_id] = $perms;
        }
    }

    /**
     * @param $perm
     * @param bool $user_id
     * @return bool
     */
    public function have_perm($perm, $user_id = false)
    {

        $pageperm = $this->permissions[$perm];
        $userperm = $this->permissions[$this->get_perm($user_id)];

        return $pageperm <= $userperm;
    }


    /**
     * @param $range_id
     * @param bool $user_id
     * @return mixed
     */
    public function get_studip_perm($range_id, $user_id = false)
    {

        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }

        if (!isset($this->studip_perms[$range_id][$user_id])) {
            $this->studip_perms[$range_id][$user_id] = $this->get_uncached_studip_perm($range_id, $user_id);
        }
        return $this->studip_perms[$range_id][$user_id];
    }

    /**
     * @param $range_id
     * @param $user_id
     * @return bool|string
     */
    public function get_uncached_studip_perm($range_id, $user_id)
    {
        global $user;
        $db = DBManager::get();
        $status = false;
        if ($user_id && $user_id == $user->id) {
            $user_perm = $user->perms;
        } else {
            $user_perm = $this->get_perm($user_id);
            if (!$user_perm) {
                return false;
            }
        }
        if ($user_perm == "root") {
            return "root";
        } elseif ($user_perm == "admin") {
            $st = $db->prepare("SELECT seminare.Seminar_id
                          FROM user_inst
                          LEFT JOIN seminare USING (Institut_id)
                          WHERE inst_perms='admin' AND user_id = ? AND seminare.Seminar_id = ? LIMIT 1");
            $st->execute([$user_id, $range_id]);
            if ($st->fetchColumn()) {
                $status = "admin";
            } else {
                $st = $db->prepare("SELECT Seminar_id FROM user_inst a
                            LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
                            LEFT JOIN Institute c ON (b.Institut_id=c.fakultaets_id)
                            LEFT JOIN seminare d ON (d.Institut_id=c.Institut_id)
                            WHERE a.user_id = ? AND a.inst_perms='admin' AND d.Seminar_id = ? LIMIT 1");
                $st->execute([$user_id, $range_id]);
                if ($st->fetchColumn()) {
                    $status = "admin";
                } else {
                    $st = $db->prepare("SELECT a.Institut_id FROM user_inst a
                                LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)
                                WHERE user_id = ? AND a.inst_perms='admin'
                                AND b.Institut_id = ? LIMIT 1");
                    $st->execute([$user_id, $range_id]);
                    if ($st->fetchColumn()) {
                        $status = "admin";
                    }
                }
            }
        }

        if ($status) {
            return $status;
        }

        if (get_config('DEPUTIES_ENABLE') && isDeputy($user_id, $range_id)) {
            if ($_SESSION['seminar_change_view_' . $range_id]) {
                $status = $_SESSION['seminar_change_view_' . $range_id];
            } else {
                $status = 'dozent';
            }
        } else {
            $st = $db->prepare("SELECT status FROM seminar_user
                          WHERE user_id = ? AND Seminar_id = ?");
            $st->execute([$user_id, $range_id]);
            if ($status = $st->fetchColumn()) {
                if (in_array($status, words('dozent tutor')) && isset($_SESSION['seminar_change_view_' . $range_id])) {
                    $status = $_SESSION['seminar_change_view_' . $range_id];
                }
            } else {
                $st = $db->prepare("SELECT inst_perms FROM user_inst
                              WHERE user_id = ? AND Institut_id = ?");
                $st->execute([$user_id, $range_id]);
                $status = $st->fetchColumn();
            }
        }
        return $status;
    }

    /**
     * @param $perm
     * @param $range_id
     * @param bool $user_id
     * @return bool
     */
    public function have_studip_perm($perm, $range_id, $user_id = false)
    {

        $pageperm = $this->permissions[$perm];
        $userperm = $this->permissions[$this->get_studip_perm($range_id, $user_id)];

        return $pageperm <= $userperm;
    }

    /**
     * @param $range_id
     * @param bool $user_id
     * @return mixed
     */
    public function get_profile_perm($range_id, $user_id = false)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }

        if (!isset($this->studip_perms[$range_id][$user_id])) {
            $this->studip_perms[$range_id][$user_id] = $this->get_uncached_profile_perm($range_id, $user_id);
        }
        return $this->studip_perms[$range_id][$user_id];
    }

    /**
     * @param $range_id
     * @param $user_id
     * @return bool|string
     */
    public function get_uncached_profile_perm($range_id, $user_id)
    {
        $status = false;

        if ($range_id === $user_id && $this->have_perm('autor', $user_id)) {
            // user on his own profile
            $status = 'user';
        } else if (isDeputyEditAboutActivated() && isDeputy($user_id, $range_id, true)) {
            // user is an assigned deputy
            $status = 'user';
        } else if ($this->have_perm('root', $user_id)) {
            // respect root's authority
            $status = 'admin';
        } else if ($this->have_perm('admin', $user_id)) {
            // institute admin may have permission
            $db = DBManager::get();
            $stmt = $db->prepare("SELECT a.inst_perms FROM user_inst AS a " .
                "LEFT JOIN user_inst AS b USING (Institut_id) " .
                "WHERE a.user_id = ? AND a.inst_perms = 'admin' " .
                "  AND b.user_id = ? AND b.inst_perms IN ('autor', 'tutor', 'dozent')");
            $stmt->execute([$user_id, $range_id]);

            if ($stmt->fetchColumn()) {
                $status = 'admin';
            } else if ($this->is_fak_admin($user_id)) {
                $stmt = $db->prepare("SELECT a.inst_perms FROM user_inst a " .
                    "LEFT JOIN Institute i ON a.Institut_id = i.fakultaets_id " .
                    "LEFT JOIN user_inst b ON b.Institut_id = i.Institut_id " .
                    "WHERE a.user_id = ? AND a.inst_perms = 'admin' " .
                    "  AND b.user_id = ? AND b.inst_perms != 'user'");
                $stmt->execute([$user_id, $range_id]);

                if ($stmt->fetchColumn()) {
                    $status = 'admin';
                }
            }
        }

        return $status;
    }

    /**
     * @param $perm
     * @param $range_id
     * @param bool $user_id
     * @return bool
     */
    public function have_profile_perm($perm, $range_id, $user_id = false)
    {

        $pageperm = $this->permissions[$perm];
        $userperm = $this->permissions[$this->get_profile_perm($range_id, $user_id)];

        return $pageperm <= $userperm;
    }

    /**
     * @param bool $user_id
     * @return bool
     */
    public function is_fak_admin($user_id = false)
    {
        global $user;
        if (!$user_id) $user_id = $user->id;
        $user_perm = $this->get_perm($user_id);
        if ($user_perm == "root") {
            return true;
        }
        if ($user_perm != "admin") {
            return false;
        }
        if (isset($this->fak_admins[$user_id])) {
            return $this->fak_admins[$user_id];
        } else {
            $db = DBManager::get();
            $st = $db->prepare("SELECT a.Institut_id FROM user_inst a
                          LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)
                          WHERE a.user_id = ? AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id) LIMIT 1");
            $st->execute([$user_id]);
            return $this->fak_admins[$user_id] = (bool)$st->fetchColumn();
        }
    }

    /**
     * @param bool $user_id
     * @return bool
     */
    public function is_staff_member($user_id = false)
    {
        global $user;
        if (!$user_id) $user_id = $user->id;
        $user_perm = $this->get_perm($user_id);
        if ($user_perm == "root") {
            return true;
        }
        if (!$this->have_perm('autor', $user_id)) {
            return false;
        }
        $db = DBManager::get();
        $st = $db->prepare("SELECT 1 FROM user_inst
                            WHERE user_id = ? AND inst_perms <> 'user' LIMIT 1");
        $st->execute([$user_id]);
        return (bool)$st->fetchColumn();
    }
}
