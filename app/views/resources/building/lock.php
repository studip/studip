<? if ($show_form): ?>
<form class="default" method="post" data-dialog="reload-on-close"
    action="<?= $controller->link_for('resources/building/lock/' . $building->id) ?>">
    <fieldset>
        <legend><?= _('Bitte Sperrzeiten auswählen') ?></legend>
        <label>
            <?= _('Startzeitpunkt') ?>
            <input type="text" class="has-date-picker" name="begin_date" value="<?= htmlReady($begin->format('d.m.Y')) ?>">
            <input type="text" class="has-time-picker" name="begin_time" value="<?= htmlReady($begin->format('H:i')) ?>">
        </label>
        <label>
            <?= _('Endzeitpunkt') ?>
            <input type="text" class="has-date-picker" name="end_date" value="<?= htmlReady($end->format('d.m.Y')) ?>">
            <input type="text" class="has-time-picker"  name="end_time" value="<?= htmlReady($end->format('H:i')) ?>">
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Sperren'), 'confirmed') ?>
    </div>
</form>
<? endif ?>
