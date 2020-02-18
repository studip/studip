<? if ($show_form): ?>
    <form class="default" method="post"
          data-dialog="size-auto<?= !$no_reload ? ';reload-on-close' : ''?>"
          action="<?= $controller->link_for('resources/resource/delete_bookings/' . $resource_id_parameter) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <fieldset>
            <legend><?= _('Zeitbereich zum Löschen von Buchungen wählen') ?></legend>
            <label class="col-3">
                <?= _('Startdatum') ?>
                <input type="text" class="has-date-picker size-s" name="begin_date"
                       value="<?= $begin->format('d.m.Y') ?>">
            </label>
            <label class="col-3">
                <?= _('Enddatum') ?>
                <input type="text" class="has-date-picker size-s" name="end_date"
                       value="<?= $end->format('d.m.Y') ?>">
            </label>
            <label class="col-3">
                <?= _('Startuhrzeit') ?>
                <input type="text" class="has-time-picker size-s" name="begin_time"
                       value="<?= $begin->format('H:i') ?>">
            </label>
            <label class="col-3">
                <?= _('Enduhrzeit') ?>
                <input type="text" class="has-time-picker size-s" name="end_time"
                       value="<?= $end->format('H:i') ?>">
            </label>
        </fieldset>
        <footer data-dialog-button>
            <?= \Studip\Button::create(
                _('Buchungen löschen'),
                'delete'
            ) ?>
        </footer>
    </form>
<? endif ?>
