<?php
/**
 * Abstract controller for the consultation app.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
abstract class ConsultationController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->current_user = User::findByUsername(Request::username('username', $GLOBALS['user']->username));

        if ($this->current_user->id !== $GLOBALS['user']->id) {
            URLHelper::addLinkParam('username', $this->current_user->username);
        }

        // Restore request if present
        if (isset($this->flash['request'])) {
            foreach ($this->flash['request'] as $key => $value) {
                Request::set($key, $value);
            }
        }

        // This defines the function to display a note. Not really a partial,
        // not a controller method. This has no real place...
        $this->displayNote = function ($what, $length = 40) {
            $what = trim($what);
            if (!$what) {
                return '';
            }

            if (mb_strlen($what)  < $length) {
                return '<div class="consultation-note">' . htmlReady($what) . '</div>';
            }

            return sprintf(
                '<div class="consultation-note shortened" data-tooltip="%s">%s&hellip;</div>',
                htmlReady($what),
                htmlReady(substr($what, 0, $length))
            );
        };
    }

    protected function keepRequest()
    {
        $this->flash['request'] = Request::getInstance()->getIterator()->getArrayCopy();
    }

    protected function sendMessage(User $user, ConsultationSlot $slot, $subject, $reason, User $sender = null)
    {
        // Don't send message if teacher doesn't want it
        if ($user->id === $slot->block->teacher_id && !UserConfig::get($user->id)->CONSULTATION_SEND_MESSAGES) {
            return;
        }

        setTempLanguage($user->id);

        $message = $this->get_template_factory()->open('consultation/mail.php')->render([
            'slot'   => $slot,
            'reason' => $reason ?: _('Kein Grund angegeben'),
        ]);

        $messaging = new messaging;
        $messaging->insert_message($message, $user->username, $sender ? $sender->id : '', '', '', '', '', $subject);

        restoreLanguage();
    }

    protected function loadBlock($block_id)
    {
        if (is_array($block_id)) {
            return array_map([$this, 'loadBlock'], $block_id);
        }

        $block = ConsultationBlock::find($block_id);

        if ($block->teacher_id !== $this->current_user->id) {
            throw new AccessDeniedException();
        }

        return $block;
    }

    protected function loadSlot($block_id, $slot_id)
    {
        $block = $this->loadBlock($block_id);
        $slot  = $block->slots->find($slot_id);

        if (!$slot) {
            throw new Exception(_('Dieser Sprechstundentermin existiert nicht'));
        }

        return $slot;
    }

    protected function loadBooking($block_id, $slot_id, $booking_id)
    {
        $slot    = $this->loadSlot($block_id, $slot_id);
        $booking = $slot->bookings->find($booking_id);

        if (!$booking) {
            throw new Exception(_('Diese Sprechstundenbuchung existiert nicht'));
        }

        return $booking;
    }
}
