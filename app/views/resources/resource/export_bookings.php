<? if ($resource): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('resources/export/bookings')?>">
        <?= CSRFProtection::tokenTag() ?>
        <? if ($resource instanceof Room): ?>
            <input type="hidden" name="selected_rooms[]"
                   value="<?= htmlReady($resource->id)?>">
        <? else: ?>
            <input type="hidden" name="selected_resources[]"
                   value="<?= htmlReady($resource->id)?>">
        <? endif ?>
        <article class="widget">
            <header><?= _('Zeitbereich auswählen') ?></header>
            <section>
                <label>
                    <?= _('Startzeitpunkt') ?>
                    <input type="text" class="datepicker" name="begin_date"
                           value="<?= $begin->format('d.m.Y') ?>">
                    <input type="time" name="begin_time"
                           value="<?= $begin->format('H:i')?>">
                </label>
                <label>
                    <?= _('Endzeitpunkt') ?>
                    <input type="text" class="datepicker" name="end_date"
                           value="<?= $end->format('d.m.Y') ?>">
                    <input type="time" name="end_time"
                           value="<?= $end->format('H:i')?>">
                </label>
            </section>
        </article>
        <div data-dialog-button>
            <?= \Studip\Button::create(
                _('Exportieren'),
                'export'
            ) ?>
        </div>
    </form>
<? endif ?>
