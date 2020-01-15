<? if ($show_form): ?>
    <form class="default" action="<?= $action_link ?>" method="post"
          data-dialog="reload-on-close">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset class="global-lock-time-fields">
            <legend><?= _('Zeitraum') ?></legend>
            <label>
                <?= _('Startdatum') ?>
                <input type="text" name="begin_date" class="has-date-picker""
                       value="<?= htmlReady($begin->format('d.m.Y'))?>">
            </label>
            <label>
                <?= _('Startzeitpunkt') ?>
                <input type="text" name="begin_time" class="has-time-picker"
                       value="<?= htmlReady($begin->format('H:i'))?>">
            </label>
            <label>
                <?= _('Enddatum') ?>
                <input type="text" name="end_date" class="has-date-picker"
                       value="<?= htmlReady($end->format('d.m.Y'))?>">
            </label>
            <label>
                <?= _('Endzeitpunkt') ?>
                <input type="text" name="end_time" class="has-time-picker"
                       value="<?= htmlReady($end->format('H:i'))?>">
            </label>
        </fieldset>
        <fieldset>
            <legend><?= _('Typ der Sperrung') ?></legend>
            <select name="selected_type">
                <? foreach ($defined_types as $id => $label): ?>
                    <option value="<?= htmlReady($id)?>"
                            <?= $id == $selected_type
                              ? 'selected="selected"'
                              : ''?>>
                        <?= htmlReady($label) ?>
                    </option>
                <? endforeach ?>
            </select>
        </fieldset>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'save') ?>
        </div>
    </form>
<? endif ?>
