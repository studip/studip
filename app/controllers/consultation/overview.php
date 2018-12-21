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

        Navigation::activateItem('/profile/consultation/overview');
        PageLayout::setTitle(sprintf(
            _('Sprechstundentermine von %s'),
            $this->current_user->getFullName()
        ));
    }

    public function index_action()
    {
        $this->blocks = SimpleCollection::createFromArray(
            ConsultationBlock::findBySQL(
                'teacher_id = :user_id AND start > UNIX_TIMESTAMP() ORDER BY start',
                [':user_id' => $this->current_user->id]
            )
        )->filter(function ($block) {
            return $block->isVisibleForUser();
        });

        if ($course_id = Request::option('course_id')) {
            $this->blocks = $this->blocks->findBy('course_id', $course_id);
        }

        $this->setupSidebar();
    }

    public function book_action($block_id, $slot_id)
    {
        $this->slot = $this->loadSlot($block_id, $slot_id);

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->slot->isOccupied()) {
                PageLayout::postError(_('Dieser Sprechstundentermin ist bereits belegt.'));
            } else {
                $booking = new ConsultationBooking();
                $booking->slot_id = $this->slot->id;
                $booking->user_id = $GLOBALS['user']->id;
                $booking->reason  = trim(Request::get('reason'));
                $booking->store();

                $this->sendMessage(
                    $this->slot->block->teacher,
                    $this->slot,
                    _('Sprechstundentermin zugesagt'),
                    $booking->reason,
                    $booking->user
                );

                PageLayout::postSuccess(_('Der Sprechstundentermin wurde reserviert.'));
            }

            $this->redirect("consultation/overview#block-{$block_id}");
        }
    }

    public function cancel_action($block_id, $slot_id)
    {
        $this->slot = $this->loadSlot($block_id, $slot_id);

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if (!$this->slot->isOccupied($GLOBALS['user']->id)) {
                PageLayout::postError(_('Dieser Sprechstundentermin ist nicht von Ihnen belegt.'));
            } else {
                $booking = $this->slot->bookings->findOneBy('user_id', $GLOBALS['user']->id);

                $this->sendMessage(
                    $this->slot->block->teacher,
                    $this->slot,
                    _('Sprechstundentermin abgesagt'),
                    trim(Request::get('reason')),
                    $booking->user
                );

                $booking->delete();

                PageLayout::postSuccess(_('Der Sprechstundentermin wurde abgesagt.'));
            }

            $this->redirect("consultation/overview#block-{$block_id}");
        }
    }

    private function setupSidebar()
    {
        $course_ids = array_unique(array_filter($this->blocks->pluck('course_id')));

        if (count($course_ids) === 0) {
            return;
        }

        $courses = Course::findMany(
            $course_ids,
            Config::get()->IMPORTANT_SEMNUMBER ? 'ORDER BY VeranstaltungsNummer, Name' : 'ORDER BY Name'
        );

        $options = ['' => _('Alle Sprechstunden anzeigen')];
        foreach ($courses as $course) {
            $options[$course->id] = $course->getFullName();
        }

        Sidebar::get()->addWidget(new SelectWidget(
            _('Veranstaltungs-Filter'),
            $this->url_for('consultation/overview'),
            'course_id'
        ))->setOptions($options);
    }
}
