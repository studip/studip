<? if ($direct_room_requests_only): ?>
    <p>
        <?= _('Geben Sie bitte den gewünschten Raum an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.') ?>
    </p>
    <p>
        <strong><?= _('Achtung') ?>:</strong>
        <?= _('Geben Sie bitte immer die notwendige Sitzplatzanzahl mit an!') ?>
    </p>
<? else: ?>
    <p>
        <?= _('Geben Sie den gewünschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.') ?>
        </p>
        <p>
            <strong><?= _('Achtung') ?>:</strong>
            <?= _('Um später einen passenden Raum für Ihre Veranstaltung zu bekommen, geben Sie bitte immer die gewünschten Eigenschaften mit an!') ?>
        </p>
<? endif ?>
<section>
    <h2><?= _('Anfrage') ?></h2>
    <article><?= htmlready($request->getTypeString(), 1, 1) ?></article>
    <h2><?= _('Bearbeitungsstatus') ?></h2>
    <article><?= htmlReady($request->getStatusText()) ?></article>
</section>
