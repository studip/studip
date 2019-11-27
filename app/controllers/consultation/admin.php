<?php
require_once __DIR__ . '/consultation_controller.php';

/**
 * Administration controller for the consultation app.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
class Consultation_AdminController extends ConsultationController
{
    const SLOT_COUNT_THRESHOLD = 1000;
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($this->current_user->id !== $GLOBALS['user']->id && $GLOBALS['user']->perms !== 'root') {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/profile/consultation/admin');
        PageLayout::setTitle(_('Verwaltung der Sprechstundentermine'));

        $this->user_config = UserConfig::get($this->current_user->id);

        $this->setupSidebar($action, $this->user_config);
    }

    private function groupSlots(array $slots)
    {
        $blocks = [];
        foreach ($slots as $slot) {
            if (!isset($blocks[$slot->block_id])) {
                $blocks[$slot->block_id] = [
                    'block' => $slot->block,
                    'slots' => [],
                ];
            }
            $blocks[$slot->block_id]['slots'][] = $slot;
        }
        return $blocks;
    }

    public function index_action($page = 1)
    {
        $this->count = ConsultationSlot::countByTeacher_id($this->current_user->id);
        $this->limit = Config::get()->ENTRIES_PER_PAGE;

        if ($page > ceil($this->count / $this->limit)) {
            $page = 1;
        }

        $this->page = $page;
        $this->blocks = $this->groupSlots(ConsultationSlot::findByTeacher_id(
            $this->current_user->id,
            "ORDER BY start ASC LIMIT " . (($page - 1) * $this->limit) . ", {$this->limit}"
        ));
    }

    public function expired_action($page = 1)
    {
        $this->count = ConsultationSlot::countByTeacher_id(
            $this->current_user->id,
            true
        );
        $this->limit = Config::get()->ENTRIES_PER_PAGE;

        if ($page > ceil($this->count / $this->limit)) {
            $page = 1;
        }

        $this->page = $page;
        $this->blocks = $this->groupSlots(ConsultationSlot::findByTeacher_id(
            $this->current_user->id,
            "ORDER BY start ASC LIMIT " . (($page - 1) * $this->limit) . ", {$this->limit}",
            true
        ));

        $this->render_action('index');
    }

    public function create_action()
    {
        PageLayout::setTitle(_('Neue Sprechstundenblöcke anlegen'));

        $courses = $this->current_user->course_memberships->findBy('status', ['tutor', 'dozent']);
        if (count($courses) > 0) {
            $search_object = new MyCoursesSearch('Seminar_id', $this->current_user->perms, [
                'userid'    => $this->current_user->id,
                'semtypes'  => [],
                'exclude'   => [],
                'semesters' => array_keys(Semester::getAll()),
            ]);
            $this->course_search = new QuickSearch('course_id', $search_object);

            if ($course_id = Request::option('course_id')) {
                $this->course_search->defaultValue(
                    $course_id,
                    Course::find($course_id)->getFullName('number-name-semester')
                );
            }
        }

        // TODO: inst_default?
        $rooms = $this->current_user->institute_memberships->pluck('Raum');
        $rooms = array_filter($rooms);
        $this->room = $rooms ? reset($rooms) : '';
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        try {
            $slot_count = ConsultationBlock::countBlocks(
                $this->getDateAndTime('start'),
                $this->getDateAndTime('end'),
                Request::int('day-of-week'),
                Request::int('interval'),
                Request::int('duration')
            );
            if ($slot_count >= self::SLOT_COUNT_THRESHOLD && !Request::int('confirmed')) {
                $this->flash['confirm-many'] = $slot_count;
                throw new Exception('', -1);
            }

            $blocks = ConsultationBlock::generateBlocks(
                $this->current_user->id,
                $this->getDateAndTime('start'),
                $this->getDateAndTime('end'),
                Request::int('day-of-week'),
                Request::int('interval')
            );

            $stored = 0;
            foreach ($blocks as $block) {
                $block->room            = Request::get('room');
                $block->calendar_events = (bool) Request::int('calender-events');
                $block->note            = Request::get('note');
                $block->size            = Request::int('size', 1);
                $block->course_id       = Request::option('course_id') ?: null;

                $block->createSlots(Request::int('duration'));
                $stored += $block->store();
            }
        } catch (OverlapException $e) {
            $this->keepRequest();

            PageLayout::postError($e->getMessage(), $e->getDetails());
            $this->redirect('consultation/admin/create');
            return;
        } catch (Exception $e) {
            $this->keepRequest();

            if ($e->getCode() !== -1) {
                PageLayout::postError($e->getMessage());
            }
            $this->redirect('consultation/admin/create');
            return;
        }

        if ($stored === 0) {
            PageLayout::postError(_('In dem von Ihnen gewählten Zeitraum konnten für den gewählten Wochentag keine Termine erzeugt werden.'));
        } else {
            PageLayout::postSuccess(_('Die Sprechstundenblöcke wurden erfolgreich angelegt.'));
        }
        $this->relocate('consultation/admin');
    }

    public function note_action($block_id, $slot_id = null, $page = 1)
    {
        if ($slot_id) {
            PageLayout::setTitle(_('Anmerkung zu diesem Sprechstundentermin bearbeiten'));
        } else {
            PageLayout::setTitle(_('Anmerkung zu diesem Sprechstundenblock bearbeiten'));
        }

        $this->block   = $this->loadBlock($block_id);
        $this->slot_id = $slot_id;
        $this->page    = $page;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $note = trim(Request::get('note'));

            $changed = false;
            if ($slot_id) {
                $slot = $this->block->slots->find($slot_id);
                $slot->note = $note;
                $changed = $slot->store();
            } else {
                $this->block->note = $note;
                foreach ($this->block->slots as $slot) {
                    $slot->note = '';
                }
                $changed = $this->block->store();
            }
            if ($changed) {
                PageLayout::postSuccess(_('Der Sprechstundenblock wurde bearbeitet'));
            }

            if ($this->block->is_expired) {
                $this->redirect("consultation/admin/expired/{$page}#block-{$block_id}");
            } else {
                $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
            }
        }
    }

    public function remove_action($block_id, $slot_id = null, $page = 1)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $is_expired = false;
        if (!$slot_id) {
            $block = $this->loadBlock($block_id);
            $is_expired = $block->is_expired;

            $invalid = 0;
            foreach ($block->slots as $slot) {
                if (!$slot->is_expired && $slot->has_bookings) {
                    $invalid += 1;
                } else {
                    $slot->delete();
                }
            }

            if ($invalid > 0) {
                PageLayout::postError(implode(' ', [
                    _('Sie können mindestens einen Sprechstundentermin nicht löschen, da er bereits belegt ist.'),
                    _('Bitte sagen Sie diese Termine erst ab.')
                ]));
            } else {
                $block->delete();
                PageLayout::postSuccess(_('Die Sprechstundentermine wurden gelöscht'));
            }

        } else {
            $this->slot = $this->loadSlot($block_id, $slot_id);
            $is_expired = $this->slot->is_expired;

            if (!$this->slot->is_expired && $this->slot->has_bookings) {
                PageLayout::postError(implode(' ', [
                    _('Sie können diesen Sprechstundentermin nicht löschen, da er bereits belegt ist.'),
                    _('Bitte sagen Sie den Termin erst ab.')
                ]));
            } else {
                $this->slot->delete();
                PageLayout::postSuccess(_('Der Sprechstundentermin wurde gelöscht'));
            }
        }

        if ($is_expired) {
            $this->redirect("consultation/admin/expired/{$page}#block-{$block_id}");
        } else {
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function book_action($block_id, $slot_id, $page = 1)
    {
        PageLayout::setTitle(_('Sprechstundentermin reservieren'));

        $this->slot = $this->loadSlot($block_id, $slot_id);
        $this->page = $page;

        $permissions = ['user', 'autor', 'tutor'];
        if (Config::get()->CONSULTATION_ALLOW_DOCENTS_RESERVING) {
            $permissions[] = 'dozent';
        }

        if ($this->slot->block->course_id) {
            $this->search_object = new PermissionSearch('user_in_sem', '', 'user_id', [
                'seminar_id' => $this->slot->block->course_id,
                'sem_perm'   => $permissions,
            ]);
        } else {
            $this->search_object = new PermissionSearch('user', '', 'user_id', [
                'permission'   => $permissions,
                'exclude_user' => '',
            ]);
        }

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->slot->isOccupied()) {
                PageLayout::postError(_('Dieser Sprechstundentermin ist bereits belegt.'));
            } else {
                $booking = new ConsultationBooking();
                $booking->slot_id = $this->slot->id;
                $booking->user_id = Request::option('user_id');
                $booking->reason  = trim(Request::get('reason'));
                $booking->store();

                $this->sendMessage(
                    $booking->user,
                    $this->slot,
                    _('Sprechstundentermin zugesagt'),
                    $booking->reason,
                    $this->slot->block->teacher
                );

                if ($GLOBALS['user']->id !== $this->slot->block->teacher_id) {
                    $this->sendMessage(
                        $this->slot->block->teacher,
                        $this->slot,
                        _('Sprechstundentermin zugesagt'),
                        $booking->reason,
                        $booking->user
                    );
                }

                PageLayout::postSuccess(_('Der Sprechstundentermin wurde reserviert.'));
            }

            $this->redirect("consultation/admin/index/{$page}#block-{$this->slot->block_id}");
        }
    }

    public function edit_room_action($block_id, $page = 1)
    {
        PageLayout::setTitle(_('Ort des Sprechstundenblocks bearbeiten'));

        $this->block = $this->loadBlock($block_id);
        $this->page  = $page;
    }

    public function store_room_action($block_id, $page = 1)
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->block = $this->loadBlock($block_id);
        $this->block->room = Request::get('room');
        $this->block->store();

        PageLayout::postSuccess(_('Der Sprechstundenblock wurde gespeichert.'));

        if ($this->block->is_expired) {
            $this->redirect("consultation/admin/expired/{$page}#block-{$block_id}");
        } else {
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function cancel_block_action($block_id, $page = 1)
    {
        PageLayout::setTitle(_('Sprechstundentermine absagen'));

        $this->block = $this->loadBlock($block_id);
        $this->page  = $page;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $reason = trim(Request::get('reason'));
            foreach ($this->block->slots as $slot) {
                foreach ($slot->bookings as $booking) {
                    $this->sendMessage(
                        $booking->user,
                        $slot,
                        _('Sprechstundentermin abgesagt'),
                        $reason,
                        $this->block->teacher
                    );

                    if ($GLOBALS['user']->id !== $slot->block->teacher_id) {
                        $this->sendMessage(
                            $slot->block->teacher,
                            $slot,
                            _('Sprechstundentermin abgesagt'),
                            $reason,
                            $booking->user
                        );
                    }

                    $booking->delete();
                }
            }

            PageLayout::postSuccess(_('Die Sprechstundentermine wurden abgesagt.'));
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function cancel_slot_action($block_id, $slot_id, $page = 1)
    {
        PageLayout::setTitle(_('Sprechstundentermin absagen'));

        $this->slot = $this->loadSlot($block_id, $slot_id);
        $this->page = $page;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $ids = false;
            if (count($this->slot->bookings) > 1) {
                $ids = Request::intArray('ids');
            }

            $removed = 0;
            $reason  = trim(Request::get('reason'));
            foreach ($this->slot->bookings as $booking) {
                if ($ids !== false && !in_array($booking->id, $ids)) {
                    continue;
                }

                $this->sendMessage(
                    $booking->user,
                    $this->slot,
                    _('Sprechstundentermin abgesagt'),
                    $reason,
                    $this->slot->block->teacher
                );

                if ($GLOBALS['user']->id !== $this->slot->block->teacher_id) {
                    $this->sendMessage(
                        $this->slot->block->teacher,
                        $this->slot,
                        _('Sprechstundentermin abgesagt'),
                        $reason,
                        $booking->user
                    );
                }

                $booking->delete();
                $removed += 1;
            }

            if ($removed === count($this->slot->bookings)) {
                PageLayout::postSuccess(_('Der Sprechstundentermin wurde abgesagt.'));
            } elseif ($removed > 1) {
                PageLayout::postSuccess(sprintf(
                    _('Der Sprechstundentermin wurde für %u Personen abgesagt.'),
                    $removed
                ));
            } elseif ($removed === 1) {
                PageLayout::postSuccess(_('Der Sprechstundentermin wurde für eine Person abgesagt.'));
            }
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function reason_action($block_id, $slot_id, $booking_id, $page = 1)
    {
        PageLayout::setTitle(_('Grund für die Sprechstundenbuchung bearbeiten'));

        $this->booking = $this->loadBooking($block_id, $slot_id, $booking_id);
        $this->page    = $page;

        if (Request::isPost()) {
            CSRFProtection::tokenTag();

            $this->booking->reason = trim(Request::get('reason'));
            $this->booking->store();

            $this->sendMessage(
                $this->booking->user,
                $this->booking->slot,
                _('Grund des Sprechstundentermins bearbeitet'),
                $this->booking->reason,
                $this->booking->slot->block->teacher
            );

            if ($GLOBALS['user']->id !== $this->booking->slot->block->teacher_id) {
                $this->sendMessage(
                    $this->booking->slot->block->teacher,
                    $this->booking->slot,
                    _('Grund des Sprechstundentermins bearbeitet'),
                    $this->booking->reason,
                    $this->booking->user
                );
            }

            PageLayout::postSuccess(_('Der Grund für die Sprechstundenbuchung wurde bearbeitet.'));

            if ($this->booking->slot->block->is_expired) {
                $this->redirect("consultation/admin/expired/{$page}#block-{$this->booking->slot->block_id}");
            } else {
                $this->redirect("consultation/admin/index/{$page}#block-{$this->booking->slot->block_id}");
            }
        }
    }

    public function toggle_action($what, $state)
    {
        if ($what === 'messages') {
            $this->user_config->store(
                'CONSULTATION_SEND_MESSAGES',
                (bool) $state
            );
        }

        $this->redirect('consultation/admin/');
    }

    public function bulk_action($page, $expired)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $deleted = 0;
        $skipped = 0;
        foreach ($this->getSlotsFromBulk() as $slot) {
            if ($slot->is_expired || !$slot->has_bookings) {
                $deleted += $slot->delete();
            } else {
                $skipped += 1;
            }
        }

        if ($deleted > 0) {
            PageLayout::postSuccess(_('Die Sprechstundentermine wurden gelöscht'));
        }
        if ($skipped > 0) {
            PageLayout::postInfo(
                _('Einige Sprechstundentermine konnten nicht gelöscht werden, da sie belegt sind'),
                [_('Sie müssen diese Termine erst absagen, bevor Sie sie löschen können.')]
            );
        }

        if ($expired) {
            $this->redirect("consultation/admin/expired/{$page}");
        } else {
            $this->redirect("consultation/admin/index/{$page}");
        }
    }

    public function mail_action($block_id, $slot_id = null)
    {
        // Get matching slots
        if ($block_id === 'bulk') {
            $slots = $this->getSlotsFromBulk();
        } else {
            $block = $this->loadBlock($block_id);

            foreach ($block->slots as $slot) {
                if ($slot_id && $slot->id != $slot_id) {
                    continue;
                }

                $slots[] = $slots;
            }
        }

        // Get user names and timestamps
        $p_rec = [];
        $timestamps = [];
        foreach ($slots as $slot) {
            if (count($slot->bookings) === 0) {
                continue;
            }

            $timestamps[] = $slot->start_time;
            foreach ($slot->bookings as $booking) {
                $p_rec[] = $booking->user->username;
            }
        }

        // Get unique usernames
        $p_rec = array_unique($p_rec);

        // Get correct default subject
        $default_subject = _('Sprechstundentermin');
        if (count($timestamps) === 1) {
            $default_subject = sprintf(
                _('Sprechstundentermin am %s um %s'),
                strftime('%x', $timestamps[0]),
                strftime('%R', $timestamps[0])
            );
        } else {
            $days = array_unique(array_map(function ($timestamp) {
                return strftime('%x', $timestamp);
            }, $timestamps));
            if (count($days) === 1) {
                $default_subject = sprintf(
                    _('Sprechstundentermin am %s'),
                    $days[0]
                );
            }
        }

        // Redirect to message write
        $_SESSION['sms_data'] = compact('p_rec');
        page_close();
        $this->redirect(URLHelper::getURL(
            'dispatch.php/messages/write',
            compact('default_subject')
        ));
    }

    private function getSlotsFromBulk()
    {
        $slots = [];

        $block_ids = Request::intArray('block-id');
        $slot_ids  = Request::getArray('slot-id');

        foreach ($this->loadBlock($block_ids) as $block) {
            foreach ($block->slots as $slot) {
                $slots[$slot->id] = $slot;
            }
        }

        foreach ($slot_ids as $slot_id) {
            list($block_id, $slot_id) = explode('-', $slot_id);
            try {
                if ($slot = $this->loadSlot($block_id, $slot_id)) {
                    $slots[$slot->id] = $slot;
                }
            } catch (Exception $e) {
            }
        }

        return $slots;
    }

    private function setupSidebar($action, $config)
    {
        $sidebar = Sidebar::get();

        $views = $sidebar->addWidget(new ViewsWidget());
        $views->addLink(
            _('Aktuelle Termine'),
            $this->indexURL()
        )->setActive($action !== 'expired');
        $views->addLink(
            _('Vergangene Termine'),
            $this->expiredURL()
        )->setActive($action === 'expired');

        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Sprechstundenblöcke anlegen'),
            $this->url_for('consultation/admin/create'),
            Icon::create('add')
        )->asDialog('size=auto');

        $options = $sidebar->addWidget(new OptionsWidget());
        $options->addCheckbox(
            _('Benachrichtungen über Buchungen'),
            $config->CONSULTATION_SEND_MESSAGES,
            $this->url_for("consultation/admin/toggle/messages/1"),
            $this->url_for("consultation/admin/toggle/messages/0")
        );

        $export = $sidebar->addWidget(new ExportWidget());
        $export->addLink(
            _('Anmeldungen exportieren'),
            $this->url_for('consultation/export/bookings'),
            Icon::create('file-excel+export')
        );
    }

    private function getDateAndTime($index)
    {
        if (!Request::submitted("{$index}-date") || !Request::submitted("{$index}-time")) {
            throw new Exception("Date with index '{$index}' was not submitted properly");
        }

        return strtotime(implode(' ', [
            Request::get("{$index}-date"),
            Request::get("{$index}-time")
        ]));
    }
}
