<? use Studip\Button, Studip\LinkButton; ?>
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? else : ?>
    <? SkipLinks::addIndex(_('Termine exportieren'), 'main_content', 100); ?>
<? endif; ?>
<form action="<?= $controller->url_for('calendar/single/export_calendar/' . $calendar->getRangeId(), ['atime' => $atime, 'last_view' => $last_view]) ?>" method="post" name="sync_form" id="calendar_sync" class="default">
    <fieldset>
        <legend>
            <?= sprintf(_('Termine exportieren')) ?>
        </legend>

        <label for="event-type">
            <?= _('Welche Termine sollen exportiert werden') ?>:

            <select name="event_type" id="event-type" size="1">
                <option value="user" selected><?= _('Nur eigene Termine') ?></option>
                <option value="course"><?= _('Nur Veranstaltungs-Termine') ?></option>
                <option value="all"><?= _('Alle Termine') ?></option>
            </select>
        </label>

        <label>
            <input type="radio" name="export_time" value="all" id="export-all" checked>
            <?= _('Alle Termine exportieren') ?>
        </label>

        <label>
                <input type="radio" name="export_time" value="date" id="export-date">
                <?= _('Nur Termin in folgendem Zeitraum exportieren') ?>
        </label>

        <section class="hgroup">
            <? $start = strtotime('now') ?>
            <? $end = strtotime('+1 year') ?>
            <input id="export-start" type="text" name="export_start" class="no-hint"
                maxlength="10" class="hasDatepicker" value="<?= strftime('%x', $start) ?>">
            <input id="export-end" type="text" name="export_end" class="no-hint"
                maxlength="10" class="hasDatepicker" value="<?= strftime('%x', $end) ?>">
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= Button::createAccept(_('Termine exportieren'), 'export', ['title' => _('Termine exportieren')]) ?>

        <? if (!Request::isXhr()) : ?>
            <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/single/' . $last_view)) ?>
        <? endif; ?>
    </footer>
</form>
<script>
    jQuery('#export-start').datepicker();
    jQuery('#export-end').datepicker();
</script>
