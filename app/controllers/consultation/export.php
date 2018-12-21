<?php
require_once __DIR__ . '/consultation_controller.php';

/**
 * Export controller for the consultation app.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
class Consultation_ExportController extends ConsultationController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($this->current_user->id !== $GLOBALS['user']->id && $GLOBALS['user']->perms !== 'root') {
            throw new AccessDeniedException();
        }
    }

    public function bookings_action()
    {
        $csv = [];
        $csv[] = [
            _('Datum'),
            _('Beginn'),
            _('Ende'),
            _('Person'),
            _('Ort'),
            _('Notiz'),
            _('Grund'),
        ];

        $blocks = ConsultationBlock::findByTeacher_id($this->current_user->id, 'ORDER BY start ASC');
        foreach ($blocks as $block) {
            foreach ($block->slots as $slot) {
                foreach ($slot->bookings as $booking) {
                    $csv[] = [
                        strftime('%x', $slot->start_time),
                        date('H:i', $slot->start_time),
                        date('H:i', $slot->end_time),
                        $booking->user->getFullName(),
                        $slot->block->room,
                        $slot->note ?: $slot->block->note,
                        $booking->reason
                    ];
                }
            }
        }

        $this->render_csv($csv, 'Sprechstunden-Anmeldungen-' . date('Ymd') . '.csv');
    }


    public function print_action($block_id)
    {
        $this->blocks = $block_id === 'bulk'
                      ? $this->loadBlock(Request::intArray('ids'))
                      : [$this->loadBlock($block_id)];
        $this->set_layout(null);
    }
}
