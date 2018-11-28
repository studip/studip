<?php
$open_requests = $course->room_requests->filter(function (RoomRequest $request) {
    return $request->closed < 2;
});
?>
<? if (count($open_requests) > 0): ?>
    <?= MessageBox::info(sprintf(ngettext(
            'Für diese Veranstaltung liegt eine offene Raumanfrage vor.',
            'Für diese Veranstaltung liegen %u offene Raumanfragen vor',
            count($open_requests)
        ), count($open_requests)) . '<br>'
        . Studip\LinkButton::create(_('Raumanfragen anzeigen'),
                URLHelper::getURL('dispatch.php/course/room_requests/index/' . $course->getId()))) ?> 
<? endif; ?>
