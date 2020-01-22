<? if ($direct_room_requests_only): ?>
    <?= MessageBox::info(
        _('Geben Sie bitte den gewünschten Raum an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.'),
        [_('<strong>Achtung:</strong> Geben Sie bitte immer die notwendige Sitzplatzanzahl mit an!')]
    )?>
<? else: ?>
    <?= MessageBox::info(
        _('Geben Sie den gewünschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.'),
        [_('<strong>Achtung:</strong> Um später einen passenden Raum für Ihre Veranstaltung zu bekommen, geben Sie bitte immer die gewünschten Eigenschaften mit an!')]
    )?>
<? endif ?>
<section class="resources-grid">
    <section class="contentbox">
        <header><h1><?= _('Anfrage') ?></h1></header>
        <section><?= htmlready($request->getTypeString(), 1, 1) ?></section>
    </section>
    <section class="contentbox">
        <header><h1><?= _('Bearbeitungsstatus') ?></h1></header>
        <section><?= htmlReady($request->getStatusText()) ?></section>
    </section>
</section>

