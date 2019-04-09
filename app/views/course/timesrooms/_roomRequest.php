<?php
$global_requests = $course->room_requests->filter(function (RoomRequest $request) {
    return $request->closed < 2 && !$request->termin_id;
});
?>
<section class="contentbox">
    <header>
        <h1>
            <?= _('Raumanfrage für die gesamte Veranstaltung') ?>
        </h1>

        <nav>
            <?= tooltipIcon(_('Hier können Sie für die gesamte Veranstaltung, also für alle regelmäßigen und unregelmäßigen Termine, '
                              . 'eine Raumanfrage erstellen.')) ?>
            <a class="link-add" href="<?= $controller->url_for('course/room_requests/edit/' . $course->id,
                    ['cid' => $course->id, 'new_room_request_type' => 'course', 'origin' => 'course_timesrooms']) ?>"
               data-dialog="size=big"
               title="<?= _('Neue Raumanfrage für die Veranstaltung erstellen') ?>">
                <?= _('Neue Raumanfrage') ?>
            </a>
        </nav>
    </header>

    <section>
    <? if (count($global_requests) > 0): ?>
        <p><?= _('Für diese Veranstaltung liegt eine offene Raumanfrage vor') ?></p>
        <?= Studip\LinkButton::create(_('Raumanfragen anzeigen'),
                URLHelper::getURL('dispatch.php/course/room_requests/index/' . $course->getId())) ?>
    <? else: ?>
        <p><?= _('Keine Raumanfrage vorhanden') ?></p>
    <? endif; ?>
    </section>
</section>
