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
                    <input type="text" class="has-date-picker" ="1"
                           name="begin_date" value="<?= $begin->format('d.m.Y') ?>">
                    <input type="text" class="has-time-picker" name="begin_time"
                           value="<?= $begin->format('H:i')?>">
                </label>
                <label>
                    <?= _('Endzeitpunkt') ?>
                    <input type="text" class="has-date-picker" name="end_date"
                           value="<?= $end->format('d.m.Y') ?>">
                    <input type="text" class="has-time-picker" name="end_time"
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
