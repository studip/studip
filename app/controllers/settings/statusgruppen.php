<?php
/**
 * Settings_StatusgruppenController - Administration of all user and
 * statusgruppen related settings
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

class Settings_StatusgruppenController extends Settings_SettingsController
{
    /**
     * Set up this controller
     *
     * @param String $action Name of the action to be invoked
     * @param Array $args    Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        require_once 'lib/statusgruppe.inc.php';

        PageLayout::setHelpKeyword('Basis.HomepageUniversitäreDaten');
        PageLayout::setTitle(_('Einrichtungsdaten bearbeiten'));
        Navigation::activateItem('/profile/edit/statusgruppen');
        SkipLinks::addIndex(_('Einrichtungsdaten bearbeiten'), 'layout_content', 100);

        Sidebar::get()->setImage('sidebar/group-sidebar.png');
    }

    /**
     * Displays the statusgruppen of a user.
     *
     * @param mixed $verify_action Optional name of an action to be verified
     * @param mixed $verify_id     Optional id that belongs to the action to
     *                             be verified
     */
    public function index_action($verify_action = null, $verify_id = null)
    {
        $all_rights = false;
        if ($this->user->username != $GLOBALS['user']->username) {
            $query = "SELECT Institut_id
                      FROM Institute
                      WHERE fakultaets_id = ? AND fakultaets_id != Institut_id
                      ORDER BY Name";
            $inner_statement = DBManager::get()->prepare($query);

            $parameters = [];
            if ($GLOBALS['perm']->have_perm('root')) {
                $all_rights = true;
                $query = "SELECT Institut_id, Name, 1 AS is_fak
                          FROM Institute
                          WHERE Institut_id = fakultaets_id
                          ORDER BY Name";
            } elseif ($GLOBALS['perm']->have_perm('admin')) {
                $query = "SELECT Institut_id, Name, b.Institut_id = b.fakultaets_id AS is_fak
                          FROM user_inst AS a
                          LEFT JOIN Institute AS b USING (Institut_id)
                          WHERE a.user_id = ? AND a.inst_perms = 'admin'
                          ORDER BY is_fak, Name";
                $parameters[] = $GLOBALS['user']->id;
            } else {
                $query = "SELECT a.Institut_id, Name
                          FROM user_inst AS a
                          LEFT JOIN Institute AS b USING (Institut_id)
                          WHERE inst_perms IN ('tutor', 'dozent') AND user_id = ?
                          ORDER BY Name";
                $parameters[] = $GLOBALS['user']->id;
            }

            $statement = DBManager::get()->prepare($query);
            $statement->execute($parameters);
            $institutes = $statement->fetchAll(PDO::FETCH_ASSOC);

            $admin_insts = [];
            foreach ($institutes as $institute) {
                $institute['groups'] = GetAllStatusgruppen($institute['Institut_id'], $this->user->id) ?: [];

                if ($institute['is_fak']) {
                    $stmt = DBManager::get()->prepare("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id = ? AND Institut_id != fakultaets_id ORDER BY Name");
                    $stmt->execute([$institute['Institut_id']]);
                    $institute['sub'] = $stmt->fetchGrouped(PDO::FETCH_ASSOC);
                    foreach ($institute['sub'] as $id => $sub) {
                        $sub['groups']         = GetAllStatusgruppen($id, $this->user->id) ?: [];
                        $institute['sub'][$id] = $sub;
                    }
                }

                $admin_insts[] = $institute;
            }
        } else {
            $all_rights = true;
        }

        // get the roles the user is in
        $institutes = [];
        foreach ($this->user->institute_memberships as $institute_membership) {
            if ($institute_membership->inst_perms !== 'user') {
                $institutes[$institute_membership->institut_id] = $institute_membership->toArray() + $institute_membership->institute->toArray();

                $roles = GetAllStatusgruppen($institute_membership->institut_id, $this->user->user_id, true);
                $institutes[$institute_membership->institut_id]['roles'] = $roles ?: [];

                $institutes[$institute_membership->institut_id]['flattened'] = array_filter(getFlattenedRoles($roles), function ($role) {
                    return $role['user_there'];
                });

                $datafields = [];
                foreach ($institutes[$institute_membership->institut_id]['flattened'] as $role_id => $role) {
                    $datafields[$role_id] = DataFieldEntry::getDataFieldEntries([$this->user->user_id, $role_id]) ?: [];
                }
                $institutes[$institute_membership->institut_id]['datafields'] = $datafields;
            }
        }

        $this->institutes = $institutes;

        $this->verify_action = $verify_action;
        $this->verify_id     = $verify_id;

        // data for edit_about_add_person_to_role
        $this->admin_insts = $admin_insts;

        $this->locked = !$this->shallChange('', 'institute_data');
        if ($this->locked) {
            $message = LockRules::getObjectRule($this->user->user_id)->description;
            if ($message) {
                PageLayout::postInfo($message);
            }
        }
    }

    /**
     * Set defaults for a single datafield of a statusgruppe.
     *
     * @param String $inst_id      Id of the institute in question
     * @param String $role_id      Id of the statusgruppe in question
     * @param String $datafield_id Id of the datafield in question
     * @param bool $state          Indicates whether the defaults should be used or not
     */
    public function default_action($inst_id, $role_id, $datafield_id, $state)
    {
        $value = 'default_value';
        if (!$state) {
            $defaults = DataFieldEntry::getDataFieldEntries([$this->user->user_id, $inst_id]);
            $value    = $defaults[$datafield_id]->getValue();
        }

        $entry = new DatafieldEntryModel([$datafield_id, $this->user->user_id, $role_id, '']);
        $entry->content = $value;
        $entry->store();

        $this->redirect($this->url_for('settings/statusgruppen', ['contentbox_open' => $role_id, 'type' => 'role']));
    }

    /**
     * Set defaults for all datafields of a statusgruppe.
     *
     * @param String $role_id Id of the statusgruppe in question
     * @param bool $state     Indicates whether the defaults should be used or not
     */
    public function defaults_action($role_id, $state)
    {
        $datafields = StatusgruppeUser::find([$role_id, $this->user->user_id])->datafields;
        if ($state) {
            $datafields->each(function ($datafield) {
                $datafield->content = 'default_value';
                $datafield->store();
            });
        } else {
            $datafields->delete();
        }

        $this->redirect($this->url_for('settings/statusgruppen', ['contentbox_open' => $role_id, 'type' => 'role']));
    }

    /**
     * Assign/add a user to a statusgruppe.
     */
    public function assign_action()
    {
        $this->check_ticket();

        $role_id = Request::option('role_id');
        if ($role_id) {
            $group = Statusgruppen::find($role_id);

            // Get institute from group
            $temp = $group;
            while ($temp->parent) {
                $temp = $temp->parent;
            }
            $range_id = $temp->range_id;
            if (!Institute::find($range_id)) {
                throw new RuntimeException('Selected group does not belong to an institute');
            }

            // Try to add group member
            if ($group->isMember($this->user->id)) {
                PageLayout::postInfo(_('Die Person ist bereits in der Gruppe eingetragen.'));
            } elseif (!$group->addUser($this->user->id)) {
                PageLayout::postError(_('Fehler beim Eintragen in die Gruppe!'));
            } else {
                $member  = new InstituteMember([$this->user->id, $range_id]);
                $member->inst_perms = $this->user->perms;

                $was_new   = $member->isNew();
                $was_dirty = $member->isDirty();

                $member->store();

                if ($was_new) {
                    StudipLog::log('INST_USER_ADD', $range_id, $this->user->id, $member->inst_perms);
                    NotificationCenter::postNotification('UserInstitutionDidCreate', $range_id, $this->user->id);
                } elseif ($was_dirty) {
                    StudipLog::log('INST_USER_STATUS', $range_id, $this->user->id, $member->inst_perms);
                    NotificationCenter::postNotification('UserInstitutionPermDidUpdate', $range_id, $this->user->id);
                }

                InstituteMember::ensureDefaultInstituteForUser($this->user->id);

                $_SESSION['edit_about_data']['open'] = $role_id;
                PageLayout::postSuccess(_('Die Person wurde in die ausgewählte Gruppe eingetragen!'));
            }
        }

        $this->redirect('settings/statusgruppen#' . $role_id);
    }

    /**
     * Removes a user from a statusgruppe.
     *
     * @param String $id     Id of the statusgruppe in question
     * @param bool $verified Indicates whether the action has been verified
     */
    public function delete_action($id, $verified = false)
    {
        if ($verified) {
            $this->check_ticket();

            Statusgruppen::find($id)->removeUser($this->user->id);
            PageLayout::postSuccess(_('Die Person wurde aus der ausgewählten Gruppe gelöscht!'));
        }

        $this->redirect('settings/statusgruppen');
    }

    /**
     * Moves a specific statusgruppe into the given direction.
     *
     * @param String $id        Id of the statusgruppe in question
     * @param String $direction Either 'up' or 'down'
     */
    public function move_action($id, $direction)
    {
        if (in_array($GLOBALS['perm']->get_profile_perm($this->user->user_id), words('user admin'))) {

            $query = "SELECT Institut_id
                      FROM user_inst
                      WHERE user_id = ? AND inst_perms != 'user'
                      ORDER BY priority ASC";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->user->user_id]);
            $institutes = $statement->fetchAll(PDO::FETCH_COLUMN);
            $priorities = array_flip($institutes);

            $changed  = false;
            $priority = $priorities[$id];
            if ($direction === 'down' && $priority + 1 < count($priorities)) {
                $priorities[$id]                        = $priority + 1;
                $priorities[$institutes[$priority + 1]] = $priority;
                $changed                                = true;
            } else if ($direction === 'up' && $priority > 0) {
                $priorities[$id]                        = $priority - 1;
                $priorities[$institutes[$priority - 1]] = $priority;
                $changed                                = true;
            }

            if ($changed) {
                $query = "UPDATE user_inst
                          SET priority = ?
                          WHERE user_id = ? AND Institut_id = ?";
                $statement = DBManager::get()->prepare($query);

                foreach ($priorities as $id => $priority) {
                    $statement->execute([
                        $priority,
                        $this->user->user_id,
                        $id,
                    ]);
                }
                PageLayout::postSuccess(_('Reihenfolge wurde geändert'));
            }
        }
        $this->redirect('settings/statusgruppen#' . $id);
    }


    /**
     * Stores the statusgruppen of a user.
     */
    public function store_action($type, $id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $changed = false;
        $success = [];
        $errors  = [];

        if ($type === 'institute') {

            if ($status = Request::option('status')) {
                $query     = "SELECT inst_perms FROM user_inst WHERE user_id = ? AND Institut_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$this->user->user_id, $id]);
                $perms = $statement->fetchColumn();

                if (($status != $perms) && in_array($status, $this->user->getInstitutePerms())) {
                    $query     = "UPDATE user_inst SET inst_perms = ? WHERE user_id = ? AND Institut_id = ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([
                        $status,
                        $this->user->user_id,
                        $id,
                    ]);

                    StudipLog::log('INST_USER_STATUS', $id, $this->user->user_id, $perms . ' -> ' . $status);
                    NotificationCenter::postNotification('UserInstitutionPermDidUpdate', $id, $this->user->user_id);

                    $success[] = _('Der Status wurde geändert!');
                }
            }

            if ($this->shallChange('', 'institute_data')) {
                $query = "UPDATE user_inst
                          SET raum = ?, sprechzeiten = ?, Telefon = ?, Fax = ?
                          WHERE Institut_id = ? AND user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    Request::get('raum'),
                    Request::get('sprech'),
                    Request::get('tel'),
                    Request::get('fax'),
                    $id,
                    $this->user->user_id,
                ]);
                if ($statement->rowCount() > 0) {
                    $changed   = true;
                    $success[] = sprintf(_('Ihre Daten an der Einrichtung %s wurden geändert.'), htmlReady(Request::get('name')));

                    setTempLanguage($this->user->user_id);
                    $this->postPrivateMessage(_('Ihre Daten an der Einrichtung %s wurden geändert.') . "\n", Request::get('name'));
                    restoreLanguage();
                }
            }

            if ($default_institute = Request::int('default_institute', 0)) {
                $query     = "UPDATE user_inst SET externdefault = 0 WHERE user_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$this->user->user_id]);
            }

            $query = "UPDATE user_inst
                      SET externdefault = ?, visible = ?
                      WHERE Institut_id = ? AND user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $default_institute,
                Request::int('invisible', 0) ? 0 : 1,
                $id,
                $this->user->user_id,
            ]);
        }
        if (in_array($type, words('institute role'))) {
            if ($datafields = Request::getArray('datafields')) {
                foreach ($datafields as $key => $value) {
                    $struct = new DataField($key);
                    $entry  = DataFieldEntry::createDataFieldEntry($struct, [$this->user->user_id, $id]);
                    $entry->setValueFromSubmit($value);

                    if ($entry->isValid()) {
                        if ($entry->store() && !$changed && $type === 'institute') {
                            $changed   = true;
                            $success[] = sprintf(_('Ihre Daten an der Einrichtung %s wurden geändert.'), htmlReady(Institute::find($id)->name)
                            );
                        }
                    } else {
                        $errors[] = sprintf(_('Fehlerhafter Eintrag im Feld <em>%s</em>: %s (Eintrag wurde nicht gespeichert)'),
                            htmlReady($entry->getName()),
                            $entry->getDisplayValue(true));
                    }
                }
            }
        }

        if (count($success) > 0) {
            PageLayout::postSuccess(_('Änderung erfolgreich'), $success);

            if (count($errors) > 0) {
                PageLayout::postWarning(_('Bei der Verarbeitung sind allerdings folgende Fehler aufgetreten'), $errors);
            }
        } elseif (count($errors) === 1) {
            PageLayout::postError($errors);
        } elseif (count($errors) > 0) {
            PageLayout::postError(_('Fehler bei der Speicherung Ihrer Daten. Bitte überprüfen Sie Ihre Angaben.'), $errors);
        }


        $this->redirect($this->url_for('settings/statusgruppen',
            ['contentbox_open' => $id, 'type' => strtolower($type)]));
    }

    public function verify_action($action, $id = null)
    {
        if ($action === 'delete' && $id) {
            PageLayout::postQuestion(
                _('Wollen Sie die Zuordnung zu der Funktion wirklich löschen?'),
                $this->url_for("settings/statusgruppen/delete/{$id}/1")
            )->includeTicket();
        }

        $this->redirect('settings/statusgruppen');
    }
}
