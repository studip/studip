<form class="default" method="post" action="<?= $controller->link_for('calendar/schedule/storesettings') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Darstellung des Stundenplans ändern') ?>
        </legend>
        <section>
            <?= _('Angezeigter Zeitraum') ?>
            <section class="hgroup">
                <label>
                    <?= _('von') ?>
                    <input type="text" name="start_hour" id="start-hour" class="size-s"
                           value="<?= sprintf('%02u:00', $settings['glb_start_time']) ?>"
                           data-time-picker>
                </label>
                <label>
                    <?= _('bis') ?>
                    <input type="text" name="end_hour" id="end-hour" class="size-s"
                           value="<?= sprintf('%02u:00', $settings['glb_end_time']) ?>"
                           data-time-picker>
                </label>
                <?= _('Uhr') ?><br>
            </section>
        </section>
        <section class='settings'>
            <?= _('Angezeigte Wochentage') ?>
            <? foreach ([1, 2, 3, 4, 5, 6, 0] as $day) : ?>
                <label>
                    <input type="checkbox" name="days[]" value="<?= $day ?>"
                        <?= in_array($day, $settings['glb_days']) !== false ? 'checked' : '' ?>>
                    <?= getWeekDay($day, false) ?>
                </label>
            <? endforeach ?>
            <span class="invalid_message"><?= _('Bitte mindestens einen Wochentag auswählen.') ?></span><br>
        </section>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::createSuccess(_('Speichern'), ['onclick' => "return STUDIP.Calendar.validateNumberOfDays();"]) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('calendar/schedule/#')) ?>
    </footer>
</form>
