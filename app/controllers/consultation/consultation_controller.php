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

        $this->range = Context::get() ?: User::findByUsername(Request::username('username', $GLOBALS['user']->username));

        if ($this->range instanceof User) {
            URLHelper::addLinkParam('username', $this->range->username);
        } elseif ($this->range instanceof Course || $this->range instanceof Institute) {
            URLHelper::addLinkParam('cid', $this->range->id);
        }

        // Restore request if present
        if (isset($this->flash['request'])) {
            foreach ($this->flash['request'] as $key => $value) {
                Request::set($key, $value);
            }
        }

        // This defines the function to display a note. Not really a partial,
        // not a controller method. This has no real place...
        $this->displayNote = function ($what, $length = 40, $position = 'above') {
            $what = trim($what);
            if (!$what) {
                return '';
            }

            if (mb_strlen($what)  < $length) {
                return '<div class="consultation-note consultation-note-' . $position . '">' . formatLinks($what) . '</div>';
            }

            return sprintf(
                '<div class="consultation-note consultation-note-%s shortened" data-tooltip=\'%s\'>%s&hellip;</div>',
                $position,
                json_encode(['html' => formatLinks($what)]),
                htmlReady(substr($what, 0, $length))
            );
        };
    }

    protected function activateNavigation($path)
    {
        $path = ltrim($path, '/');

        if ($this->range instanceof User) {
            Navigation::activateItem("/profile/consultation/{$path}");
        } elseif ($this->range instanceof Course || $this->range instanceof Institute) {
            Navigation::activateItem("/course/consultation/{$path}");
        } else {
            throw new Exception('Not implemented yet');
        }
    }

    protected function getConsultationTitle()
    {
        return $this->range->getConfiguration()->CONSULTATION_TAB_TITLE;
    }

    protected function keepRequest()
    {
        $this->flash['request'] = Request::getInstance()->getIterator()->getArrayCopy();
    }

    protected function loadBlock($block_id)
    {
        if (is_array($block_id)) {
            return array_map([$this, 'loadBlock'], $block_id);
        }

        $block = ConsultationBlock::find($block_id);

        if (!$block->range->isAccessibleToUser()) {
            throw new AccessDeniedException();
        }

        return $block;
    }

    protected function loadSlot($block_id, $slot_id)
    {
        $block = $this->loadBlock($block_id);
        $slot  = $block->slots->find($slot_id);

        if (!$slot) {
            throw new Exception(_('Dieser Termin existiert nicht'));
        }

        return $slot;
    }

    protected function loadBooking($block_id, $slot_id, $booking_id)
    {
        $slot    = $this->loadSlot($block_id, $slot_id);
        $booking = $slot->bookings->find($booking_id);

        if (!$booking) {
            throw new Exception(_('Diese Buchung existiert nicht'));
        }

        return $booking;
    }
}
