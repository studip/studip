<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class ConsultationMailer
{
    private static $messaging = null;

    /**
     * Returns a messaging object.
     *
     * @return messaging object
     */
    private static function getMessaging()
    {
        if (self::$messaging === null) {
            self::$messaging = new messaging();
        }
        return self::$messaging;
    }

    /**
     * Sends a consultation information message.
     *
     * @param  User             $user    Recipient
     * @param  ConsultationSlot $slot    Slot in question
     * @param  string           $subject Subject of the message
     * @param  string           $reason  Reason for a booking or cancelation
     * @param  User             $sender  Sender of the message
     */
    public static function sendMessage(User $user, ConsultationBooking $booking, $subject, $reason = '')
    {
        // Don't send message if user doesn't want it
        if (!UserConfig::get($user->id)->CONSULTATION_SEND_MESSAGES) {
            return;
        }

        setTempLanguage($user->id);

        $message = $GLOBALS['template_factory']->open('consultations/mail.php')->render([
            'user'   => $booking->user,
            'slot'   => $booking->slot,
            'reason' => $reason ?: _('Kein Grund angegeben'),
        ]);

        messaging::sendSystemMessage($user, $subject, $message);

        restoreLanguage();
    }

    /**
     * Send a booking information message to the teacher of the booked slot.
     *
     * @param  ConsultationBooking $booking The booking
     */
    public static function sendBookingMessageToTeacher(ConsultationBooking $booking)
    {
        foreach ($booking->slot->block->responsible_persons as $user) {
            self::sendMessage(
                $user,
                $booking,
                sprintf(_('Termin von %s zugesagt'), $booking->user->getFullName()),
                $booking->reason
            );
        }
    }

    /**
     * Send a booking information message to the user of the booked slot.
     *
     * @param  ConsultationBooking $booking The booking
     */
    public static function sendBookingMessageToUser(ConsultationBooking $booking)
    {
        self::sendMessage(
            $booking->user,
            $booking,
            sprintf(_('Termin bei %s zugesagt'), $booking->slot->block->range_display),
            $booking->reason
        );
    }

    /**
     * Send an information message about a changed reason to a user of the
     * booked slot.
     *
     * @param  ConsultationBooking $booking  The booking
     * @param  User                $receiver The receiver of the message
     * @param  User                $sender   The sender of the message
     */
    public static function sendReasonMessage(ConsultationBooking $booking, User $receiver)
    {
        self::sendMessage(
            $receiver,
            $booking->slot,
            sprintf(_('Grund des Termins bei bearbeitet'), $booking->slot->block->range_display),
            $booking->reason
        );
    }

    /**
     * Send a cancelation message to the teacher of the booked slot.
     *
     * @param  ConsultationBooking $booking The booking
     * @param  String              $reason  Reason of the cancelation
     */
    public static function sendCancelMessageToTeacher(ConsultationBooking $booking, $reason = '')
    {
        foreach ($booking->slot->block->responsible_persons as $user) {
            self::sendMessage(
                $user,
                $booking,
                sprintf(_('Termin von %s abgesagt'), $booking->user->getFullName()),
                trim($reason)
            );
        }
    }

    /**
     * Send a cancelation message to the user of the booked slot.
     *
     * @param  ConsultationBooking $booking The booking
     * @param  String              $reason  Reason of the cancelation
     */
    public static function sendCancelMessageToUser(ConsultationBooking $booking, $reason)
    {
        self::sendMessage(
            $booking->user,
            $booking,
            sprintf(_('Termin bei %s abgesagt'), $booking->slot->block->range_display),
            trim($reason)
        );
    }
}
