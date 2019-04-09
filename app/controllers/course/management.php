<?php
# Lifter010: TODO

/*
 * management.php - realises a redirector for administrative pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      tgloeggl <tgloeggl@uos.de>
 * @author      aklassen <andre.klassen@elan-ev.de>
 * @author      dsiegfried <david.siegfried@uni-vechta.de>
 * @copyright   2010 ELAN e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       1.10
 */

class Course_ManagementController extends AuthenticatedController
{
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$GLOBALS['perm']->have_studip_perm("tutor", $GLOBALS['SessionSeminar'])) {
            throw new Trails_Exception(400);
        }
        if (Context::isCourse()) {
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][Context::get()->status]['class']] ?: SemClass::getDefaultSemClass();
        } else {
            $sem_class = SemClass::getDefaultInstituteClass(Context::get()->type);
        }
        if (!$sem_class->isModuleAllowed("CoreAdmin")) {
            throw new Exception(_('Dies ist eine Studiengruppe und kein Seminar!'));
        }

        PageLayout::setTitle(sprintf(_("%s - Verwaltung"), Context::getHeaderLine()));
        PageLayout::setHelpKeyword('Basis.InVeranstaltungVerwaltung');
    }

    /**
     * shows index page of course or institute management
     *
     * @return void
     */
    function index_action()
    {
        Navigation::activateItem('course/admin/main');

        if (Context::isInstitute()) {
            Helpbar::get()->addPlainText(_('Information'), _('Als Mitarbeiter Ihrer Einrichtung können Sie für diese Inhalte in mehreren Kategorien bereitstellen.Inhalte in Ihrer Einrichtung können von allen Stud.IP-Nutzern abgerufen werden.'));
        } else {
            Helpbar::get()->addPlainText(_('Information'), _('Sie können hier Ihre Veranstaltung in mehreren Kategorien anpassen. Informationen wie Grunddaten oder Termine und Einstellungen, Zugangsbeschränkungen und Funktionen können Sie hier administrieren.'));
        }

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $course = Course::findCurrent();
        if ($course) {
            $actions = new ActionsWidget();
            if ($GLOBALS['perm']->have_perm($sem_create_perm)) {
                if (!LockRules::check($course->id, 'seminar_copy')) {
                    $actions->addLink(
                        _('Veranstaltung kopieren'),
                        URLHelper::getURL($this->url_for('course/wizard/copy/'.$course->id), ['studip_ticket' => Seminar_Session::get_ticket()]),
                        Icon::create('seminar+add')
                    );
                }

                if (Config::get()->ALLOW_DOZENT_DELETE || $GLOBALS['perm']->have_perm('admin')) {
                    $actions->addLink(
                        _('Veranstaltung löschen'),
                        URLHelper::getURL( $this->url_for('course/archive/confirm'), ['studip_ticket' => Seminar_Session::get_ticket()]),
                        Icon::create('seminar+remove')
                    )->asDialog('size=auto');
                }

                if ((Config::get()->ALLOW_DOZENT_VISIBILITY || $GLOBALS['perm']->have_perm('admin')) && !LockRules::Check($course->id, 'seminar_visibility')) {
                    $is_visible = $course->visible;
                    if ($course->duration_time == -1 || $course->end_semester->visible) {
                        $actions->addLink(
                            _('Sichtbarkeit ändern') . ' (' .  ($is_visible ? _('sichtbar') : _('unsichtbar')) . ')',
                            URLHelper::getURL($this->url_for('course/management/change_visibility'), ['studip_ticket' => Seminar_Session::get_ticket()]),
                            Icon::create('visibility-' . ($is_visible ? 'visible' : 'invisible'))
                        );
                    }
                }
                if ($GLOBALS['perm']->have_perm('admin')) {
                    $is_locked =$course->lock_rule;
                    $actions->addLink(
                        _('Sperrebene ändern') . ' (' .  ($is_locked ? _('gesperrt') : _('nicht gesperrt')) . ')',
                        URLHelper::getURL($this->url_for('course/management/lock'), ['studip_ticket' => Seminar_Session::get_ticket()]),
                        Icon::create('lock-' . ($is_locked  ? 'locked' : 'unlocked'))
                    )->asDialog('size=auto');
                }
            }
            $sidebar->addWidget($actions);

            // Entry list for admin upwards.
            if ($GLOBALS['perm']->have_studip_perm('admin', $GLOBALS['SessionSeminar'])) {
                $list = new SelectWidget(_('Veranstaltungen'), '?#admin_top_links', 'cid');
                $seminars = AdminCourseFilter::get()->getCoursesForAdminWidget();
                foreach ($seminars as $seminar) {
                    $list->addElement(new SelectElement(
                        $seminar['Seminar_id'],
                        $seminar['Name'],
                        $seminar['Seminar_id'] === Context::getId(),
                        $seminar['VeranstaltungsNummer'] . ' ' . $seminar['Name']
                    ));
                }
                $list->size = min(8, count($seminars));
                $sidebar->addWidget($list);
            }
        }
    }

    /**
     * change the visibility of a course
     *
     * @return void
     */
    function change_visibility_action()
    {
        if ((Config::get()->ALLOW_DOZENT_VISIBILITY || $GLOBALS['perm']->have_perm('admin'))
            && !LockRules::Check($GLOBALS['SessionSeminar'], 'seminar_visibility')
            && check_ticket(Request::option('studip_ticket')))
        {
            $course = Course::findCurrent();
            if ($course->duration_time == -1 || $course->end_semester->visible) {
                if (!$course->visible) {
                    StudipLog::log('SEM_VISIBLE', $course->id);
                    $course->visible = true;
                    $msg = _('Die Veranstaltung wurde sichtbar gemacht.');
                } else {
                    StudipLog::log('SEM_INVISIBLE', $course->id);
                    $course->visible = false;
                    $msg = _('Die Veranstaltung wurde versteckt.');
                }
                if ($course->store()) {
                    PageLayout::postSuccess($msg);
                }
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * shows the lock rules
     *
     * @return void
     */
    public function lock_action()
    {
        Navigation::activateItem('course/admin/main');
        PageLayout::setTitle(_('Sperrebene ändern'));
        $course = Course::findCurrent();

        if (!$course) {
            $this->redirect($this->url_for('/index'));
            return;
        }

        $this->all_lock_rules    = array_merge([['name' => ' -- ' . _("keine Sperrebene") . ' -- ', 'lock_id' => 'none']], LockRule::findAllByType('sem'));
        $this->current_lock_rule = LockRule::find($course->lock_rule);
    }

    /**
     * set the lock rule
     *
     * @return void
     */
    public function set_lock_rule_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$GLOBALS['perm']->have_studip_perm('admin', $GLOBALS['SessionSeminar'])) {
            throw new AccessDeniedException();
        }
        $course = Course::findCurrent();

        if ($course) {
            $rule_id = Request::get('lock_sem') != 'none' ? Request::get('lock_sem') : null;

            $course->lock_rule = $rule_id;
            if ($course->store()) {
                if (!is_null($rule_id)) {
                    $lock_rule = LockRule::find($rule_id);
                    $msg       = sprintf(_('Die Sperrebene %s wurde erfolgreich übernommen!'), $lock_rule->name);
                } else {
                    $msg = _('Die Sperrebene wurde erfolgreich zurückgesetzt!');
                }
                PageLayout::postMessage(MessageBox::success($msg));
            }
        }
        $this->relocate($this->url_for('/index'));
    }
}
