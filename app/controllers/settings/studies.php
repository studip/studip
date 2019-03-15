<?php

/**
 * Settings_StudiesController - Administration of all user studies related
 * settings
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
require_once 'settings.php';

class Settings_StudiesController extends Settings_SettingsController
{

    /**
     * Set up this controller.
     *
     * @param String $action Name of the action to be invoked
     * @param Array $args    Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!in_array($this->user->perms, words('autor tutor dozent'))) {
            throw new AccessDeniedException();
        }

        PageLayout::setHelpKeyword('Basis.HomepageUniversitäreDaten');
        PageLayout::setTitle(_('Studiengang bearbeiten'));
        Navigation::activateItem('/profile/edit/studies');
        SkipLinks::addIndex(_('Fächer und Abschlüsse auswählen'), 'select_fach_abschluss');
        SkipLinks::addIndex(_('Zu Einrichtungen zuordnen'), 'select_institute');

        $this->allow_change = [
            'sg' => !StudipAuthAbstract::CheckField('studiengang_id', $this->user->auth_plugin)
                    && (Config::get()->ALLOW_SELFASSIGN_STUDYCOURSE || $GLOBALS['perm']->have_perm('admin')),
            'in' => Config::get()->ALLOW_SELFASSIGN_INSTITUTE || $GLOBALS['perm']->have_perm('admin'),
        ];
    }

    /**
     * Displays the study information of a user.
     */
    public function index_action()
    {
        $this->faecher              = StudyCourse::findBySQL('1 ORDER BY name');
        $this->abschluesse          = Abschluss::findBySQL('1 ORDER by name');
        $this->available_institutes = Institute::getInstitutes();
        $this->institutes           = $this->user->institute_memberships->filter(function ($a) {
            return $a->inst_perms === 'user';
        });
    }

    /**
     * Stores the study information of a user (subject and degree-wise).
     */
    public function store_sg_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $any_change = false;

        $fach_abschluss_delete = Request::getArray('fach_abschluss_delete');
        if (count($fach_abschluss_delete) > 0) {
            foreach ($fach_abschluss_delete as $fach_id => $abschluesse) {
                $row_count  = UserStudyCourse::deleteBySQL(
                    'user_id = ? AND fach_id = ? AND abschluss_id IN (?)',
                    [$this->user->user_id, $fach_id, $abschluesse]);
                $any_change = $row_count > 0;

                // if we have no studies anymore we delete the visibilitysetting
                if (!$this->hasStudiengang()) {
                    Visibility::removePrivacySetting('studying');
                }
            }
        }

        if (!$any_change) {
            $change_fachsem = Request::getArray('change_fachsem');
            foreach ($change_fachsem as $fach_id => $abschluesse) {
                foreach ($abschluesse as $abschluss_id => $semester) {
                    $user_stc = UserStudyCourse::find([
                        $this->user->user_id,
                        $fach_id,
                        $abschluss_id
                    ]);
                    if ($user_stc) {
                        $user_stc->semester = $semester;
                        $any_change = $user_stc->store() !== false;
                    }
                }
            }

            $new_studiengang = Request::option('new_studiengang');
            if ($new_studiengang && $new_studiengang != 'none') {
                if (!$this->hasStudiengang()) {
                    Visibility::addPrivacySetting(_('Wo ich studiere'), 'studying', 'studdata');
                }
                $abschluss_id = Request::option('new_abschluss');
                $duplicate    = UserStudyCourse::exists([
                    $this->user->user_id,
                    $new_studiengang,
                    $abschluss_id,
                ]);

                if ($duplicate) {
                    PageLayout::postInfo(_('Dieser Studiengang ist bereits eingetragen'));
                } else {
                    $any_change = !is_null(UserStudyCourse::create([
                        'user_id'      => $this->user->user_id,
                        'fach_id'      => $new_studiengang,
                        'semester'     => Request::int('fachsem'),
                        'abschluss_id' => Request::option('new_abschluss'),
                    ]));
                }
            }

            // store versions if module management is enabled
            $change_versions = Request::getArray('change_version');
            foreach ($change_versions as $fach_id => $abschluesse) {
                foreach ($abschluesse as $abschluss_id => $version_id) {
                    $version = reset(StgteilVersion::findByFachAbschluss(
                        $fach_id, $abschluss_id, $version_id));
                    if ($version && $version->hasPublicStatus('genehmigt')) {
                        $user_stc = UserStudyCourse::find([
                            $this->user->user_id,
                            $fach_id,
                            $abschluss_id
                        ]);
                        if ($user_stc) {
                            $user_stc->version_id = $version->getId();
                            $any_change = $user_stc->store() !== false;
                        }
                    }
                }
            }
        }

        if ($any_change) {
            PageLayout::postSuccess(_('Die Zuordnung zu Studiengängen wurde geändert.'));

            setTempLanguage($this->user->user_id);
            $this->postPrivateMessage(_("Die Zuordnung zu Studiengängen wurde geändert!\n"));
            restoreLanguage();
        }

        $this->redirect('settings/studies');
    }

    /**
     * Stores the study information of a user (institute-wise).
     */
    public function store_in_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $inst_delete = Request::optionArray('inst_delete');
        if (count($inst_delete) > 0) {
            $query     = "DELETE FROM user_inst WHERE user_id = ? AND Institut_id = ?";
            $statement = DBManager::get()->prepare($query);

            foreach ($inst_delete as $institute_id) {
                $statement->execute([
                    $this->user->user_id,
                    $institute_id,
                ]);
                if ($statement->rowCount() > 0) {
                    StudipLog::log('INST_USER_DEL', $institute_id, $this->user->user_id);
                    NotificationCenter::postNotification('UserInstitutionDidDelete', $institute_id, $this->user->user_id);
                    $delete = true;
                }
            }
        }

        $new_inst = Request::option('new_inst');
        if ($new_inst) {

            $query
                       = "INSERT IGNORE INTO user_inst
                        (user_id, Institut_id, inst_perms)
                      VALUES (?, ?, 'user')";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->user->user_id,
                $new_inst,
            ]);
            if ($statement->rowCount() > 0) {
                StudipLog::log('INST_USER_ADD', $new_inst, $this->user->user_id, 'user');
                NotificationCenter::postNotification('UserInstitutionDidCreate', $new_inst, $this->user->user_id);

                $new = true;
            }
        }

        if ($delete || $new) {
            PageLayout::postSuccess(_('Die Zuordnung zu Einrichtungen wurde geändert.'));

            setTempLanguage($this->user->user_id);
            $this->postPrivateMessage(_("Die Zuordnung zu Einrichtungen wurde geändert!\n"));
            restoreLanguage();
        }

        $this->redirect('settings/studies');
    }

    private function hasStudiengang()
    {
        $count = UserStudyCourse::countBySql('user_id = ?', [$this->user->user_id]);
        return $count > 0;
    }
}
