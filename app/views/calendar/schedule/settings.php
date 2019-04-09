<?
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

?>

<form class="default" method="post" action="<?= $controller->url_for('calendar/schedule/storesettings') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Darstellung des Stundenplans ändern') ?>
        </legend>

        <section>
            <?= _("Angezeigter Zeitraum") ?>

            <section class="hgroup">
                <label>
                    <?= _("von") ?>
                    <select name="start_hour">
                    <? for ($i = 0; $i <= 23; $i++) : ?>
                        <option value="<?= $i ?>" <?= $settings['glb_start_time'] == $i ? 'selected="selected"' : '' ?>>
                            <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00
                        </option>
                    <? endfor ?>
                    </select>
                </label>

                <label>
                    <?= _("bis") ?>

                    <select name="end_hour">
                    <? for ($i = 0; $i <= 23; $i++) : ?>
                        <option value="<?= $i ?>" <?= $settings['glb_end_time'] == $i ? 'selected="selected"' : '' ?>>
                            <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00
                            </option>
                    <? endfor ?>
                    </select>
                </label>

                <?= _("Uhr") ?><br>
            </section>
        </section>


        <section class="settings">
            <?= _("Angezeigte Wochentage") ?>

            <? foreach ([1,2,3,4,5,6,0] as $day) : ?>
                <label>
                    <input type="checkbox" name="days[]" value="<?= $day ?>"
                        <?= in_array($day, $settings['glb_days']) !== false ? 'checked="checked"' : '' ?>>
                    <?= getWeekDay($day, false) ?>
                </label>
            <? endforeach ?>
            <span class="invalid_message"><?= _("Bitte mindestens einen Wochentag auswählen.") ?></span><br>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createSuccess(_('Speichern'), ['onclick' => "return STUDIP.Calendar.validateNumberOfDays();"]) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('calendar/schedule/#')) ?>
    </footer>
</form>
