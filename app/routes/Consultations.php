<?php
namespace RESTAPI\Routes;

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class Consultations extends \RESTAPI\RouteMap
{
    /*
     * @get /consultations
     * @get /consultations/blocks
     * @get /consultations/slots
     * @get /consultations/bookings
     *
     * @get /users/:user_id/consultations/slots
     * @get /users/:user_id/consultations/bookings
     */
    public function getDiscovery()
    {
        $routes = $this->router->getRoutes(true);
        foreach ($routes as $uri_template => $methods) {
            foreach ($methods as $method => $route) {
                $routes[$uri_template][$method] = $route['description'];
            }
        }
        return $routes;
    }

    /**
     * @get /consultations/blocks/:block_id
     */
    public function getBlock($block_id)
    {
        return self::blockToJSON($this->requireBlock($block_id));
    }

    /**
     * @get /consultations/slots/bulk
     */
    public function getManySlots()
    {
        $ids = \Request::intArray('ids');
        if (!$ids) {
            $this->halt(400, 'No ids provided');
        }

        return \ConsultationSlot::findAndMapMany(
            function ($slot) {
                return self::slotToJSON($slot);
            },
            $ids
        );
    }

    /**
     * @get /consultations/slots/:slot_id
     */
    public function getSlot($slot_id)
    {
        return self::slotToJSON($this->requireSlot($slot_id));
    }

    /**
     * @get /consultations/bookings/:booking_id
     */
    public function getBooking($booking_id)
    {
        return self::bookingToJSON($this->requireBooking($booking_id));
    }

    /**
     * @delete /consultations/bookings/:booking_id
     */
    public function deleteBooking($booking_id)
    {
        $booking = $this->requireBooking($booking_id);

        if ($GLOBALS['user']->id === $booking->user_id) {
            $booking->delete();
        } elseif ($GLOBALS['user']->id === $booking->slot->block->teacher_id) {
        } else {
            $this->halt(403);
        }


    }

    /**
     * Finds and returns the block with the given id. If no block exists with
     * the given id, a 404 is issued.
     *
     * @param  int $block_id Id of the block
     */
    private function requireBlock($block_id)
    {
        $block = \ConsultationBlock::find($block_id);
        if (!$block) {
            $this->notFound();
        }
        return $block;
    }

    /**
     * Converts the block to it's JSON representation.
     *
     * @param  ConsultationBlock $block
     * @return array
     */
    private static function blockToJSON(\ConsultationBlock $block)
    {
        return [
            'id'         => (int) $block->id,
            'teacher_id' => $block->teacher_id,
            'start'      => (int) $block->start,
            'end'        => (int) $block->end,
            'room'       => (string) $block->room,
            'note'       => (string) $block->note,
            'size'       => (int) $block->size,
            'course_id'  => $block->course_id ?: null,
            'mkdate'     => (int) $block->mkdate,
            'chdate'     => (int) $block->chdate,
        ];
    }

    /**
     * Finds and returns the slot with the given id. If no slot exists with
     * the given id, a 404 is issued.
     *
     * @param  int $slot_id Id of the slot
     */
    private function requireSlot($slot_id)
    {
        $slot = \ConsultationSlot::find($slot_id);
        if (!$slot) {
            $this->notFound();
        }
        return $slot;
    }

    /**
     * Converts the slot to it's JSON representation.
     *
     * @param  ConsultationSlot $slot
     * @return array
     */
    private static function slotToJSON(\ConsultationSlot $slot)
    {
        return [
            'id'         => (int) $slot->id,
            'block_id'   => (int) $slot->block_id,
            'start_time' => (int) $slot->start_time,
            'end_time'   => (int) $slot->end_time,
            'note'       => (string) $slot->note,
            'mkdate'     => (int) $slot->mkdate,
            'chdate'     => (int) $slot->chdate,

            'booking_count' => count($slot->bookings),
        ];
    }

    /**
     * Finds and returns the booking with the given id. If no booking exists
     * with the given id, a 404 is issued.
     *
     * @param  int $booking_id Id of the booking
     */
    private function requireBooking($booking_id)
    {
        $booking = \ConsultationBooking::find($booking_id);
        if (!$booking) {
            $this->notFound();
        }
        return $booking;
    }

    /**
     * Converts the booking to it's JSON representation.
     *
     * @param  ConsultationBooking $booking
     * @return array
     */
    private static function bookingToJSON(\ConsultationBooking $booking)
    {
        return [
            'id'      => (int) $booking->id,
            'slot_id' => $booking->slot_id,
            'user_id' => $booking->user_id,
            'reason'  => (string) $booking->reason,
            'mkdate'  => (int) $booking->mkdate,
            'chdate'  => (int) $booking->chate,
        ];
    }
}
