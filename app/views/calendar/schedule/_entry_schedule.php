<form class="default"
      action="<?= $controller->link_for('calendar/schedule/addentry' . ($show_entry['id'] ? '/' . $show_entry['id'] : '')) ?>"
      method="post" name="edit_entry" onSubmit="return STUDIP.Schedule.checkFormFields()">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Stundenplaneintrag') ?>
        </legend>

        <label class="col-2">
            <?= _('Tag') ?>
            <select name="entry_day" class="size-s">
                <? foreach ([1, 2, 3, 4, 5, 6, 7] as $index) : ?>
                    <option
                        value="<?= $index ?>" <?= (isset($show_entry['day']) && $show_entry['day'] == $index) ? 'selected="selected"' : '' ?>>
                        <?= getWeekDay($index % 7, false) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>

        <label class="col-2">
            <?= _('von') ?>
            <input class="size-s studip-timepicker" placeholder="HH:mm" type="text" size="2" name="entry_start"
                   value="<?= $show_entry['start'] ? substr_replace($show_entry['start'], ':', -2, 0) : '' ?>">
        </label>

        <label class="col-2">
            <?= _('bis') ?>
            <input class="size-s studip-timepicker" placeholder="HH:mm" type="text" size="2" name="entry_end"
                   value="<?= $show_entry['end'] ? substr_replace($show_entry['end'], ':', -2, 0) : '' ?>">
        </label>

        <span class="invalid_message"><?= _('Die Endzeit liegt vor der Startzeit!') ?></span>

        <section id="color_picker">
            <?= _('Farbe des Termins') ?><br>
            <div style="margin: 10px 0">
                <? foreach ($GLOBALS['PERS_TERMIN_KAT'] as $index => $data) : ?>
                    <span class="schedule-category<?= $index ?>" style="vertical-align: middle; padding: 3px">
                        <input type="radio" name="entry_color"
                               value="<?= $index ?>" <?= ($index == $show_entry['color']) ? 'checked="checked"' : '' ?>>
                    </span>
                <? endforeach ?>
            </div>
        </section>

        <label>
            <?= _('Titel') ?>
            <input type="text" name="entry_title" value="<?= htmlReady($show_entry['title']) ?>">
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name="entry_content"
                      rows="7"><?= htmlReady($show_entry['content']) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), ['style' => 'margin-right: 20px']) ?>
        <? if ($show_entry['id']) : ?>
            <?= Studip\LinkButton::create(
                _('Löschen'),
                $controller->url_for('calendar/schedule/delete/'. $show_entry['id']),
                ['style' => 'margin-right: 20px']
            ) ?>
        <? endif ?>

        <? if ($show_entry) : ?>
            <?= Studip\LinkButton::createCancel(
                _('Abbrechen'),
                $controller->url_for('calendar/schedule'),
                ['onclick' => 'STUDIP.Schedule.cancelNewEntry(); STUDIP.Calendar.click_in_progress = false;return false;']) ?>
        <? else: ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), 'javascript:STUDIP.Schedule.cancelNewEntry()') ?>
        <? endif ?>
    </footer>
</form>
