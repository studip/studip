<form action="<?= $controller->url_for('course/cancel_dates/store') ?>" method="post" class="default" name="cancel_dates">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Folgende Veranstaltungstermine ausfallen lassen') ?>
        </legend>
        <div style="padding: 5px; margin: 5px;font-weight: bold;">
            <? echo join(', ', array_map(function ($d) {
                return $d->toString();
            }, $dates)); ?>
        </div>

        <label>
            <?= _('Kommentar') ?>
            <?= tooltipIcon(_('Wenn Sie die nicht stattfindenden Termine mit einem Kommentar versehen, werden die Ausfalltermine im Ablaufplan weiterhin dargestellt und auch im Terminkalender eingeblendet.')) ?>
            <textarea wrap="virtual" name="cancel_dates_comment" id="cancel_dates_comment"></textarea>
        </label>
        <label>
            <input type="checkbox" name="cancel_dates_snd_message" value="1">
            <?= _('Benachrichtigung über ausfallende Termine an alle Teilnehmenden verschicken') ?>
        </label>
    </fieldset>
    <? if ($issue_id) : ?>
        <input type="hidden" name="issue_id" value="<?= $issue_id ?>">
    <? else : ?>
        <input type="hidden" name="termin_id" value="<?= $dates[0]->getTerminId() ?>">
    <? endif ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
    </footer>
</form>
