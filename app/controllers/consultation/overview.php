<?php
require_once __DIR__ . '/consultation_controller.php';

/**
 * Overview/Student controller for the consultation app.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
class Consultation_OverviewController extends ConsultationController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(sprintf(
            '%s: %s',
            $this->getConsultationTitle(),
            $this->range->getFullName()
        ));
    }

    public function index_action($page = 0)
    {
        $this->activateNavigation('overview');
        $this->setupSidebar();

        $this->count = ConsultationBlock::countByRange($this->range);
        $this->limit = Config::get()->ENTRIES_PER_PAGE;

        if ($page >= ceil($this->count / $this->limit)) {
            $page = 0;
        }

        $this->page   = max($page, 0);
        $this->blocks = ConsultationBlock::findbyRange(
            $this->range,
            "ORDER BY start ASC LIMIT " . ($this->page * $this->limit) . ", {$this->limit}"
        );

        $action = $GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED ? 'index' : 'ungrouped';
        $this->render_action($action);
    }

    public function booked_action($page = 0)
    {
        $this->activateNavigation('booked');

        $this->slots = ConsultationSlot::findOccupiedSlotsByUserAndRange(
            $GLOBALS['user']->id,
            $this->range
        );
    }

    public function book_action($block_id, $slot_id)
    {
        $this->slot = $this->loadSlot($block_id, $slot_id);

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->slot->isOccupied()) {
                PageLayout::postError(_('Dieser Termin ist bereits belegt.'));
            } else {
                $booking = new ConsultationBooking();
                $booking->slot_id = $this->slot->id;
                $booking->user_id = $GLOBALS['user']->id;
                $booking->reason  = trim(Request::get('reason')) ?: null;
                $booking->store();

                PageLayout::postSuccess(_('Der Termin wurde reserviert.'));
            }

            $this->redirect("consultation/overview#block-{$block_id}");
        }
    }

    public function cancel_action($block_id, $slot_id, $from_booked = false)
    {
        $this->slot        = $this->loadSlot($block_id, $slot_id);
        $this->from_booked = $from_booked;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if (!$this->slot->isOccupied($GLOBALS['user']->id)) {
                PageLayout::postError(_('Dieser Termin ist nicht von Ihnen belegt.'));
            } else {
                $booking = $this->slot->bookings->findOneBy('user_id', $GLOBALS['user']->id);

                $booking->cancel(Request::get('reason'));

                PageLayout::postSuccess(_('Der Termin wurde abgesagt.'));
            }

            if ($from_booked) {
                $this->redirect("consultation/overview/booked#block-{$block_id}");
            } else {
                $this->redirect("consultation/overview#block-{$block_id}");
            }
        }
    }

    public function toggle_action($what, $state = null)
    {
        if ($what === 'grouped') {
            $GLOBALS['user']->cfg->store(
                'CONSULTATION_SHOW_GROUPED',
                $state === null ? !$GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED : (bool) $state
            );
        }

        $this->redirect('consultation/overview');
    }

    private function setupSidebar()
    {
        $options = Sidebar::get()->addWidget(new OptionsWidget());
        $options->addCheckbox(
            _('Termine gruppiert anzeigen'),
            $GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED,
            $this->toggleURL('grouped')
        );
    }
}
