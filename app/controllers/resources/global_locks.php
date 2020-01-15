<?php

/**
 * global_locks.php - contains Resources_GlobalLocksController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2018
 * @category    Stud.IP
 * @since       TODO
 */


/**
 * Resources_AdminController contains actions
 * related to global resource management locks.
 */
class Resources_GlobalLocksController extends AuthenticatedController
{
    public function add_action()
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        if (Navigation::hasItem('/room_management/admin/global_locks')) {
            Navigation::activateItem('/room_management/admin/global_locks');
        }

        $this->begin = new DateTime();
        $this->begin->add(
            new DateInterval('P1D')
        );
        $this->begin->setTime(0,0,0);
        $this->end = clone $this->begin;
        $this->end->add(
            new DateInterval('P7D')
        );

        $this->defined_types = GlobalResourceLock::getDefinedTypes();
        $this->selected_type = 'default';

        $this->show_form = true;

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            $begin_date = Request::get('begin_date');
            $begin_time = Request::get('begin_time');
            $end_date = Request::get('end_date');
            $end_time = Request::get('end_time');
            $this->selected_type = Request::get('selected_type');

            $begin = DateTime::createFromFormat(
                'd.m.Y H:i:s',
                trim($begin_date) . ' ' . trim($begin_time) . ':00',
                $this->begin->getTimezone()
            );
            if (!$begin) {
                PageLayout::postError(
                    _('Der angegebene Startzeitpunkt ist ungültig!')
                );
                return;
            }
            $this->begin = $begin;

            $end = DateTime::createFromFormat(
                'd.m.Y H:i:s',
                trim($end_date) . ' ' . trim($end_time) . ':00',
                $this->end->getTimezone()
            );
            if (!$end) {
                PageLayout::postError(
                    _('Der angegebene Endzeitpunkt ist ungültig!')
                );
                return;
            }
            $this->end = $end;

            if ($this->begin > $this->end) {
                PageLayout::postError(
                    _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
                );
                return;
            }

            $lock = new GlobalResourceLock();
            $lock->begin = $this->begin->getTimestamp();
            $lock->end = $this->end->getTimestamp();
            $lock->type = $this->selected_type;
            if ($lock->store()) {
                PageLayout::postSuccess(
                    _('Die Sperrung wurde hinzugefügt!')
                );
                $this->show_form = false;
            } else {
                PageLayout::postError(
                    _('Fehler beim Hinzufügen der Sperrung!')
                );
            }
        }
    }


    public function edit_action($lock_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'autor')) {
            throw new AccessDeniedException();
        }

        if (Navigation::hasItem('/room_management/admin/global_locks')) {
            Navigation::activateItem('/room_management/admin/global_locks');
        }

        $this->lock = GlobalResourceLock::find($lock_id);
        if (!$this->lock) {
            PageLayout::postError(
                _('Die angegebene Sperrung wurde nicht in der Datenbank gefunden!')
            );
            return;
        }

        $this->begin = new DateTime();
        $this->begin->setTimestamp($this->lock->begin);
        $this->end = new DateTime();
        $this->end->setTimestamp($this->lock->end);

        $this->defined_types = GlobalResourceLock::getDefinedTypes();
        $this->selected_type = $this->lock->type;

        $this->show_form = true;

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();
            $begin_date = Request::get('begin_date');
            $begin_time = Request::get('begin_time');
            $end_date = Request::get('end_date');
            $end_time = Request::get('end_time');
            $this->selected_type = Request::get('selected_type');

            $begin = DateTime::createFromFormat(
                'd.m.Y H:i:s',
                trim($begin_date) . ' ' . trim($begin_time) . ':00',
                $this->begin->getTimezone()
            );
            if (!$begin) {
                PageLayout::postError(
                    _('Der angegebene Startzeitpunkt ist ungültig!')
                );
                return;
            }
            $this->begin = $begin;

            $end = DateTime::createFromFormat(
                'd.m.Y H:i:s',
                trim($end_date) . ' ' . trim($end_time) . ':00',
                $this->end->getTimezone()
            );
            if (!$end) {
                PageLayout::postError(
                    _('Der angegebene Endzeitpunkt ist ungültig!')
                );
                return;
            }
            $this->end = $end;

            if ($this->begin > $this->end) {
                PageLayout::postError(
                    _('Der Startzeitpunkt darf nicht hinter dem Endzeitpunkt liegen!')
                );
                return;
            }

            $this->lock->begin = $this->begin->getTimestamp();
            $this->lock->end = $this->end->getTimestamp();
            $this->lock->type = $this->selected_type;

            $success = false;
            if ($this->lock->isDirty()) {
                if ($this->lock->store()) {
                    $success = true;
                }
            } else {
                //No changes? That's also a sort of success.
                $success = true;
            }

            if ($success) {
                PageLayout::postSuccess(
                    _('Die Sperrung wurde bearbeitet!')
                );
                $this->show_form = false;
            } else {
                PageLayout::postError(
                    _('Fehler beim Bearbeiten der Sperrung!')
                );
            }
        }
    }
    
    public function delete_action($lock_id = null)
    {
        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }

        if (Navigation::hasItem('/room_management/admin/global_locks')) {
            Navigation::activateItem('/room_management/admin/global_locks');
        }

        $this->lock = GlobalResourceLock::find($lock_id);
        if (!$this->lock) {
            PageLayout::postError(
                _('Die angegebene Sperrung wurde nicht in der Datenbank gefunden!')
            );
            return;
        }

        $this->show_data = true;
        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($this->lock->delete()) {
                PageLayout::postSuccess(
                    _('Die Sperrung wurde gelöscht!')
                );
                $this->show_data = false;
            } else {
                PageLayout::postError(
                    _('Fehler beim Löschen der Sperrung!')
                );
            }
        }
    }
}
