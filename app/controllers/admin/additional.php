<?php
/**
 * additonal.php - controller class for the additonal data
 *
 * Admin of a Seminar can chose his required aux data and decide if it is
 * forced from the user
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       3.0
 */
class Admin_AdditionalController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Load the course
        $this->course = Course::findCurrent();

        // Check permissions to be on this site
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $this->course->id)) {
            throw new AccessDeniedException(_("Sie haben keine Berechtigung diese " .
                    "Veranstaltung zu verändern."));
        }
    }

    /**
     * Index displays and updates
     */
    public function index_action()
    {
        /*
         * Updaterequest
         */
        if (Request::submitted('save')) {
            if ($rule = Request::get('aux_data')) {
                $this->course->aux_lock_rule = $rule;
                $this->course->aux_lock_rule_forced = Request::get('forced') ? : 0;
            } else {
                // If no rule is set we cant force it
                $this->course->aux_lock_rule = null;
                $this->course->aux_lock_rule_forced = 0;
            }
            // purge data
            if (Request::submitted('delete')) {
                $stmt = DBManager::get()->prepare('DELETE FROM datafields_entries WHERE sec_range_id = ?');
                $stmt->execute([$this->course->id]);
            }

            if ($this->course->store()) {
                if (!is_null($this->course->aux_lock_rule)) {
                    PageLayout::postSuccess(_('Zusatzangaben wurden erfolgreich zugeordnet'));
                } else {
                    PageLayout::postSuccess(_('Zuweisung der Zusatzangaben wurden aufgehoben'));
                }
            }
        }

        // Fetch data
        $stmt = DBManager::get()->prepare('SELECT COUNT(*) FROM datafields_entries WHERE sec_range_id = ?');
        $stmt->execute([$this->course->id]);
        $this->count = $stmt->fetchColumn();
        $this->list = AuxLockRule::findBySQL('1=1');
    }
}
