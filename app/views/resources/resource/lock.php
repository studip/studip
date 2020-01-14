<? if ($show_form): ?>
<form class="default" method="post" data-dialog="reload-on-close"
    action="<?= URLHelper::getLink('dispatch.php/resources/resource/lock/' . $resource->id) ?>">
    <fieldset>
        <legend><?= _('Bitte Sperrzeiten auswählen') ?></legend>
        <label>
            <?= _('Startzeitpunkt') ?>
            <input type="text" name="begin_date" class="has-date-picker"
                   value="<?= htmlReady($begin->format('d.m.Y')) ?>">
            <input type="text" name="begin_time" class="has-time-picker"
                    value="<?= htmlReady($begin->format('H:i')) ?>">
        </label>
        <label>
            <?= _('Endzeitpunkt') ?>
            <input type="text" name="end_date" class="has-date-picker"
                   value="<?= htmlReady($end->format('d.m.Y')) ?>">
            <input type="text" name="end_time" class="has-time-picker"
                    value="<?= htmlReady($end->format('H:i')) ?>">
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Sperren'), 'save') ?>
    </div>
</form>
<? endif ?>
