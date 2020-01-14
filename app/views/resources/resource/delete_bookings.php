<? if ($show_form): ?>
    <form class="default" method="post"
          data-dialog="<?= $no_reload ? '1' : 'reload-on-close'?>"
          action="<?= URLHelper::getLink(
                  'dispatch.php/resources/resource/delete_bookings/'
                  . $resource_id_parameter
                  ) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Zeitbereich zum Löschen von Buchungen wählen') ?></legend>
            <label>
                <?= _('Startdatum') ?>
                <input type="text" class="has-date-picker" name="begin_date"
                       value="<?= $begin->format('d.m.Y') ?>">
            </label>
            <label>
                <?= _('Start-Uhrzeit') ?>
                <input type="text" class="has-time-picker" name="begin_time"
                       value="<?= $begin->format('H:i') ?>">
            </label>
            <label>
                <?= _('Enddatum') ?>
                <input type="text" class="has-date-picker" name="end_date"
                       value="<?= $end->format('d.m.Y') ?>">
            </label>
            <label>
                <?= _('End-Uhrzeit') ?>
                <input type="text" class="has-time-picker" name="end_time"
                       value="<?= $end->format('H:i') ?>">
            </label>
        </fieldset>
        <div data-dialog-button>
            <?= \Studip\Button::create(
                _('Buchungen löschen'),
                'delete'
            ) ?>
        </div>
    </form>
<? endif ?>
